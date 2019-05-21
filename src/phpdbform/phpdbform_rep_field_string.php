<?php
/**************************************
 * phpdbform_rep_field_string         *
 **************************************
 * String field                       *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2002 - 10 - 25                     *
 **************************************/

class phpdbform_rep_field_string extends phpdbform_rep_field {

    function __construct( $field, $title )
    {
        phpdbform_rep_field::phpdbform_rep_field( $field, $title );
        $this->type = "string";
        $this->align = "left";
    }
}
?>