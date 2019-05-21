<?php
/**************************************
 * phpdbform                          *
 **************************************
 * Main class for phpdbform           *
 * with database access               *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2001 - 02 - 06                     *
 **************************************/

namespace phpdbform;

use \phpdbform\phpdbform_form as phpform;

class phpdbform_db extends phpform
{
    // DB stuff
    public $table;
    public $db;                    // db link
    public $dbfields;              // fields from database
    public $keys;                  // keys that identifies one unique row, use commas for more than one
    public $selform;               // form for selecting rows
    public $mode;                  // mode of the form, insert or update
    public $keyvalue;              // value of the key, if updatemode
    public $old_extra;             // dados antigos extras em tabelas separadas anteriores ao update
    public $new_extra;             // dados novo extras em tabelas separadas anteriores ao update

    // Events fired just before executing the specified action
    // it must return true, so the process may continue
    // if it returns false, no action is taken (it's assumed that the event did it)
    public $oninsert;
    public $onupdate;
    public $ondelete;

    // db - database object linking into the db server
    // table - table name
    // keys - fields separeted by comma that select an unique row
    // sel_fields - fields shown at the selection box
    // sel_order - order used to sort the list at the selection box
    public function __construct($db, $table, $keys, $sel_fields = "", $sel_order = "", $no_session = 0)
    {
        if ($no_session<>1 and session_id()=="") {
            session_start();
        }
        $this->db = $db;
        $this->table = $table;
        $this->keys = explode(",", $keys);
        // call parents constructor - use tablename as formname
        parent::__construct($table);
        // if sel_fields == "" then the user don't want the select form!
        if ($sel_fields == "") {
            $this->selform = 0;
        } else {
            // if sel_order == "" then we use the keys as the order for listing the select form
            if ($sel_order == "") {
                $sel_order = $keys;
            }
            $this->selform = new phpselectform($this->db, $this->table, $keys, $sel_fields, $sel_order);
        }
        // at the beggining, the form starts in insertmode
        // then, at the process we check if it has akey defined, it enters update mode
        // after a delete action, it should enter insert mode
        $this->mode = "";
        if ($this->table != "vazia") {
            $this->mode = "insert";
        } // Para habilitar delete na tabela vazia
        // fill field lenghts
        $this->dbfields = $this->db->get_fields($table);
    }

    public function prepara_campo($campo1)
    {
        // Remover apóstrofe e outros caracteres indesejados
        $campo = $this->fields[$campo1]->value;
        $campo = str_replace("'", "´", $campo);
        $campo = str_replace("\"", "´´", $campo);

        // Ajusta formatos, visto que MySQL 5 gera erros se vc ajustar campos com valor fora do intervalo permitido ! ou ""
        /*
        print
            $campo1."/".
            $campo."/".
            $this->dbfields[$campo1]["type"]."/".
            $this->dbfields[$campo1]["maxlength"].
            "<br>";
        */
        switch (strtoupper($this->dbfields[$campo1]["type"])) {
            case "VARCHAR":
            case "CHAR":
                // Trunca, pois o MySQL 5 gera erro !
                if (strlen($campo)>$this->dbfields[$campo1]["maxlength"]) {
                    $campo = substr($campo, 0, $this->dbfields[$campo1]["maxlength"]);
                }
                break;
            case "DATE":
                if ($campo=="") {
                    $campo = '0000-00-00';
                }
                break;
            case "TIME":
                if ($campo=="") {
                    $campo = '00:00:00';
                }
                break;
            case "TINYINT":
            case "INT":
            case "INTEGER":
            case "BIGINT":
            case "FLOAT":
            case "DOUBLE":
                if ($campo=="") {
                    $campo='0';
                }
                break;
        }

        // Codificar campo com "\" p/ os caracteres especiais !
        return addslashes($campo);
    }

    public function add_textbox($field, $title, $size = 0, $maxlength = 0, $required = false)
    {
        // forcing a maxlength
        if ($maxlength > 0) {
            $this->dbfields[$field]["maxlength"] = $maxlength;
        }
        if ($size == 0) {
            $size = $this->dbfields[$field]["maxlength"];
        }
        phpform::add_textbox($field, $title, $size, isset($this->dbfields[$field]["maxlength"])?$this->dbfields[$field]["maxlength"]:0, $required);
    }

