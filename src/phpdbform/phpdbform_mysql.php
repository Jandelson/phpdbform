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

class phpdbform_db {
    var $database;
    var $db_host;
    var $auth_name;
    var $auth_pass;
    var $conn;

    function __construct( $database_name, $database_host, $user_name, $user_passwd ) {
        $this->database = $database_name;
        $this->db_host = $database_host;
        $this->auth_name = $user_name;
        $this->auth_pass = $user_passwd;
    }
    function error()
    {
        return mysql_error();
    }
    function show_error($msg,$stmt="")
    {
    	global $ignorar_connect;

    	if (!$ignorar_connect) {
        	print "<b>$stmt<br>$msg ";
    		print $this->error();
    		print "</B><BR>";
    	}
    }
    // Connect to database
    function connect($new_link=false)
    {
        global $error_msg;

        //Ok 17/09/07 09:41:47 Mudei para conexao persistente...
        //Ok 19/02/08 07:43:24 Voltei novamente pq esta dando muitos erros na Ubertintas...
        $this->conn = @mysql_connect( $this->db_host, $this->auth_name, $this->auth_pass, $new_link );

        if( !$this->conn ) {
            $this->show_error($error_msg[0]);
            return false;
        }
        if( !mysql_select_db($this->database, $this->conn) )
        {
            $this->close();
            $this->show_error($error_msg[1]);
            return false;
        }
        return true;
    }
    // Close the connection
    function close()
    {
        if ($this->conn)
        	mysql_close($this->conn);
        $this->conn=false;
    }
    function lock($t)
    {
        return $this->query("lock tables $t WRITE");
    }
    function unlock()
    {
        return $this->query("unlock tables");
    }
    /*
     * Drop Tabelas
    */
    function dropTable($t=array()) {
		if (is_array($t)) {
			if(count($t)>0) {
				foreach ($t as $k=>$v) {
					$this->query("drop table if exists $v");
				}
			}
		}
	}
    // Do a query
    function query($stmt,$msg=0,$dbg=0)
    {
        global $error_msg;
        if ($dbg==1) print $stmt."<br>";
        $ret = mysql_query( $stmt, $this->conn );
        if($ret===false)
        	$this->show_error($error_msg[$msg],$stmt);
        return $ret;
    }
    // Do a query limitando retorno
    function limit($stmt,$inicio,$total_reg, $dbg=0)
    {
        return $this->query("$stmt LIMIT $inicio,$total_reg",0,$dbg);
    }
    function fetch_array( $ret, $type = MYSQL_ASSOC )
    {
        return mysql_fetch_array($ret, $type);
    }
    function fetch_row( $ret )
    {
        return mysql_fetch_row($ret);
    }
    function free_result( $ret )
    {
        mysql_free_result($ret);
    }
    function num_fields( $ret )
    {
        return mysql_num_fields($ret);
    }
    function field_len( $ret, $num )
    {
        return mysql_field_len($ret, $num);
    }
    function field_name( $ret, $num )
    {
        return mysql_field_name( $ret, $num );
    }
    function num_rows( $stmt, $cnt=null )
    {
    	if (empty($cnt)) {
        	$cnt = $this->query($stmt);
        }
        if (is_bool($cnt))
            return 0;
        return mysql_num_rows($cnt);
    }
    function affected_rows($cnt)
    {
        return mysql_affected_rows($cnt);
    }
    function field_allow_null( $ret, $num )
    {
        //$ret = MySql result set handle
        //$num = record number
        $meta = mysql_fetch_field ($ret, $num);
        if (!$meta) {
            //Information about field not available.
            return -1;
        }
        if ($meta->not_null == 1) return false;
        else return true;
    }
    function insert_id($tabela,$primario,$campos=array(), $converter=true)
    {
        $stmt = "INSERT INTO $tabela (";
        $vi = "";
        foreach($campos as $ke => $va)
        {
            if ($converter) {
	            $va = str_replace( "'", "´", $va );
    	        $va = str_replace( "\"", "´´", $va );
            }
            $stmt .= "$vi$ke";
            $vi = ",";
        }
        $stmt .= ") VALUES (";
        $vi = "";
        foreach($campos as $ke => $va)
        {
	        $va = str_replace( "'", "´", $va );
    	    $va = str_replace( "\"", "´´", $va );
        	$va = addslashes($va);
            $stmt .= "$vi'$va'";
            $vi = ",";
        }
        $stmt .= ")";
        $ret = $this->query($stmt);
        if (!$ret)
        {
        	print "<b>$stmt</b>";
            return false;
        }
        else
            return mysql_insert_id( $this->conn );
    }
    function field_type( $ret, $num )
    {
        return mysql_field_type( $ret, $num );
    }
    function get_fields( $table )
    {
        // returns an array with filed properties
        $ret = array();
        if ($table<>"vazia")
        {
        $lfields = mysql_query("SHOW FIELDS FROM $table",$this->conn);
        while($row=mysql_fetch_array($lfields))
        {
            $field = $row["Field"];
            $type = strtolower($row["Type"]);
            $type = stripslashes($type);
            $type = str_replace("binary","",$type);
            $type = str_replace("zerofill","",$type);
            $type = str_replace("unsigned","",$type);
            $length = $type;
            $length = strstr($length,"(");
            $length = str_replace("(","",$length);
            $length = str_replace(")","",$length);
            $length = (int)chop($length);
            $type = chop(preg_replace("/\\(.*\\)/", "", $type));
            //print "Field: $field - Mysql: ${row["Type"]} - Type: $type - Length: $length<br>";
            $ret[$field]["type"]=$type;
            $ret[$field]["maxlength"]=$length;
        }
        }
        return $ret;
    }
}
?>