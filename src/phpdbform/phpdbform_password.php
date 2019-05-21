<?php
/**************************************
 * phpdbform_password                 *
 **************************************
 * Password control                   *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2001 - 02 - 07                     *
 **************************************/
namespace phpdbform;

class phpdbform_password extends phpdbform_field
{
    public function __construct($form_name, $field, $title, $size, $maxlength)
    {
        $this->form_name = $form_name;
        $this->field = $field;
        $this->title = $title;
        $this->size = $size;
        $this->maxlength = $maxlength;
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
        return "<span id=\"password_{$this->key}\">" . $title . "</span><input type=password class=\"{$this->cssclass}\" name=\"{$this->key}\" id=\"{$this->key}\" size={$this->size} $maxlength value=\"" . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . "\" $javascript {$this->tag_extra}><br>\n";
    }

    public function process()
    {
        if (isset($_POST[$this->key])) {
            $this->value = $_POST[$this->key];
            $this->delmagic();
        }
    }
}
