<?php
/**************************************
 * phpdbform_checkbox                 *
 **************************************
 * Checkbox control                   *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2002 - 06 - 03                     *
 **************************************/

namespace phpdbform;

class phpdbform_checkbox extends phpdbform_field
{
    public $checked_value;
    public $unchecked_value;
    /**
     * CkeckBox
     *
     * @param [type] $form_name
     * @param [type] $field
     * @param [type] $title
     * @param [type] $checked_value
     * @param [type] $unchecked_value
     * @param string $description
     */
    public function __construct($form_name, $field, $title, $checked_value, $unchecked_value, $description = '')
    {
        $this->form_name = $form_name;
        $this->field = $field;
        $this->title = $title;
        $this->checked_value = $checked_value;
        $this->unchecked_value = $unchecked_value;
        $this->description = $description;
        $this->key = $this->form_name . "_" . $this->field;
        $this->cssclass = "field_checkbox";
    }

    public function getString()
    {
        if (strlen($this->onblur)) {
            $javascript = "onblur=\"{$this->onblur}\"";
        } else {
            $javascript="";
        }
        $checked = ($this->value == $this->checked_value)?"checked":"";
        return "<input type=\"checkbox\" class=\"{$this->cssclass}\" name=\"$this->key\" value=\"1\" $checked $javascript {$this->tag_extra}><span title=\"{$this->description}\">{$this->title}</span>";
    }

    public function process()
    {
        if (isset($_POST[$this->key])) {
            // Checkbox sempre sao postados com valor 1 qdo checado.
            // Se nao checados, nao sao postados.
            // Porem qdo esta invisivel, (hidden), pode vir com valor zero ou 1
            $this->value = $this->checked_value;
            if (empty($_POST[$this->key])) {
                $this->value = $this->unchecked_value;
            }
        } else {
            $this->value = $this->unchecked_value;
        }
    }
}
