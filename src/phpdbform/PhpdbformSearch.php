<?php
/**
 * Controle de pesquisa com sugestao
 * Criado em Wed Feb 06 2019
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
 * @todo
 */
namespace phpdbform;

class PhpdbformSearch extends phpdbform_field
{
    public function __construct($form_name, $field, $title, $url)
    {
        $this->form_name = $form_name;
        $this->field = $field;
        $this->title = $title;
        $this->key = $this->form_name . '_' . $this->field;
        $this->cssclass = 'field_search';
        $this->url = $url;
    }

    public function getString()
    {
        if (!empty($this->onblur)) {
            $javascript = "onblur=\"{$this->onblur}\"";
        } else {
            $javascript = '';
        }

        if ($this->value > 0) {
            $url = $this->url;
            list($url, $params) = explode('?', $this->url);
            $url = 'http://localhost/sistema/rest/' . $url . '/' . $this->value . '?' . $params;
            $json = $this->httpRequest(true, '', $url, 1, 'GET');
            $json[0]['name'] = strip_tags($json[0]['name']);
        } else {
            $json[0]['name'] = '';
        }

        return "<script>addOnLoad(function() { tcnxProcura('{$this->key}', '{$this->url}'); });</script>"
            . "<tr><td colspan=99>"
            . "<table width='100%' cellspacing=0 cellpadding=0><tbody><tr>"
            . "<td align='left' nowrap>{$this->title}</td>"
            . "<td align='right' nowrap><a href=\"javascript:tcnxProcuraLB('{$this->key}');\">Limpar</a></td>"
            . '</tr></tbody></table>'
            . "<input type=hidden id='{$this->key}' name='{$this->key}' value='"
            . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . "'>"
            . "<input type=text id='txt{$this->key}' "
            . "name='txt{$this->key}' value='{$json[0]['name']}' style='width:100%' "
            . "$javascript {$this->tag_extra}>"
            . "</td></tr>\n";
    }

    public function process()
    {
        if (isset($_POST[$this->key])) {
            $this->value = $_POST[$this->key];
            $this->delmagic();
        }
    }
}
