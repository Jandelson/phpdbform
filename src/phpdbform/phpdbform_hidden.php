<?php
/**************************************
 * phpdbform_hidden                   *
 **************************************
 * Hidden control                     *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2001 - 02 - 11                     *
 **************************************/

namespace phpdbform;

class phpdbform_hidden extends phpdbform_field
{
    public function __construct($form_name, $field)
    {
        $this->form_name = $form_name;
        $this->field = $field;
        $this->key = $this->form_name . "_" . $this->field;
    }

    public function getString()
    {
        return "<input type=hidden id=\"$this->key\" name=\"$this->key\" value=\"".htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8')."\">\n";
    }

    public function process()
    {
        if (isset($_POST[$this->key])) {
            $this->value = $_POST[$this->key];
            $this->delmagic();
        }
    }
}
