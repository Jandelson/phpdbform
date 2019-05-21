<?php
/**************************************
 * phpdbform_rep_field                *
 **************************************
 * Base class for report's fields     *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2002 - 10 - 25                     *
 **************************************/

class phpdbform_rep_field {
    var $field;
    var $title;
    var $type;
    var $value;
    var $align;
    var $row;
    var $width;     // width in pixels or % of the cell
    var $print;     // will this field be shown in the report?
    var $onprint;   // function onfield( $value ) - called before printing the field

    function __construct( $field, $title )
    {
        $this->field = $field;
        $this->title = $title;
        $this->value = "";
        $this->print = true;
    }

    function draw_header()
    {
        if( isset($this->width) ) {
            if( !preg_match("/^[1-9][0-9]+%*$/",$this->width) ) {
                unset( $this->width );
                echo "Invalid width for field";
            }
        }
        if( isset($this->width) ) $width = "width=\"{$this->width}\"";
        else $width = "";
        print "<th class=\"row0\" align=\"{$this->align}\" $width>{$this->title}</th>";
    }

    function draw()
    {
        // the width was checked at draw_header
        if( isset($this->onprint) ) {
            $func = $this->onprint;
            $func($this);
        }
        if( isset($this->width) ) $width = "width=\"{$this->width}\"";
        else $width = "";
        $class = "row".(($this->row%2)+1);
        print "<td align=\"{$this->align}\" class=\"$class\" $width>{$this->value}</td>";
    }
}
?>