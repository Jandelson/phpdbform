<?php
/**************************************
 * phpdbform_static_radiobox          *
 **************************************
 * Static RadioBox control            *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2001 - 06 - 13                     *
 **************************************/

namespace phpdbform;

class phpdbform_static_radiobox extends phpdbform_field
{
    // array of value, text
    public $options = array();
    public $elementoOrientacao = '<br>';

    public function __construct($form_name, $field, $title, $options, $orientacao)
    {
        $this->form_name = $form_name;
        $this->field = $field;
        $this->title = $title;
        $this->orientacao = $orientacao;
        
        if ($this->orientacao == 'h') {
            $this->elementoOrientacao = '&nbsp';
        }
        if (is_array($options)) {
            $this->options = $options;
        } else {
            $tok = strtok($options, ",");
            while ($tok) {
                $pos = strpos($tok, ";");
                if ($pos === false) {
                    $this->options[] = array( $tok, $tok );
                } else {
                    $this->options[] = array( substr($tok, 0, $pos), substr($tok, $pos + 1) );
                }
                $tok = strtok(",");
            }
        }
        $this->key = $this->form_name . "_" . $this->field;
        $this->cssclass = "field_radiobox";
    }

    public function getString()
    {
        if (strlen($this->onblur)) {
            $javascript = "onblur=\"{$this->onblur}\"";
        } else {
            $javascript="";
        }
        if (!empty($this->title)) {
            $ret = $this->title.$this->elementoOrientacao;
        } else {
            $ret = "";
        }

        $id  = (strpos(strtoupper($this->tag_extra), "ID=")===false)?" id=\"$this->key\"":"";

        reset($this->options);
        while ($tok = each($this->options)) {
            $checked = ($tok[1][0] == $this->value)?"checked":"";
            $ret .=
                "<input type=\"radio\" class=\"{$this->cssclass}\" ".
                "name=\"$this->key\" $id value=\"{$tok[1][0]}\" $checked $javascript {$this->tag_extra}>{$tok[1][1]}{$this->elementoOrientacao}\n";
        }
        return $ret;
    }

    public function process()
    {
        if (isset($_POST[$this->key])) {
            $this->value = $_POST[$this->key];
            $this->delmagic();
        }
    }
}
