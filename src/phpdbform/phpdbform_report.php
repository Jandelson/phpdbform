<?php
/**************************************
 * phpform_report                     *
 **************************************
 * Report generator                   *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2002 - 10 - 25                     *
 **************************************/
require_once("phpdbform/phpdbform_rep_field.php");
require_once("phpdbform/phpdbform_rep_field_string.php");
require_once("phpdbform/phpdbform_rep_field_int.php");
require_once("phpdbform/phpdbform_rep_field_real.php");
require_once("phpdbform/phpdbform_rep_field_date.php");
require_once("phpdbform/phpdbform_rep_field_datetime.php");

class phpdbform_report {
    var $stmt;      // report query
    var $fields;
    var $db;        // db link
    var $dbfields;  // fields from database
    var $rows;      // row per page
    var $offset;    // db limit offset
    var $page;      // current page viewed
    var $numfields; // number of fields in report (fields in query - fields not printed)
    var $ret;       // db handle for query;
    var $title;
    var $border;    // border of the table
    var $width;     // width in pixels or % of the table
    var $align;     // align of the table
    var $cellpadding;
    var $cellspacing;
    var $draw_ruler;
    var $onloadrow; // function onloadrow($form) - called for every row loaded, returns false to dont print
    var $starttime;
    var $group_field; // set this if you want to group rows
    var $group_title; // text that will apear before group value
    var $group_value; // used internally to know when group changes
    var $group_align;

    function getmicrotime()
    {
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }

    function __construct( $db, $stmt, $title )
    {

        $this->db = $db;
        $this->stmt = $stmt;
        $this->title = $title;
        $this->rows = 30;  // default of 30 rows per page
		$this->rowsloaded = 0;
        $this->offset = 0;
        $this->page = 1;
        $this->align = "center";
        $this->cellpadding = 2;
        $this->cellspacing = 1;
        $this->draw_ruler = false;
        $this->group_value = "";
        $this->group_align = "left";
        $this->starttime = $this->getmicrotime();
    }

    function process()
    {
        if( isset($_GET["pag"]) ) {
            $this->page = intval($_GET["pag"]);
            if( $this->page < 1 ) $this->page = 1;
            $this->offset = ($this->page - 1)*$this->rows;
        }
        $stmt = $this->stmt . " limit {$this->offset},{$this->rows}";
        $this->ret = $this->db->query( $stmt, "building report" );
        $this->numfields = $this->db->num_fields( $this->ret );
        for( $i = 0; $i < $this->numfields; $i++ ) {
            $curr = $this->db->field_name( $this->ret, $i );
            $type = $this->db->field_type( $this->ret, $i );
            if( $type == "string" ) $this->fields[$curr] = new phpdbform_rep_field_string( $curr, $curr );
            else if( $type == "int" ) $this->fields[$curr] = new phpdbform_rep_field_int( $curr, $curr );
            else if( $type == "real" ) $this->fields[$curr] = new phpdbform_rep_field_real( $curr, $curr );
            else if( $type == "date" ) $this->fields[$curr] = new phpdbform_rep_field_date( $curr, $curr );
            else if( $type == "datetime" ) $this->fields[$curr] = new phpdbform_rep_field_datetime( $curr, $curr );
            // debug here:
            // echo "<strong>field:</strong> {$curr} <strong>type:</strong> {$type}<br>\n";
        }
    }

    function draw_header()
    {
        // check values
        $this->border = intval($this->border);
        if( $this->align != "left" && $this->align != "center" && $this->align != "right" ) {
            $this->align = "center";
            echo "Invalid align for report<br>";
        }
        $this->cellpadding = intval($this->cellpadding);
        $this->cellspacing = intval($this->cellspacing);
        if( isset($this->width) ) {
            if( !preg_match("/^[1-9][0-9]+%*$/",$this->width) ) {
                unset( $this->width );
                echo "Invalid width for report";
            }
        }
        if( isset($this->width) ) $width = "width=\"{$this->width}\"";
        else $width = "";
        print "<table border=\"$this->border\" align=\"{$this->align}\" cellpadding=\"$this->cellpadding\" "
            ."cellspacing=\"$this->cellspacing\" $width>\n";

        // counting the fields that will print
        reset( $this->fields );
        $noprint = 0;
        while( $fld = each( $this->fields ) ) {
            if( !$fld[1]->print ) $noprint++;
        }

        $this->numfields -= $noprint;
        print "\t<tr><th class=\"row0\" colspan=\"{$this->numfields}\" align=\"center\">{$this->title}</th></tr>\n";
        if( $this->draw_ruler ) {
            print "<tr><th colspan=\"{$this->numfields}\" background=\"/sistema/imagens/ruler.png\" height=50>&nbsp;</th></tr>";
        }
        reset( $this->fields );
        while( $fld = each( $this->fields ) ) {
            if( $fld[1]->print ) {
                print "\t\t";
                $fld[1]->draw_header();
                print "\n";
            }
        }
        print "\t</tr>\n";
    }

