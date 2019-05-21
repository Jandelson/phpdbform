<?php
/**************************************
 * phpfilterform                      *
 **************************************
 * Class for drawing the filter form  *
 * used by phpdbform                  *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2002 - 07 - 08                     *
 **************************************/

class phpfilterform {

    var $name;      // unique name for form, normally phpdbform table
    var $field;
    var $title;
    var $size;
    var $value;
	var $cssclass;

    function __construct( $name, $field, $title, $size )
    {
        $this->name = $name;
        $this->field = $field;
        $this->title = $title;
        $this->size = intval( $size );
		$this->cssclass = "field_filterbox";
    }
    function prepara_campo( $campo )
    {
        // Remover apóstrofe e outros caracteres indesejados
        // Codificar campo com "\" p/ os caracteres especiais !
        $campo = str_replace( "'", "´", $campo );
        return addslashes( $campo );
    }
    function delmagic( $text )
    {
        // this function removes backslashes ig magic_quotes_gpc is on
        if( get_magic_quotes_gpc() ) return stripslashes( $text );
        else return $text;
    }

    // process input from filter form
    // returns true if anything was selected
    function process()
    {
        $afield = "select_{$this->name}_filter";
        if( !isset( $_POST[$afield] ) ) {
            // even if nothing was selected, let's see if there anything in the session
            if( isset($_SESSION[$this->name."_sess"]) )
            {
                $this->value = $_SESSION[$this->name."_sess"];
            }
            return false;
        }
        $this->value = $this->delmagic($_POST[$afield]);
        // store this value in the session
        $_SESSION[$this->name."_sess"] = $this->value;
        return true;
    }

    // returns the where clause for the query.
    // if no value is selected, returns nothing.
    // this may speed queries, as using a like for anything may be strange
    function get_where_clause()
    {
        if( empty( $this->value ) ) return "";
        return " where {$this->field} like '%".prepara_campo($this->value)."%'";
    }

	function draw()
	{
		print $this->getString();
	}

    function getString()
    {
        $ret = "<form method=\"post\" name=\"select_{$this->name}\">\n";
        if( !empty($this->title) ) $ret .= $this->title."<br>\n";
        $ret .= "<input type=\"text\" class=\"{$this->cssclass}\" name=\"select_{$this->name}_filter\" size=\"{$this->size}\" value=\"".htmlspecialchars($this->value,ENT_COMPAT,'UTF-8')."\">\n</form>\n";
		return $ret;
    }
}
?>
