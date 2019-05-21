<?php
/**************************************
 * phpdbform_rep_field_date           *
 **************************************
 * Date field                         *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2002 - 10 - 25                     *
 **************************************/

class phpdbform_rep_field_date extends phpdbform_rep_field {
    var $format;

    function phpdbform_rep_field_date( $field, $title )
    {
        phpdbform_rep_field::phpdbform_rep_field( $field, $title );
        $this->type = "date";
        $this->align = "left";
    }

    function draw()
    {
        if( isset($this->format) ) {
            $this->value = date( $this->format, strtotime($this->value) );
        }
        phpdbform_rep_field::draw();
    }

}
?>