    function draw_group()
    {
        if( $this->group_align != "left" && $this->group_align != "center" && $this->group_align != "right" )
            $this->group_align = "center";
        print "\t<tr><td class=\"rowg\" colspan=\"{$this->numfields}\" align=\"{$this->group_align}\">"
            ."<span class=\"grouptitle\">{$this->group_title}</span> {$this->group_value}</td>\n\t</tr>\n";
    }

    function load_row( &$row, $line )
    {
        if( $row = $this->db->fetch_array( $this->ret ) ) {
            reset( $this->fields );
            while( $fld = each( $this->fields ) ) {
                print "\t\t";
                $this->fields[$fld[1]->field]->value = $row[$fld[1]->field];
                $this->fields[$fld[1]->field]->row = $line;
            }
			++$this->rowsloaded;
            return true;
        } else return false;
    }

    function draw_row()
    {
        reset( $this->fields );
        while( $fld = each( $this->fields ) ) {
            if( $fld[1]->print ) {
                print "\t\t";
                $fld[1]->draw();
                print "\n";
            }
        }
    }

    function draw()
    {
        $i = 0;
        $row = array();
        $this->draw_header();
        while( $this->load_row( $row, $i++ ) ) {
            if( isset($this->onloadrow) ) {
                $func = $this->onloadrow;
                $ok = $func($this);
            } else $ok = true;
            if( $ok ) {
                // check if group changed
                if( isset($this->group_field) ) {
                    if( $this->group_value != $this->fields[$this->group_field]->value ) {
                        $this->group_value = $this->fields[$this->group_field]->value;
                        $this->draw_group();
                    }
                }
                print "\n\t<tr>\n";
                $this->draw_row();
                print "</td>\t<tr>\n";
            }
        }
        $this->draw_footer();
    }

    function draw_footer()
    {
        print "\n\t<tr>\n\t\t<td class=\"row0\" colspan=\"{$this->numfields}\">"
            ."<table border=0 width=\"120\" align=\"center\"><tr><td class=\"row0\" width=\"30\">";
        if( $this->page > 1 ) print "<a href=\"".basename($_SERVER["PHP_SELF"])."?pag=1\">|&lt</a>";
        else print "|&lt";
        print "</td><td class=\"row0\" width=\"30\">";
        if( $this->page > 1 ) print "<a href=\"".basename($_SERVER["PHP_SELF"])."?pag=".($this->page-1)."\">&lt&lt</a>";
        else print "&lt&lt";
        print "</td><td class=\"row0\" align=\"center\" width=\"30\">".$this->page."</td>"
            ."<td class=\"row0\" align=\"right\" width=\"30\">";
        if( $this->rowsloaded >= $this->rows ) print "<a href=\"".basename($_SERVER["PHP_SELF"])."?pag=".($this->page+1)."\">&gt&gt</a>";
		else print "&gt&gt";
        print "</td><td class=\"row0\" width=\"30\">";
        print "&gt;|";
        print "</td></tr></table></td>\n\t</tr>\n";
        print "\t<tr>\n\t\t<td class=\"row3\" colspan=\"{$this->numfields}\" align=\"right\">";
        printf( "%.3f s - <a href=\"http://www.phpdbform.com\" target=\"blank\">phpDBform Report Creator</a>",
            ($this->getmicrotime() - $this->starttime));
        print "</td>\n\t</tr>\n</table>\n";
    }

    function free()
    {
        $this->db->free_result( $this->ret );
    }
}
?>