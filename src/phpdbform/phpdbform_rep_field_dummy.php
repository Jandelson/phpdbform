<?php
/**************************************
 * phpdbform_rep_field_dummy          *
 **************************************
 * Dummy field                        *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2003 - 06 - 22                     *
 **************************************/

class phpdbform_rep_field_dummy extends phpdbform_rep_field {

    function __construct( $field, $title, $value )
    {
        phpdbform_rep_field::phpdbform_rep_field( $field, $title );
		$this->value = $value;
        $this->type = "dummy";
        $this->align = "left";
    }
}
?>