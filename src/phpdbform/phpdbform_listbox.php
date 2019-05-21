<?php
/**************************************
 * phpdbform_listbox                  *
 **************************************
 * ListBox with db list control       *
 * even using a db conn, it can be    *
 * used at phpform.                   *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2001 - 06 - 13                     *
 **************************************/

namespace phpdbform;

class phpdbform_listbox extends phpdbform_field
{
    public $db;
    public $table;
    public $lbkey;
    public $lbvalue;
    public $order;
    public $where;

    // todo: add support for more than one key/value (would use 2+ fields)
    public function __construct($form_name, $field, $title, $db, $table, $key, $value, $order, $where)
    {
        $this->form_name = $form_name;
        $this->field = $field;
        $this->title = $title;
        $this->db = $db;
        $this->table = $table;
        $this->lbkey = $key;
        $this->lbvalue = $value;
        $this->order = $order;
        $this->where = $where;
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
            $txt = $this->title."<br>";
        } else {
            $txt = "";
        }
        $stmt = "select {$this->lbkey}, {$this->lbvalue} from {$this->table} {$this->where} order by {$this->order}";
        $ret = $this->db->query($stmt, "populating listbox");
        $key = (strpos(strtoupper($this->tag_extra), "MULTIPLE")===false)?$this->key:$this->key."[]";
        $txt .= "<select class=\"{$this->cssclass}\" name=\"$key\" id=\"$key\" $javascript {$this->tag_extra}>\n";
        while ($row = $this->db->fetch_row($ret)) {
            if (is_array($this->value)) {
                $selected = (in_array($row[0], $this->value))?"selected":"";
            } else {
                $selected = ($row[0] == $this->value)?"selected":"";
            }
            $txt .= "<option value=\""
                .htmlspecialchars($row[0], ENT_COMPAT, 'UTF-8')."\" $selected>"
                .htmlspecialchars($row[1], ENT_COMPAT, 'UTF-8')."</option>\n";
        }
        return $txt."</select>\n";
    }

    public function process()
    {
        if (isset($_POST[$this->key])) {
            $this->value = $_POST[$this->key];
            $this->delmagic();
        }
    }
}
