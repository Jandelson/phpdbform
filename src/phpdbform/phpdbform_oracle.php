<?php
/* Copyright (C) 2000 Paulo Assis <paulo@coral.srv.br>
                 2003 Elton Minetto <minetto@unochapeco.rct-sc.br>

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

class phpdbform_db
{
    public $database;
    public $db_host;
    public $auth_name;
    public $auth_pass;
    public $conn;
    public $cont = 0;
    public $lastSQL;

    public function __construct($database_name, $database_host, $user_name, $user_passwd)
    { // OK
        $this->database = $database_name;
        $this->db_host = $database_host;
        $this->auth_name = $user_name;
        $this->auth_pass = $user_passwd;
    }

    public function show_error($msg)
    {
        global $show_errors;
        print $msg;
    }

    // Connect to database
    public function connect()
    {
        global $error_msg;
        $this->conn = oci_connect($this->auth_name, $this->auth_pass, $this->database);
        if (!$this->conn) {
            $e = oci_error();
            $this->show_error("Connection Error:'{$e['message']}'");
            return false;
        }
        $ret = $this->query("ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $ret = $this->query("ALTER SESSION SET NLS_NUMERIC_CHARACTERS='.,'");
        return true;
    }

    // Close the connection
    public function close()
    {
        OCICommit($this->conn);
        OCILogOff($this->conn);
    }

    public function lock($t)
    {
        $this->query("lock table $t in share mode");
    }

    public function unlock()
    {
        OCICommit($this->conn);
    }

    // Do a query
    public function query($stmt2, $msg = 0, $dbg = 0)
    {
        global $error_msg;

        //$dbg = 1;

        /*
        Trocar palavras chaves incompativeis
        */
        //$trans = array();
        //$stmt = strtr($stmt2, $trans);
        $stmt = $stmt2;
        $this->lastSQL = $stmt;

        if ($dbg == 1) {
            print $stmt . '<BR>';
        }

        $ret = OCIParse($this->conn, $stmt);
        if (!$ret) {
            $this->show_error(ocierror($this->stmt));
        } else {
            OCIExecute($ret);
        }
        return $ret;
    }

    // Do a query limitando retorno
    public function limit($stmt, $inicio, $total_reg)
    {
        $sql = "SELECT * FROM (SELECT ROWNUM LIMIT,s.* from ($stmt) s) WHERE LIMIT BETWEEN " . ($inicio + 1) . ' AND ' . ($inicio + $total_reg) . ' ';
        $ret = $this->query($sql);
        return $ret;
    }

    public function fetch_array($ret, $type = OCI_BOTH)
    {
        $tmp = oci_fetch_array($ret, $type + OCI_RETURN_NULLS + OCI_RETURN_LOBS);
        $vet = false;
        if (is_array($tmp)) {
            foreach ($tmp as $k => $v) {
                $vet[strtolower($k)] = $v;
            }
        }
        return $vet;
    }

    public function fetch_row($ret)
    {
        //print "Fetch_row:";
        $tmp = OCIFetchInto($ret, $results, OCI_NUM + OCI_RETURN_LOBS);
        for ($i = 0;$i < $tmp;$i++) {
            $vet[$i] = $results[$i];
            //print $results[$i]."/";
        }
        for ($i = 1;$i <= $tmp;$i++) {
            $vet[strtolower($this->field_name($ret, $i))] = $results[$i - 1];
        }
        return $vet;
    }

    public function free_result($ret)
    {
        OCIFreeStatement($ret);
    }

    public function num_fields($ret)
    {
        return OCINumCols($ret);
    }

    public function field_len($ret, $num)
    {
        return OCIColumnSize($ret, $num);
    }

    public function field_name($ret, $num)
    {
        return OCIColumnName($ret, $num);
    }

    public function num_rows($stmt, $cnt = null)
    {
        $nr = 0;
        if (empty($stmt)) {
            $stmt = $this->lastSQL;
        }
        $sql = $this->query("SELECT COUNT(*) c FROM ($stmt)");
        $c = $this->fetch_array($sql);
        $nr = $c['c'];
        return $nr;
    }

    public function field_allow_null($ret, $num) //TODO
    {
        return true;
    }

    public function insert_id($tabela, $primario, $campos = [])
    {
        if ($primario == '') {
            $primario = "id_$tabela";
        }
        $stmt = "INSERT INTO $tabela FIELDS (";
        $vi = '';
        foreach ($campos as $ke => $va) {
            $stmt .= "$vi$ke";
            $vi = ',';
        }
        $stmt .= ') VALUES (';
        $vi = '';
        foreach ($campos as $ke => $va) {
            $stmt .= "$vi'$va'";
            $vi = ',';
        }
        $stmt .= ") RETURNING $primario INTO :ref";

        //print "Insert_id:$stmt<BR>";

        $sql = OCIparse($this->conn, $stmt);
        $ref = 0;
        OCIBindByName($sql, ':ref', $ref, 99);
        OCIExecute($sql);
        if (!$sql) {
            return false;
        } else {
            return $ref;
        }
    }

    public function field_type($ret, $num)
    {
        return OCIColumnType($ret, $num);
    }

    public function get_fields($table)
    {
        $ret = [];
        if ($table <> 'vazia') {
            $stmt = ociparse($this->conn, "select * from $table");
            OCIExecute($stmt);
            $ncols = OCINumCols($stmt);
            for ($i = 1; $i <= $ncols; $i++) {
                $column_name = OCIColumnName($stmt, $i);
                $column_type = OCIColumnType($stmt, $i);
                $column_size = OCIColumnSize($stmt, $i);
                $field = strtolower($column_name);
                $ret[$field]['type'] = strtolower($column_type);
                $ret[$field]['maxlength'] = $column_size;
            }
        }
        return $ret;
    }
}
