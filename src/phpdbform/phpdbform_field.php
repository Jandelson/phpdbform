<?php
/**************************************
 * phpdbform_field                    *
 **************************************
 * Base class for controls            *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2001 - 02 - 06                     *
 **************************************/
namespace phpdbform;

class phpdbform_field
{
    public $form_name;
    public $field;
    public $title;
    public $size;
    public $maxlength;
    public $cols;
    public $rows;
    public $type;
    public $value;
    public $key;
    public $cssclass;
    public $updatable = true;

    // Javascript support
    public $onblur;
    public $tag_extra;

    public function draw()
    {
        print $this->getString();
    }
    public function process()
    {
    }
    public function getString()
    {
        return "";
    }
    public function delmagic()
    {
        // this function removes backslashes ig magic_quotes_gpc is on
        if (get_magic_quotes_gpc()) {
            if (is_array($this->value)) {
                reset($this->value);
                while (list($k, $v) = each($this->value)) {
                    $this->value[$k] = stripslashes($this->value[$k]);
                }
            } else {
                $this->value = stripslashes($this->value);
            }
        }
    }
}
