<?php
/**************************************
 * phpdbform_static_listbox           *
 **************************************
 * Static ListBox control             *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2001 - 02 - 07                     *
 **************************************/

namespace phpdbform;

class phpdbform_static_listbox extends phpdbform_field
{
    // array of value, text
    public $options = array();
    public $listbox = true;

    // options can be an array or string
    public function __construct($form_name, $field, $title, $options, $hints = '')
    {
        $this->form_name = $form_name;
        $this->field = $field;
        $this->title = $title;
        $this->hints = $hints;
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
        $this->cssclass = "field_listbox";
    }

    public function getString()
    {
        if (strlen($this->onblur)) {
            $javascript = "onblur=\"{$this->onblur}\"";
        } else {
            $javascript="";
        }
        if (!empty($this->title)) {
            $ret = $this->title."<br>";
        } else {
            $ret = "";
        }

        $key = (strpos(strtoupper($this->tag_extra), "MULTIPLE")===false)?$this->key:$this->key."[]";

        $id  = (strpos(strtoupper($this->tag_extra), "ID=")===false)?" id=\"$key\"":"";

        $ret .= "<select class=\"{$this->cssclass}\" name=\"$key\" $id $javascript {$this->tag_extra}>\n";

        $t_hints = array();
        if (!empty($this->hints)) {
            foreach ($this->hints as $value) {
                $array = explode(',', $value);
                $t_hints[$array[0]] = $array[1];
            }
        }
        reset($this->options);
        while ($tok = each($this->options)) {
            if (is_array($this->value)) {
                $selected = (in_array($tok[1][0], $this->value))?"selected":"";
            } else {
                $selected = ($tok[1][0] == $this->value)?"selected":"";
            }
            if ($tok[1][0] >= 1) {
                if (count($t_hints)>=1) {
                    $tt = $t_hints[$tok[1][0]];
                } else {
                    $tt = "";
                }
            } else {
                $tt = "";
            }
            $ret .= "<option value=\"{$tok[1][0]}\" title=\"{$tt}\" $selected>{$tok[1][1]}</option>\n";
        }
        $ret .= "</select>\n";
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
