<?php
/**************************************
 * phpdbform_exlistbox                *
 **************************************
 * ListBox with db list control       *
 * even using a db conn, it can be    *
 * used at phpform. This control      *
 * allows automatic field filling     *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2003 - 06 - 20                     *
 **************************************/

require_once("phpdbform/phpdbform_field.php");

class phpdbform_exlistbox extends phpdbform_field {
    var $db;
    var $table;
    var $lbkey;
    var $lbvalue;
    var $order;
	var $fields_from;	// separate fields with comma
	var $fields_to;		// separate fields with comma

    // todo: add support for more than one key/value (would use 2+ fields)
    function __construct( &$form, $field, $title, &$db, $table, $key, $value, $order,
			$fields_from, $fields_to )
    {
		$this->phpdbform_field( $form, $field, $title );
        $this->db = $db;
        $this->table = $table;
        $this->lbkey = $key;
        $this->lbvalue = $value;
        $this->order = $order;
		$this->cssclass = "fieldlistbox";
		$this->fields_from = $fields_from;
		$this->fields_to = $fields_to;
		$form->add( $this );
    }
    function prepara_campo( $campo )
    {
        // Remover apóstrofe e outros caracteres indesejados
        // Codificar campo com "\" p/ os caracteres especiais !
        $campo = str_replace( "'", "´", $campo );
        return addslashes( $campo );
    }
    function getString()
    {
		if( strlen($this->onblur) ) $javascript = "onblur=\"{$this->onblur}\"";
        else $javascript="";
        if( !empty($this->title) ) $txt = $this->title."<br>";
		else $txt = "";
        $stmt = "select {$this->lbkey}, {$this->lbvalue}, {$this->fields_from} from {$this->table} order by {$this->order}";
        $ret = $this->db->query( $stmt, "populating exlistbox" );
        $txt .= "<select class=\"{$this->cssclass}\" name=\"{$this->key}\" {$this->tag_extra} "
			."onChange=\"document.{$this->form->name}.noproc_{$this->field}.value =1; document.{$this->form->name}.submit();\" $javascript>\n";
        while( $row = $this->db->fetch_row($ret) )
        {
            $selected = ($row[0] == $this->value)?"selected":"";
            $txt .= "<option value=\"".htmlspecialchars($row[0])."\" $selected >"
                .htmlspecialchars($row[1])."</option>\n";
        }
		$txt .= "</select>\n"
			."<input type=\"hidden\" name=\"noproc_{$this->field}\" value=0>\n";
        return $txt;
    }

    function process()
    {
        if( isset( $_POST[$this->key] ) )
		{
            $this->value = $_POST[$this->key];
            $this->delmagic();

			if( $_POST["noproc_{$this->field}"] == 1 )
			{
				$this->form->noproc = true;
				$stmt = "select {$this->fields_from} from {$this->table} where {$this->lbkey}='".prepara_campo($this->value)."'";
		        $ret = $this->db->query( $stmt, "filling exlistbox fields" );
				$row = $this->db->fetch_row($ret);
				$this->db->free_result($ret);
				$tok = strtok ($this->fields_to, ",");
				$i=0;
				while( $tok )
				{
					$this->form->fields[$tok]->value = $row[$i++];
					$this->form->fields[$tok]->process = false;
					$tok = strtok (",");
				}
			}
        }
    }
}
?>