    public function add_password($field, $title, $size=0, $maxlength=0)
    {
        if ($maxlength > 0) {
            $this->dbfields[$field]["maxlength"] = $maxlength;
        }
        if ($size == 0) {
            $size = $this->dbfields[$field]["maxlength"];
        }
        phpform::add_password($field, $title, $size, $this->dbfields[$field]["maxlength"]);
    }

    public function add_image($field, $title, $size=0)
    {
        $this->fields[$field] = new phpdbform_image($this->name, $field, $title, $size);
    }

    // we don't need these, because phpform will create them
    //	function add_textarea($field, $title, $cols, $rows)
    //	function add_static_listbox( $field, $title, $options )
    //  function add_checkbox( $field, $title, $checked_value, $unchecked_value )

    public function add_filter($field, $title, $size)
    {
        if (!empty($this->selform)) {
            $this->selform->add_filter($field, $title, $size);
        } else {
            echo "*ERROR* add_filter called without using selection form";
        }
    }

    // select data from table
    public function select_data()
    {
        if (!$this->keyvalue) {
            return false;
        }
        $stmt = "select ";
        $tot_fields = count($this->fields);
        $i = 1;
        reset($this->fields);
        $tmp = array();
        while ($afield = each($this->fields)) {
            if (!$afield[1]->updatable) {
                continue;
            }
            $tmp[] = $afield[1]->field;
        }
        $stmt .= implode(",", $tmp);
        $stmt .= " from {$this->table} where ";
        // read values from keys
        reset($this->keyvalue);
        $i = 0;
        while ($akey = each($this->keyvalue)) {
            if ($i > 0) {
                $stmt .= " AND ";
            }
            $stmt .= trim($this->keys[$i]) . " = '{$akey[1]}'";
            $i++;
        }
        $ret = $this->db->query($stmt, "loading data from db");
        $vals = $this->db->fetch_row($ret);
        $this->db->free_result($ret);
        if (!$vals) {
            return false;
        }
        reset($vals);
        reset($this->fields);
        while ($afield = each($this->fields)) {
            if (!$afield[1]->updatable) {
                continue;
            }
            $val = each($vals);
            $this->fields[$afield[1]->field]->value = $val[1];
        }
        return true;
    }

    // insert data from form to table
    public function insert_data()
    {
        $stmt = "insert into ".$this->table." ( ";
        $first = false;
        reset($this->fields);
        while ($afield = each($this->fields)) {
            if (!$afield[1]->updatable) {
                continue;
            }
            if ($first) {
                $stmt .= ", ";
            } else {
                $first = true;
            }
            $stmt .= $afield[1]->field;
        }
        $stmt .= " ) values ( ";
        $first = false;
        reset($this->fields);
        while ($afield = each($this->fields)) {
            if (!$afield[1]->updatable) {
                continue;
            }
            if ($first) {
                $stmt .= ", ";
            } else {
                $first = true;
            }
            $field = $afield[1]->field;
            // always add slahes because we remove the slashes
            $stmt .= "'".$this->prepara_campo($field)."'";
        }
        $stmt .= " )";
        $this->db->query($stmt, "inserting data");

        // Ok 08/12/2010 18:42:27 Retorna ultimo registro inserido!
        $dad = $this->db->fetch_array($this->db->query("select last_insert_id() id"));
        $this->LastInsertID = $dad["id"];
    }

    // update data from form to table
    public function update_data()
    {
        $stmt = "update ".$this->table." set ";
        $new = [];
        $first = false;
        reset($this->fields);
        while ($afield = each($this->fields)) {
            if (!$afield[1]->updatable) {
                continue;
            }
            if ($first) {
                $stmt .= ", ";
            } else {
                $first = true;
            }
            $field = $afield[1]->field;
            // always add slahes because we remove the slashes

            $value = $this->prepara_campo($field);
            $stmt .= $field . " = '{$value}'";

            $new[$field] = $value;
        }
        $key = $this->returnKey();

        $ret = $this->db->query(
            "{$stmt} where {$key}",
            "updating data"
        );
        if (!$ret) {
            flush();
            print '</script><script>alert("Erro ao executar o sql !");</script>';
            exit;
            //echo "<textarea rows=6 cols=40>$stmt</textarea>";
        }
    }

