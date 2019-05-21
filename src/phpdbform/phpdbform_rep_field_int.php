<?php
/**************************************
 * phpdbform_rep_field_int            *
 **************************************
 * Integer field                      *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2002 - 10 - 25                     *
 **************************************/

class phpdbform_rep_field_int extends phpdbform_rep_field {
    var $format;

    function __construct( $field, $title )
    {
        phpdbform_rep_field::phpdbform_rep_field( $field, $title );
        $this->type = "int";
        $this->align = "right";
    }

    function draw()
    {
        if( isset($this->format) ) {
            $dec = intval($this->format[0]);
            $dcpt = $this->format[1];
            $thpt = $this->format[2];
            $this->value = number_format( $this->value, $dec, $dcpt, $thpt );
        }
        phpdbform_rep_field::draw();
    }
}
?>