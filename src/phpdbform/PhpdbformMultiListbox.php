<?php
/**
 * Lista multipla de selecao
 * Criado em Tue Apr 16 2019
 * PHP Version 7
 *
 * @category  ERP
 * @package   Geweb
 * @author    Gilmar de Paula Fiocca <gilmar@geweb.com.br>
 * @copyright 2019 Geweb Informática Ltda
 * @license   http://www.geweb.com.br Proprietary
 * @version   Release: 1.0.0
 * @link      http://www.geweb.com.br
 *
 * @todo Concluir
 *
 * Exemplo:
 *   $form->add_multi_listbox(
 *       'cidades',
 *       'Cidades:',
 *       $db,
 *       'agente',
 *       "concat(trim(cidade),'-',estado) cid",
 *       '',
 *       'cid',
 *       "WHERE cidade<>'' GROUP BY cid"
 *   );
 *   $form->fields['cidades']->updatable = false;
 */
namespace phpdbform;

class PhpdbformMultiListbox extends phpdbform_field
{
    public $form_name;
    public $field;
    public $title;
    public $db;
    public $table;
    public $lbkey;
    public $lbvalue;
    public $order;
    public $where;
    public $key;
    public $cssclass;

    public function __construct($form_name, $field, $title, $db, $table, $key, $value, $order, $where)
    {
        if (empty($value)) {
            $value = $key;
        }
        $this->form_name = $form_name;
        $this->field = $field;
        $this->title = $title;
        $this->db = $db;
        $this->table = $table;
        $this->lbkey = $key;
        $this->lbvalue = $value;
        $this->order = $order;
        $this->where = $where;
        $this->key = $this->form_name . '_' . $this->field;
        $this->cssclass = 'field_multi_listbox';
    }

    public function getString()
    {
        // Preenche listas
        $ret = $this->db->query(
            "select {$this->lbkey}, {$this->lbvalue} from {$this->table} {$this->where} order by {$this->order}",
            0
        );
        $aFrom = $aTo = [];
        while ($row = $this->db->fetch_row($ret)) {
            // Se estiver postado os valores, checar se o item da lista esta na lista de selecionados
            // e nao mostra-lo novamente
            $selected = false;
            if (!empty($_POST["{$this->key}"])) {
            }
            $a = '<option value="'
                . htmlspecialchars($row[0], ENT_COMPAT, 'UTF-8') . '">'
                . htmlspecialchars($row[1], ENT_COMPAT, 'UTF-8') . "</option>\n";
            if (!$selected) {
                $aFrom[] = $a;
            } else {
                $aTo[] = $a;
            }
        }
        $bt1 = $this->key . '_Tudo';
        $bt2 = $this->key . '_Nada';

        return "<script>addOnLoad(function() { tcnxMultiListbox('{$this->key}'); });</script>" .
            '<table class="table"><tr><td>' .
            $this->title . '<br>' .
            "<select multiple class=\"{$this->cssclass}\"
            name=\"{$this->key}_from\" id=\"{$this->key}_from\">\n" .
            implode("\n", $aFrom) .
            "</select>\n" .
            '</td><td align=center valign=center>' .
            '<input type="button" name="' . $bt1 . '" id="' . $bt1 . '" value=">>" title="Todos"><br>'.
            '<input type="button" name="' . $bt2 . '" id="' . $bt2 . '" value="<<" title="Nenhum"><br>'.
            '</td><td>' .
            'Selecionados:<br>' .
            "<select multiple class=\"{$this->cssclass}\" id=\"{$this->key}\" name=\"{$this->key}[]\">\n" .
            implode("\n", $aTo) .
            "</select>\n" .
            '</td></tr></table>';
    }

    public function process()
    {
        if (isset($_POST[$this->key])) {
            $this->value = $_POST[$this->key];
            $this->delmagic();
        }
    }
}
