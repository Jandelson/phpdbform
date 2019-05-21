<?php
/**
 * Tratamento de  acesso ao MySQL
 * Linha para Forçar UTF-8 na abertura, pois nao tem acentuação neste script
 * PHP Version 7
 *
 * @category  ERP
 * @package   Geweb
 * @author    Gilmar de Paula Fiocca <gilmar@geweb.com.br>
 * @copyright 2000 Paulo Assis <paulo@coral.srv.br>
 * @license   http://www.geweb.com.br Proprietary
 * @version   Release: 1.0.0
 * @link      http://www.geweb.com.br
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */
/**
 * Tratamento de  acesso ao MySQL
 * PHP Version 7
 *
 * @category  ERP
 * @package   Geweb
 * @author    Gilmar de Paula Fiocca <gilmar@geweb.com.br>
 * @copyright 2000 Paulo Assis <paulo@coral.srv.br>
 * @license   http://www.geweb.com.br Proprietary
 * @version   Release: 1.0.0
 * @link      http://www.geweb.com.br
 */
class phpdbform_db
{
    public $database;
    public $db_host;
    public $auth_name;
    public $auth_pass;
    public $conn;
    public $closed = true;
    public $redis;
    public $debug = false;

    /**
     * Undocumented function
     *
     * @param string $database_name .
     * @param string $database_host .
     * @param string $user_name     .
     * @param string $user_passwd   .
     */
    public function __construct($database_name, $database_host, $user_name, $user_passwd)
    {
        $this->database = $database_name;
        $this->db_host = $database_host;
        $this->auth_name = $user_name;
        $this->auth_pass = $user_passwd;
        $this->redis = \GeWeb\Gredis::getInstance();
    }
    /**
     * Undocumented function
     *
     * @return void
     */
    public function error()
    {
        if ($this->conn) {
            return print_r($this->conn->error, true);
        } else {
            return "";
        }
    }
    /**
     * Undocumented function
     *
     * @param string $msg  .
     * @param string $stmt .
     *
     * @return void
     */
    public function show_error($msg, $stmt = "")
    {
        global $ignorar_connect, $codusu, $Gerror, $dbcfg;

        if (!$ignorar_connect) {
            print "<b>$stmt<br>$msg ";
            print $this->error();
            print "</B><BR>";

            $log = new \GeWeb\Glog('sqlErrors');
            $log->geraLog(
                implode(
                    "\n",
                    [
                    "script:".$_SERVER['PHP_SELF'],
                    "usuario:".$codusu,
                    "stmt:".$stmt,
                    "erro:".$msg
                    ]
                ),
                250,
                false
            );
            if ($dbcfg['geweb'] and $Gerror) {
                $stmt = str_replace(array("select",","), array("\n select","\n,"), $stmt);
                $Gerror->whoops->handleError(
                    E_WARNING,
                    $this->error().$stmt,
                    '/opt/lampp/htdocs/' . $_SERVER['PHP_SELF'],
                    0
                );
            }
        }
    }
    /**
     * Connect to database
     *
     * @return void
     */
    public function connect()
    {
        global $charset;

        $this->close();
        $this->conn = new mysqli(
            $this->db_host,
            $this->auth_name,
            $this->auth_pass,
            $this->database
        );
        if ($this->conn->connect_error) {
            $this->show_error($this->conn->connect_error);
            return false;
        } else {
            $c = $charset;
            switch ($c) {
                case "UTF-8":
                    $c = "utf8";
                    break;
                case "WINDOWS-1252":
                    $c = "latin1";
                    break;
                default:
                    $c = "indefinido:{$charset}";
                    break;
            }
            if (!$this->conn->set_charset($c)) {
                $this->show_error(
                    $this->conn->connect_error.
                    " (charset {$c})"
                );
                return false;
            }
        }
        $this->closed = false;
        return true;
    }
    /**
     * Close the connection
     *
     * @return void
     */
    public function close()
    {
        if ($this->closed) {
            return;
        }
        if ($this->conn) {
            $this->query("commit");
            $this->conn->close();
        }
        $this->closed = true;
    }
    /**
     * Undocumented function
     *
     * @param string $t tabela
     *
     * @return void
     */
    public function lock($t)
    {
        return $this->query("lock tables $t WRITE");
    }
    /**
     * Undocumented function
     *
     * @return void
     */
    public function unlock()
    {
        return $this->query("unlock tables");
    }
    /**
     * Drop Tabelas
     *
     * @param array $t Tabelas
     *
     * @return void
     */
    public function dropTable($t = array())
    {
        if (is_array($t)) {
            if (count($t)>0) {
                foreach ($t as $k => $v) {
                    $this->query("drop table if exists $v");
                }
            }
        }
    }
    /**
     * Do a query
     *
     * @param string  $stmt .
     * @param integer $msg  .
     * @param integer $dbg  .
     *
     * @return void
     */
    public function query($stmt, $msg = 0, $dbg = 0)
    {
        global $error_msg, $dbcfg, $ti;
        /**
         * Analisar se estiver usando redis se precisa eliminar o cache se teve alguma alteração
         * Removido para analise, tudo está sendo feito no dbform no post do formulario
         * alterado $this->redis->connected p/ false
         */
        if ($this->redis->connected) {
            $this->redis->redisAnalise($stmt, true);
        }
        if ($dbg or $this->debug) {
            print '<pre>'.
                "\n<b>Tempo:".sec2time(time2sec(date("H:i:s")) - time2sec($ti))."</b>\n".
                trim($stmt)."\n".
                '</pre>';
        }
        $ret = $this->conn->query($stmt);
        if ($ret===false) {
            $this->show_error($error_msg[$msg], $stmt);
        }
        return $ret;
    }
    /**
     * Do a query limitando retorno
     *
     * @param string  $stmt      .
     * @param integer $inicio    .
     * @param integer $total_reg .
     * @param integer $dbg       .
     *
     * @return void
     */
    public function limit($stmt, $inicio, $total_reg, $dbg = 0)
    {
        return $this->query("$stmt LIMIT $inicio,$total_reg", 0, $dbg);
    }
    /**
     * Undocumented function
     *
     * @param Resource $ret Recurso da execucao do SQL
     *
     * @return void
     */
    public function fetch_array($ret)
    {
        global $charset;

        if (is_bool($ret)) {
            return $ret;
        } else {
            if ($ret) {
                return $ret->fetch_array(MYSQLI_ASSOC);
            } else {
                return false;
            }
        }
    }
    /**
     * Undocumented function
     *
     * @param Resource $ret Recurso
     *
     * @return void
     */
    public function fetch_row($ret)
    {
        if (is_bool($ret)) {
            return $ret;
        } else {
            return $ret->fetch_row();
        }
    }
    /**
     * Undocumented function
     *
     * @param Resource $ret Recurso
     *
     * @return void
     */
    public function free_result($ret)
    {
        mysqli_free_result($ret);
    }
    /**
     * Undocumented function
     *
     * @param Resource $ret Recurso
     *
     * @return void
     */
    public function num_fields($ret)
    {
        return $ret->field_count;
    }
    /**
     * Undocumented function
     *
     * @param Resource $ret Recurso
     * @param integer  $num Numero do campo
     *
     * @return void
     */
    public function field_len($ret, $num)
    {
        $i = mysqli_fetch_field_direct($ret, $num);
        return $i->max_length;
    }
    /**
     * Undocumented function
     *
     * @param Resource $ret Recurso
     * @param integer  $num Numero do campo
     *
     * @return void
     */
    public function field_name($ret, $num)
    {
        $i = mysqli_fetch_field_direct($ret, $num);
        return $i->name;
    }
    /**
     * Undocumented function
     *
     * @param string   $stmt SQL
     * @param Resource $cnt  Recurso do SQL
     *
     * @return void
     */
    public function num_rows($stmt, $cnt = null)
    {
        if (empty($cnt)) {
            $cnt = $this->query($stmt);
        }
        if (is_bool($cnt)) {
            return 0;
        }
        return mysqli_num_rows($cnt);
    }
    /**
     * Undocumented function
     *
     * @param Resource $cnt Recurso do SQL
     *
     * @return void
     */
    public function affected_rows($cnt)
    {
        return mysqli_affected_rows($cnt);
    }
    /**
     * Undocumented function
     *
     * @param Resource $ret Recurso
     * @param integer  $num Numero do campo
     *
     * @return void
     */
    public function field_allow_null($ret, $num)
    {
        //$ret = MySql result set handle
        //$num = record number
        $meta = mysqli_fetch_field_direct($ret, $num);
        if (!$meta) {
            //Information about field not available.
            return false;
        }
        if ($meta['flags'] & 1) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * Undocumented function
     *
     * @param string  $tabela    Nome
     * @param string  $primario  PK
     * @param array   $campos    Dados
     * @param boolean $converter Converter aspas simples/duplas ?
     *
     * @return void
     */
    public function insert_id($tabela, $primario, $campos = array(), $converter = true)
    {
        global $charset;

        $stmt = "INSERT INTO $tabela (";
        $vi = "";
        foreach ($campos as $ke => $va) {
            $stmt .= "$vi$ke";
            $vi = ",";
        }
        $stmt .= ") VALUES (";
        $vi = "";
        foreach ($campos as $ke => $va) {
            if ($converter) {
                $va = str_replace("'", "´", $va);
                $va = str_replace("\"", "´´", $va);
            }
            $va = addslashes($va);
            $stmt .= "$vi'$va'";
            $vi = ",";
        }
        $stmt .= ")";
        $ret = $this->query($stmt);
        if (!$ret) {
            print "<b>$stmt</b>";
            return false;
        } else {
            return mysqli_insert_id($this->conn);
        }
    }
    /**
     * Undocumented function
     *
     * @param Resource $ret Recurso
     * @param integer  $num Numero do campo
     *
     * @return void
     */
    public function field_type($ret, $num)
    {
        $i = mysqli_fetch_field_direct($ret, $num);
        return $this->type_hash($i->type);
    }
    /**
     * Undocumented function
     *
     * @param string $table Tabela
     *
     * @return void
     */
    public function get_fields($table)
    {
        // returns an array with filed properties
        $ret = array();
        if ($table<>"vazia") {
            $lfields = $this->query("SHOW FIELDS FROM $table");
            while ($row=$this->fetch_array($lfields)) {
                $field = $row["Field"];
                $type = strtolower($row["Type"]);
                $type = stripslashes($type);
                $type = str_replace("binary", "", $type);
                $type = str_replace("zerofill", "", $type);
                $type = str_replace("unsigned", "", $type);
                $length = $type;
                $length = strstr($length, "(");
                $length = str_replace("(", "", $length);
                $length = str_replace(")", "", $length);
                $length = (int)chop($length);
                $type = chop(preg_replace("/\\(.*\\)/", "", $type));
                //print "Field: $field - Mysql: ${row["Type"]} - Type: $type - Length: $length<br>";
                $ret[$field]["type"]=$type;
                $ret[$field]["maxlength"]=$length;
            }
        }
        return $ret;
    }
    /**
     * Undocumented function
     *
     * @param integer $retfield Nro campo
     *
     * @return void
     */
    public function type_hash($retfield)
    {
        $mysql_data_type_hash = array(
            1=>'tinyint',
            2=>'smallint',
            3=>'int',
            4=>'float',
            5=>'double',
            7=>'timestamp',
            8=>'bigint',
            9=>'mediumint',
            10=>'date',
            11=>'time',
            12=>'datetime',
            13=>'year',
            16=>'bit',
            253=>'string',
            254=>'char',
            246=>'decimal'
        );

        return $mysql_data_type_hash[$retfield];
    }
}
