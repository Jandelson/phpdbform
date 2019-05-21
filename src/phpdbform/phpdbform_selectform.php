<?php
/**************************************
 * phpselectform                      *
 **************************************
 * Class for drawing the select form  *
 * used by phpdbform                  *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2002 - 05 - 29                     *
 **************************************/
namespace phpdbform;

class phpselectform
{
    // DB stuff
    public $table;
    public $db;        // db link
    public $keys;      // keys that identifies one unique row, use commas for more than one
    public $fields;    // fields for showing at the listbox, use commas for more than one
    public $order;     // order used to show the items, use like the order by clause
    public $dbfields;  // fields from keys and fields to use in the select clause
                    // mysql doesn't need the fields of the order by to be at the select clause
                    // I don't know about others yet
    public $options;   // options tags for the selectionform
    public $value;     // selected value, same order from keys (array)
    public $cssclass;

    // filter support
    public $filter;

    public function __construct($db, $table, $keys, $fields, $order)
    {
        $this->db = $db;
        $this->table = $table;
        $this->keys = explode(",", $keys);
        $this->fields = explode(",", $fields);
        $this->order = $order;
        $this->cssclass = "field_selectbox";

        reset($this->keys);
        reset($this->fields);
        $this->dbfields = array();
        while ($afield = each($this->keys)) {
            $this->dbfields[$afield[1]] = 0;
        }
        while ($afield = each($this->fields)) {
            $this->dbfields[$afield[1]] = 0;
        }
        // the database must be connected at this time
    }

    // process input from selection
    // returns true if anything was selected
    public function process()
    {
        if (isset($this->filter)) {
            $this->filter->process();
        }
        $afield = "select_{$this->table}_field";
        if (!isset($_POST[$afield])) {
            return false;
        }
        $this->value = unserialize(stripslashes($_POST[$afield]));
        return true;
    }

    public function add_filter($field, $title, $size)
    {
        $this->filter = new phpfilterform($this->table."_filter", $field, $title, $size);
    }

    // select data from table
    public function select_data()
    {
        $stmt = "select ";
        $tot_fields = count($this->dbfields);
        $i = 1;
        reset($this->dbfields);
        while ($afield = each($this->dbfields)) {
            $stmt .= $afield[0];
            if (($i++)<$tot_fields) {
                $stmt .= ", ";
            }
        }
        $stmt .= " from {$this->table}";
        if (isset($this->filter)) {
            $stmt .= $this->filter->get_where_clause();
        }
        if (isset($this->where)) {
            $stmt .= $this->where;
        }
        // order goes directly into the select
        $stmt .= " order by {$this->order}";

        $ret = $this->db->query($stmt, "loading data into select form");
        $this->options = "";
        $bvalue = serialize($this->value);
        while ($vals = $this->db->fetch_array($ret)) {
            // select the keys first
            $vkeys = array();
            reset($this->keys);
            while ($afield = each($this->keys)) {
                $vkeys[] = $vals[$afield[1]];
            }
            $avalue = serialize($vkeys);
            $selected = ($bvalue == $avalue)?"selected":"";
            $this->options .= "<option $selected value=\"" . htmlspecialchars($avalue, ENT_COMPAT, 'UTF-8') .  "\">";
            // now the fields - I could use implode, but since I need to get the fields first...
            reset($this->fields);
            $first = true;
            while ($afield = each($this->fields)) {
                if (!$first) {
                    $this->options .= " | ";
                } else {
                    $first = false;
                }
                $this->options .= $vals[$afield[1]];
            }
            $this->options .= "</option>\n";
        }
        $this->db->free_result($ret);
    }

    // set draw_filter false when you want to draw the filter yourself
    // by calling $dbform->selform->filter->draw()
    public function getString($draw_filter = true)
    {
        $txt = "<form method=\"post\" name=\"select_{$this->table}\">\n"
                ."<select class=\"{$this->cssclass}\" name=\"select_{$this->table}_field\" onChange=\"document.select_{$this->table}.submit()\">\n"
                ."<option value=''>&nbsp;</option>\n<option value=''>Inserir novo registro</option>\n"
                .$this->options."</select>\n</form>\n";
        if (isset($this->filter)) {
            if ($draw_filter) {
                $txt .= $this->filter->getString();
            }
        }
        return $txt;
    }

    public function draw($draw_filter = true)
    {
        print $this->getString(false);
        if (isset($this->filter)) {
            if ($draw_filter) {
                $this->filter->draw();
            }
        }
    }
}