    // delete the record
    public function delete_data()
    {
        $this->db->query(
            "delete from ".$this->table .
            " where ". $this->returnKey(),
            "deleting data"
        );
    }

    public function draw_delete_button($button_text)
    {
        //Nik Chankov 2002.06.15 /show delete buton control
        if ($this->mode != "insert") {
            return
                    $this->draw_phpform_sent().
                    "<input ".
                    "type=\"submit\" ".
                    "name=\"submit_delete\" ".
                    "value=\"$button_text\" ".
                    "class=\"btOFF\" ".
                    "onclick=\"submeteu_form=1; try {OnFormSubmit();} catch(e) {}\" ".
                    "onMouseOver=\"this.className='btON'\" ".
                    "onMouseOut=\"this.className='btOFF'\" ".
                    $this->delete_extra.
                    ">\n";
        }
    }

    public function draw_extra_button($button_text)
    {
        return
                "<input ".
                "type=\"button\" ".
                "name=\"extra\" ".
                "id=\"extra\" ".
                "value=\"$button_text\" ".
                "class=\"btOFF\" ".
                "onMouseOver=\"this.className='btON'\" ".
                "onMouseOut=\"this.className='btOFF'\" ".
                "onClick=\"$this->extra_click\" ".
                $this->extra_extra.
                ">\n";
    }

    public function draw_submit(
        $button_text,
        $draw_delete = true,
        $draw_sbmit = true,
        $draw_extra = false
    ) {
        $r = array();

        if ($this->codigo_extra_botoes<>"") {
            $r[] = $this->codigo_extra_botoes;
        }

        if ($draw_extra) {
            $r[] = $this->draw_extra_button($this->nome_extra);
        }

        if ($draw_sbmit) {
            $r[] = (phpform::draw_submit($button_text));
        }

        if ($draw_delete) {
            $r[] = $this->draw_delete_button($this->nome_delete);
        }

        $r = implode("&nbsp;&nbsp;&nbsp;", $r)."&nbsp;";
        return $r;
    }
    /**
     * Criação de campos hidden para o input
     *
     * @return void
     */
    public function draw_header()
    {
        phpform::draw_header();
        if ($this->table<>"vazia") {
            print "<input type=\"hidden\" name=\"{$this->table}_sess_key\" value=\""
            .htmlspecialchars(serialize($this->keyvalue))."\">\n";
            print "<input type=\"hidden\" name=\"{$this->table}_sess_mode\" value=\""
            .htmlspecialchars($this->mode)."\">\n";
        }
    }

    public function draw()
    {
        if (!empty($this->selform)) {
            $this->selform->draw();
        }
        phpform::draw();
    }

