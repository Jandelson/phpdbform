<?php
/**************************************
 * phpform                            *
 **************************************
 * Base class for forms               *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2001 - 02 - 06                     *
 **************************************/
namespace phpdbform;

class phpdbform_form
{
    public $name;
    public $action;
    public $fields;
    public $codigo_extra_botoes;
    public $sent_gerado = false;

    public function __construct($name, $action = '')
    {
        $this->fields = [];
        $this->name = $name;
        if ($action == '') {
            $action = basename($_SERVER['PHP_SELF']);
        }
        $this->action = $action;
    }

    public function add_multi_listbox($field, $title, $db, $table, $key, $value, $order, $where)
    {
        $this->fields[$field] = new PhpdbformMultiListbox($this->name, $field, $title, $db, $table, $key, $value, $order, $where);
    }

    public function add_search($field, $title, $url)
    {
        $this->fields[$field] = new PhpdbformSearch($this->name, $field, $title, $url);
    }

    public function add_textbox($field, $title, $size, $maxlength = 0, $required = false)
    {
        $this->fields[$field] = new phpdbform_textbox($this->name, $field, $title, $size, $maxlength, $required);
    }

    public function add_textarea($field, $title, $cols, $rows)
    {
        $this->fields[$field] = new phpdbform_textarea($this->name, $field, $title, $cols, $rows);
    }

    public function add_password($field, $title, $size, $maxlength = 0)
    {
        $this->fields[$field] = new phpdbform_password($this->name, $field, $title, $size, $maxlength);
    }

    public function add_static_listbox($field, $title, $options, $hints = '')
    {
        $this->fields[$field] = new phpdbform_static_listbox($this->name, $field, $title, $options, $hints);
    }

    public function add_hidden($field)
    {
        $this->fields[$field] = new phpdbform_hidden($this->name, $field);
    }

    public function add_checkbox($field, $title, $checked_value, $unchecked_value, $description = '')
    {
        $this->fields[$field] = new phpdbform_checkbox($this->name, $field, $title, $checked_value, $unchecked_value, $description);
    }

    public function add_listbox($field, $title, $db, $table, $key, $value, $order, $where)
    {
        $this->fields[$field] = new phpdbform_listbox($this->name, $field, $title, $db, $table, $key, $value, $order, $where);
    }

    public function add_static_radiobox($field, $title, $options, $orientacao = 'v')
    {
        $this->fields[$field] = new phpdbform_static_radiobox($this->name, $field, $title, $options, $orientacao);
    }

    public function add_date($field, $title, $dateformat)
    {
        $this->fields[$field] = new phpdbform_date($this->name, $field, $title, $dateformat);
    }

    public function add_datetime($field, $title, $dateformat)
    {
        $this->fields[$field] = new phpdbform_datetime($this->name, $field, $title, $dateformat);
    }

    public function add_date_cal($field, $title, $dateformat)
    {
        $this->fields[$field] = new phpdbform_date_cal($this->name, $field, $title, $dateformat);
    }

    public function add_filebox($field, $title, $size, $maxsize, $uploadfolder)
    {
        $this->fields[$field] = new phpdbform_filebox($this->name, $field, $title, $size, $maxsize, $uploadfolder);
    }

    public function draw_phpform_sent()
    {
        if (!$this->sent_gerado) {
            $this->sent_gerado = true;
            return "<input type=\"hidden\" name=\"{$this->name}_phpform_sent\" value=\"1\">\n";
        } else {
            return '';
        }
    }

    public function draw_submit($button_text)
    {
        $ret =
            $this->draw_phpform_sent() .
            "<input type=\"hidden\" name=\"{$this->name}_phpform_sent\" value=\"1\">\n" .
            "<input type=\"submit\" name=\"submit\" value=\"$button_text\" " .
            (isset($this->submit_extra) ? $this->submit_extra : '') .
            "class=\"btOFF\" onMouseOver=\"this.className='btON'\" onMouseOut=\"this.className='btOFF'\" " .
            (($this->botoes == 6) ? "style='display:none;'" : '') .
            ">\n";
        return $ret;
    }

    public function draw_header()
    {
        if ($this->name <> 'vazia') {
            print
                "<form method=\"post\" action=\"{$this->action}\" " .
                "id=\"{$this->name}\" name=\"{$this->name}\" " .
                'onsubmit="r=true; submeteu_form=1; try { r=OnFormSubmit();} catch(e) {} return r;" ' .
                "enctype=\"multipart/form-data\">\n";
        }
    }

    public function draw_footer()
    {
        if ($this->name <> 'vazia') {
            print "</form>\n";
        }
    }

    public function draw()
    {
        $this->draw_header();
        reset($this->fields);
        while ($field = each($this->fields)) {
            $field[1]->draw();
            echo '<br>';
        }
        print '<br>';
        $this->draw_submit('Gravar');
    }

    public function process()
    {
        if (!isset($_POST["{$this->name}_phpform_sent"])) {
            return false;
        }
        reset($this->fields);
        while ($field = each($this->fields)) {
            $this->fields[$field[1]->field]->process();
        }
        return true;
    }

    public function clear()
    {
        reset($this->fields);
        while ($field = each($this->fields)) {
            $this->fields[$field[1]->field]->value = '';
        }
    }
}
