<?php
/* Copyright (C) 2000 Paulo Assis <paulo@coral.srv.br>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.  */
namespace phpdbform;

class phpdbform_pdo
{
    public $database;
    public $db_host;
    public $auth_name;
    public $auth_pass;
    public $conn;

    public function __construct($database_name, $database_host, $user_name, $user_passwd)
    {
        $this->driver = 'mysql';
        $this->database = $database_name;
        $this->db_host = $database_host;
        $this->auth_name = $user_name;
        $this->auth_pass = $user_passwd;
    }

    public function error()
    {
        if (!empty($this->conn)) {
            return print_r($this->conn->errorInfo(), true);
        } else {
            return '';
        }
    }

    public function show_error($msg, $stmt = '')
    {
        print "<b>$stmt<br>$msg ";
        print $this->error();
        print '</b><BR>';
    }

    // Connect to database
    public function connect()
    {
        try {
            $this->conn = new \PDO(
                "{$this->driver}:dbname={$this->database};host={$this->db_host}",
                $this->auth_name,
                $this->auth_pass
            );
        } catch (\PDOException $e) {
            $this->show_error($e->getMessage());
            return false;
        }

        return true;
    }

    // Close the connection
    public function close()
    {
        unset($this->conn); // Checar por close(Nao existe!)
    }

    public function lock($t)
    {
        return $this->query("lock tables $t WRITE");
    }

    public function unlock()
    {
        return $this->query('unlock tables');
    }

    /*
     * Drop Tabelas
    */
    public function dropTable($t = [])
    {
        if (is_array($t)) {
            if (count($t) > 0) {
                foreach ($t as $k => $v) {
                    $this->query("drop table if exists $v");
                }
            }
        }
    }

    // Do a query
    public function query($stmt, $msg = 0, $dbg = 0)
    {
        if ($dbg == 1) {
            print $stmt . '<br>';
        }
        $ret = $this->conn->query($stmt);
        if ($ret === false) {
            $this->show_error($msg, $stmt);
        }
        return $ret;
    }

    // Do a query limitando retorno
    public function limit($stmt, $inicio, $total_reg, $dbg = 0)
    {
        return $this->query("$stmt LIMIT $inicio,$total_reg", 0, $dbg);
    }

    public function fetch_array($ret)
    {
        if ($ret) {
            return $ret->fetch(\PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    public function fetch_row($ret)
    {
        return $ret->fetch(\PDO::FETCH_NUM);
    }

    public function free_result($ret)
    {
        $ret->closeCursor();
    }

    public function num_fields($ret)
    {
        return $ret->columnCount();
    }

    public function field_len($ret, $num)
    {
        $a = $ret->getColumnMeta($num);
        return $a['len'];
    }

    public function field_name($ret, $num)
    {
        $a = $ret->getColumnMeta($num);
        return $a['name'];
    }

    public function num_rows($stmt, $cnt = null)
    {
        if (empty($cnt)) {
            $cnt = $this->query($stmt);
        }
        return $cnt->rowCount();
    }

    public function field_allow_null($ret, $num)
    {
        // Nao existe funcao
        return true;
    }

    public function insert_id($tabela, $primario, $campos = [])
    {
        $stmt = "INSERT INTO $tabela (";
        $vi = '';
        foreach ($campos as $ke => $va) {
            $stmt .= "$vi$ke";
            $vi = ',';
        }
        $stmt .= ') VALUES (';
        $vi = '';
        foreach ($campos as $ke => $va) {
            $va = str_replace("'", '´', $va);
            $va = str_replace('"', '´´', $va);
            $va = addslashes($va);
            $stmt .= "$vi'$va'";
            $vi = ',';
        }
        $stmt .= ')';
        $ret = $this->query($stmt);
        if (!$ret) {
            print "<b>$stmt</b>";
            return false;
        } else {
            return $this->conn->lastInsertId();
        }
    }

    public function field_type($ret, $num)
    {
        $a = $ret->getColumnMeta($num);
        return strtolower($a['native_type']);
    }

    public function get_fields($table)
    {
        // returns an array with filed properties
        $ret = [];
        if ($table <> 'vazia') {
            $lfields = $this->query("SHOW FIELDS FROM $table");
            while ($row = $this->fetch_array($lfields)) {
                $field = $row['Field'];
                $type = strtolower($row['Type']);
                $type = stripslashes($type);
                $type = str_replace('binary', '', $type);
                $type = str_replace('zerofill', '', $type);
                $type = str_replace('unsigned', '', $type);
                $length = $type;
                $length = strstr($length, '(');
                $length = str_replace('(', '', $length);
                $length = str_replace(')', '', $length);
                $length = (int)chop($length);
                $type = chop(preg_replace('/\\(.*\\)/', '', $type));
                //print "Field: $field - Mysql: ${row["Type"]} - Type: $type - Length: $length<br>";
                $ret[$field]['type'] = $type;
                $ret[$field]['maxlength'] = $length;
            }
        }
        return $ret;
    }
}