    public function process()
    {
        // Se formulario de seleção p/ processamento manual dos dados
        if ($this->name=="vazia") {
            // Se o formulario foi postado, processar valores dos campos
            // Ex: Preparar data p/ formato de gravacao, checkbox p/ valor correto, etc.
            phpform::process();
            return;
        }
        if (!empty($this->selform)) {
            $selformprocessed = $this->selform->process();
        }
        if (!phpform::process()) {
            // if this form didn´t processed, see if select processed
            // first check if there is a select form
            $selected = false;
            if (!empty($this->selform)) {
                // See if any key was selected by selform
                $selected = $selformprocessed;
                if ($selected && !$this->selform->value) {
                    $selected = false;
                }
                if ($selected) {
                    $this->keyvalue = $this->selform->value;
                }
            }
            // If there was no selform, or selform selected nothing
            // try to see if the user has set keyvalue
            // how user can set keyvalue? using $form->keyvalue = "xxx,xxx"
            if (!$selected && count($this->keyvalue) > 0) {
                $selected = true;
            }
            // Something filled keyvalue, try loading the values into phpdbform
            if ($selected) {
                if ($this->select_data()) {
                    // found data!
                    $this->mode = "update";
                } else {
                    // some error occurred, clear phpdbform and set insertmode
                    $this->clear();
                    $this->mode = "insert";
                }
                // nothing was selected, go to insertmode
            } else {
                $this->mode = "insert";
            }
            // if there is a select form, fill it with data
            if (!empty($this->selform)) {
                $this->selform->select_data();
            }
            return;
        }

        // the form processed anything, lets work
        // first get key and value from session
        if (isset($_POST["{$this->table}_sess_mode"])) {
            $this->mode = $_POST["{$this->table}_sess_mode"];
            // can be a hack...
            if ($this->mode != "insert" && $this->mode != "update" && $this->mode != "delete") {
                die("Invalid mode : $this->mode");
            }
            $temp = $_POST["{$this->table}_sess_key"];
            if (get_magic_quotes_gpc()) {
                $temp = stripslashes($temp);
            }
            $this->keyvalue = unserialize($temp);
        }
        // if delete button was pressed, goto deletemode
        if (isset($_POST["submit_delete"])) {
            $this->mode = "delete";
        }
        if ($this->mode == "update") {
            /* 10/01/2018 10:47:41
            * Retorna campos que extão no form porem são de outra tabela
            * Criar regra sql na rotina returnExtra
            */
            $this->old_extra = $this->returnExtra($this->keyvalue[0], $this->table);
            $this->new_extra = $this->returnExtra($this->keyvalue[0], $this->table, $this->old_extra);

            if (!empty($this->selform)) {
                $this->selform->value = $this->keyvalue;
            }
            // update data
            if (isset($this->onupdate)) {
                if (call_user_func_array($this->onupdate, array(&$this))) {
                    $this->update_data();
                }
            } else {
                $this->update_data();
            }
        } elseif ($this->mode == "insert") {
            // insert data
            $ok = true;
            if (isset($this->oninsert)) {
                $ok = call_user_func_array($this->oninsert, array(&$this));
            }
            if ($ok) {
                $this->insert_data();
                // Se template novo, nao limpar dados
                if ($this->keyvalue[0]!==-1) {
                    $this->clear();
                }
            }
        } elseif ($this->mode == "delete") {
            // delete data
            if (isset($this->ondelete)) {
                if (call_user_func_array($this->ondelete, array(&$this))) {
                    $this->delete_data();
                }
            } else {
                $this->delete_data();
            }
            $this->clear();
            $this->keyvalue = "";
            $this->mode = "insert";
        }
        // if there is a select form, fill it with data
        if (!empty($this->selform)) {
            $this->selform->select_data();
        }
    }

    public function returnJSON()
    {
        $logJSON = [];
        $logJSON['JSONid'] = $this->keyvalue[0];
        // Adiciona campos do log
        switch ($this->table) {
            case 'aliq_trib':
                $logJSON['id_regra_trib'] = $this->fields['id_regra_trib']->value;
            break;
        }
        return $logJSON;
    }

    public function returnKey()
    {
        $key = [];
        foreach ($this->keyvalue as $k=>$v) {
            $key[] = trim($this->keys[$k]) . " = '{$v}'";
        }
        $key = implode(" AND ", $key);
        return $key;
    }
    /**
     * returnExtra
     * Retorna informações de algum campo extra que não seja da tabela e sim uma ligação
     * E esteja no formulario
     *
     * @param   id do registro,table tabela em questão
     * @param new_extra informado o array antigo para retornar o novo
     * @return  arrray (dad) com os campos extras antigos e novos
    */
    public function returnExtra($id, $table, $new_extra=null)
    {
        /*
         * Adicionar aqui campos extras que existem no form e são de outra tabela
        */
        if (!empty($new_extra)) {
            foreach ($_POST as $k=>$v) {
                $k = str_replace($table.'_', "", $k);
                $v = str_replace($table.'_', "", $v);
                foreach ($new_extra as $k1=>$v1) {
                    if ($k1 == $k) {
                        $dad[$k] = $v;
                    }
                }
            }
        }

        return $dad;
    }
}
