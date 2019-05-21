<?php
/**************************************
 * phpdbform_textbox                  *
 **************************************
 * Textbox control                    *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2001 - 02 - 06                     *
 **************************************/
namespace phpdbform;

class phpdbform_textbox extends phpdbform_field
{
    public function __construct($form_name, $field, $title, $size, $maxlength, $required)
    {
        $this->form_name = $form_name;
        $this->field = $field;
        $this->title = $title;
        $this->size = $size;
        $this->maxlength = $maxlength;
        $this->required = $required;
        $this->key = $this->form_name . '_' . $this->field;
        $this->cssclass = 'field_textbox';
    }

    public function getString()
    {
        if (strlen($this->onblur)) {
            $javascript = "onblur=\"{$this->onblur}\"";
        } else {
            $javascript = '';
        }
        if (!empty($this->title)) {
            $title = $this->title . '<br>';
        } else {
            $title = '';
        }
        if ($this->maxlength > 0) {
            $maxlength = "maxlength={$this->maxlength}";
        } else {
            $maxlength = '';
        }
        if ($this->required) {
            $required = 'required="required" placeholder="*" ';
        } else {
            $required = '';
        }
        return $title
             . "<input type=text class=\"{$this->cssclass}\" name=\"{$this->key}\" id=\"{$this->key}\" size={$this->size} $maxlength value=\""
             . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8')
             . "\" $required $javascript {$this->tag_extra}>\n";
    }

    public function process()
    {
        if (isset($_POST[$this->key])) {
            $this->value = $_POST[$this->key];
            $this->delmagic();
        }
    }
}
