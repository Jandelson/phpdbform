<?php
/**************************************
 * phpdbform_textarea                 *
 **************************************
 * Textarea control                   *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2001 - 02 - 06                     *
 **************************************/
namespace phpdbform;

class phpdbform_textarea extends phpdbform_field
{
    public function __construct($form_name, $field, $title, $cols, $rows)
    {
        $this->form_name = $form_name;
        $this->field = $field;
        $this->title = $title;
        $this->cols = $cols;
        $this->rows = $rows;
        $this->key = $this->form_name . "_" . $this->field;
        $this->cssclass = "field_textbox";
    }

    public function getString()
    {
        if (strlen($this->onblur)) {
            $javascript = "onblur=\"{$this->onblur}\"";
        } else {
            $javascript="";
        }
        if (!empty($this->title)) {
            $title = $this->title."<br>";
        } else {
            $title = "";
        }
        return $title."<textarea class=\"{$this->cssclass}\" id=\"$this->key\" name=\"$this->key\" cols=\"$this->cols\""
            ." rows=\"$this->rows\" $javascript {$this->tag_extra} wrap>".htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8')."</textarea>\n";
    }

    public function process()
    {
        if (isset($_POST[$this->key])) {
            $this->value = $_POST[$this->key];
            $this->delmagic();
        }
    }
}
