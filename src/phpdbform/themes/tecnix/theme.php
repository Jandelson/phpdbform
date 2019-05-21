<?php
/**
 * Desenha Header
 *
 * @param [type] $title         Titulo
 * @param [type] $header_code   código de cabeçalho espera qualquer código que você deseja colocar na tag de cabeçalho
 *  como código javascript ou folhas de estilo
 * @param integer $width        Largura da janela
 * @param [type] $td_miolo      Tags celula miolo
 * @param integer $janela       É uma janela ?
 * @param integer $logo         Conteudo Fora de uso
 * @param string $alinhamento   Alinhamento da janela lista
 * @param integer $psimples     Tipo de janela ? Vide rotinas_pagina/prepara_pagina
 * @param string $paltura       Altura da tabela
 * @param string $alinham2      Alinhamento celula geral tela simples
 *
 * @return void
 */
function draw_adm_header(
    $title,
    $header_code,
    $width = 500,
    $td_miolo = "",
    $janela = 1,
    $logo = 0,
    $alinhamento = "top",
    $psimples = 0,
    $paltura = "\"100%\"",
    $alinham2 = "center"
) {
    global $usuario, $extras, $dbcfg, $gerar_pagina_completa, $html_completo, $charset;
    global $botoes, $tp, $pc, $titulo, $simples, $script;
    global $n_janela, $codemp, $ListaItens;
    global $tem_bordas, $tem_insere, $tem_procura, $flausu;
    global $naoGerarCabecalho;

    static $cabecalho_gerado;

    $prototype = "<script language=\"javascript\" src=\"js/prototype.js\"></script>";

    if (!empty($ListaItens->prototype_off)) {
        $prototype = "";
    }

    $html = ""; // HTML desta rotina

    if ($paltura<>"") {
        $paltura = "height={$paltura}";
    }

    $tem_bordas  = in_array($psimples, array(0,3,4,5));
    $tem_insere  = in_array($psimples, array(0,1,5));
    $tem_procura = in_array($psimples, array(0,1,2,3));

    //print "janela=$janela simples=$simples botoes=$botoes pc=$pc tp=$tp<br>";
    $simples=$psimples;
    if (!$cabecalho_gerado) {
        $cabecalho_gerado = true;
        $style = (!empty($dbcfg['estilo_extra'])?
            $dbcfg['estilo_extra']:
            "estilo").".css";
        if (file_exists("../config/{$codemp}/{$style}")) {
            $style = "<link href=\"/config/{$codemp}/{$style}\" rel=\"stylesheet\" type=\"text/css\">";
        } else {
            $style = "";
        }

        // Nao informar .dtd, ou muda o posicionamento das "TD" para centralizado
        //<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"\">
        // Esta dando problema em varias telas... por causa do doctype,
        // achar o que seria equivalente a nao colocar nada (Quirks mode)
        // <!DOCTYPE html> Assim que possivel
        if (empty($naoGerarCabecalho)) {
            $html .= "<!DOCTYPE html>
            <html>
            <head>
            <title>{$title}</title>
            <meta http-equiv=\"Content-Type\" content=\"text/html;\" charset=\"{$charset}\">
            <script language=\"javascript\" src=\"js/rotinas3-1.0.js\"></script>
            <script language=\"javascript\" src=\"js/rotinas5.js\"></script>
            <script language=\"javascript\" src=\"js/rotinas8.js\"      ></script>
            <script language=\"javascript\" src=\"js/rotinas_menu.js\"  ></script>
            {$prototype}
            <link href=\"css/{$dbcfg['estilo']}.css\" rel=\"stylesheet\" type=\"text/css\">
            $style
            <!-- Header code -->
            $header_code
            <!-- Fim Header code -->
            </head>";
        }

        $html .= "
        <body
            onload=\"body_onload2(); body_onload(event);\"
            onunload=\"body_onunload(event);\"
            onbeforeunload=\"body_onbeforeunload(event);\"
            onkeypress=\"body_onkeypress(event);\"
            onkeydown=\"body_onkeydown(event);\"
            onkeyup=\"body_onkeyup(event);\"
            onresize=\"body_onresize(event);\"
            topmargin=0 leftmargin=0 rightmargin=0 bottommargin=0>
        ";
    }

    $html .= "<!-- Tabela janela toda -->\n";
    $html .= "<table id=tabela_janela_toda width=\"100%\" $paltura cellspacing=\"0\" cellpadding=\"0\">\n";
    if (!$tem_bordas) {
        $html .= "<tr valign=\"top\" class=\"form_miolo\"";
    } else {
        $html .= "<tr valign=\"{$alinham2}\" width=\"100%\"";
    }
    $html .= " height=\"100%\"><td>\n";

    if ($janela==1) {
        if ($tem_bordas) {
            $html .= "<td align=center valign=\"{$alinhamento}\">";
            $html .= "<table border=0 cellpadding=0 cellspacing=0 width=\"{$width}\">\n"; // Todas linhas da janela
            $html .= "<!-- Divisor -->\n";
            $html .= '<tr><td><table cellspacing=0 cellpadding=0 witdh=100%><tr>';
            $html .= '<td width="8"><img src="/sistema/library/phpdbform/themes/tecnix/images/spacer.gif" width="8" height="1" border=0 title=""></td>';
            $html .= '<td width="18"><img src="/sistema/library/phpdbform/themes/tecnix/images/spacer.gif" width="18" height="1" border=0 title=""></td>';
            $html .= '<td width="100%"><img src="/sistema/library/phpdbform/themes/tecnix/images/spacer.gif" width="288" height="1" border=0 title=""></td>';
            $html .= '<td width="54"><img src="/sistema/library/phpdbform/themes/tecnix/images/spacer.gif" width="54" height="1" border=0 title=""></td>';
            $html .= '<td width="8"><img src="/sistema/library/phpdbform/themes/tecnix/images/spacer.gif" width="8" height="1" border=0 title=""></td>';
            $html .= "</tr></table></td></tr>\n";
            $html .= "<!-- Titulo -->\n";
            $html .= '<tr><td><table id=barra_titulo cellspacing=0 cellpadding=0 width=100%><tr>';
            $html .= '<td><img src="/sistema/library/phpdbform/themes/tecnix/images/lgth_tp_esq.png" width="26" height="23" border=0 title=""></td>';
            $html .= "<td width=100% background=\"/sistema/library/phpdbform/themes/tecnix/images/lgth_tp_meio.png\" class=\"titulo\">{$title}</td>";
            $html .= '<td><img src="/sistema/library/phpdbform/themes/tecnix/images/lgth_tp_dir.png" width="62" height="23" border=0 title="" usemap="#Menu"></td>';
            $html .= "</tr></table></td></tr>\n";
            $html .= '<!-- Icones -->';
            $html .= "<tr><td><table cellspacing=0 cellpadding=0 width=100%><tr>";
            $html .= "<td style='background:#D0D0CF; width:1px;'></td>";
            $html .= "<td style='background:#FFFFFF; width:1px;'></td>";
            $html .= "<td style='background:#D0D0CF; width:6px;'></td>";
            $html .= "<td bgcolor=#D0D0CF valign=\"middle\">\n"; // Todos controles da linha
        }

        $html .= "<!-- Tabela dos botoes da barra de ferramentas -->\n";
        $html .= "<table id=barra_ferramentas border=0 cellpadding=4 cellspacing=0 width=\"100%\"><tr>\n";

        // Botoes extras
        if (!empty($extras)) {
            if (isset($extras->chave)) {
                reset($extras->chave);
                while (list($chave,$valor) = each($extras->chave)) {
                    if ($extras->tipo[$chave] == 1) {
                        $html .= "<td>";
                        $html .= "<a href=\"javascript: {$extras->valor[$chave]}\">";
                        $html .= "<img border=0 src=\"/sistema/imagens/{$extras->chave[$chave]}.gif\" title=\"{$extras->hint[$chave]}\">";
                        $html .= "</a></td>\n";
                    }
                }
            }
            if ($botoes==0 or $botoes==1) {
                if ($tem_insere) {
                    if ($flausu & FLAG_INCLUSAO) {
                        $html .= "<td><a href=\"javascript: insere($n_janela);\">";
                        $html .= "<img border=0 src=\"/sistema/imagens/inclui.gif\" title=\"Insere\">";
                        $html .= "</a></td>\n";
                    }
                }
            }
        }

        // Botoes padrao
        if ($botoes) {
            $html .= "<script> function body_onload() {try {procura.focus();} catch(erro) {} } </script>\n";
            if ($botoes<>4 and $tem_procura) {
                $html .= botoes_procura();
            }
            $html .= botoes_pagina();
        }

        $html .= "{$extras->Hdr_Barra}";
        if ($tem_insere or isset($extras->chave)) {
            $html .= "<td width=\"100%\"></td>"; // Completa a linha
        }
        $html .= "</tr>";
        $html .= "</table><!-- Fecha botoes --> \n";

        if ($tem_bordas) {
            $html .= '</td>';
            $html .= "<td bgcolor=#D0D0CF width=6></td>";
            $html .= "<td bgcolor=#808080 width=1></td>";
            $html .= "<td bgcolor=#404040 width=1></td>";
            $html .= "</tr></table></td></tr>\n"; // Fecha linha de icones

            $html .= "<!-- Divisor -->\n";
            $html .= '<tr><td><table cellspacing=0 cellpadding=0 width=100%><tr>';
            $html .= '<td><img src="/sistema/library/phpdbform/themes/tecnix/images/lgth_ct_tp_eq.png" width="8" height="8" border=0 title=""></td>';
            $html .= '<td width=100% background="/sistema/library/phpdbform/themes/tecnix/images/lgth_ct_tp_md.png"><img src="/sistema/library/phpdbform/themes/tecnix/images/spacer.gif" width="4" height="8" border=0 title=""></td>';
            $html .= '<td><img src="/sistema/library/phpdbform/themes/tecnix/images/lgth_ct_tp_dr.png" width="8" height="8" border=0 title=""></td>';
            $html .= "</tr></table></td></tr>\n"; // Fecha divisor

            $html .= "<!-- Miolo -->\n";
            $html .= "<tr><td><table cellspacing=0 cellpadding=0 width=100%><tr>";
            $html .= "<td background=\"/sistema/library/phpdbform/themes/tecnix/images/lgth_ct_eq.png\"><img src=\"/sistema/library/phpdbform/themes/tecnix/images/spacer.gif\" width=\"8\" height=\"128\" border=0 title=\"\"></td>";
            $html .= "<td width=100% valign=\"top\" class=\"conteudo\">\n";

            $html .= "<!-- Fundo branco da tela -->\n";
            $html .= "<table width=\"100%\" class=\"table-bordered\"><tr><td valign=\"center\" class=\"conteudo\">\n";
        } else {
            if (is_numeric($td_miolo)) {
                $td_miolo = "";// Compatibilizar, pois parametro vem sempre 0 ou 1
            }
            $html .= "<!-- Miolo da tabela -->\n";
            $html .= "<table width=\"100%\" border=0><tr><td $td_miolo>\n";
        }
    }

    if ($gerar_pagina_completa) {
        $html_completo .= $html;
    } else {
        print $html;
    }
}

function draw_adm_footer($janela = 1)
{
    global $simples,$dbcfg,$it,$total_reg,$db,$events, $tem_bordas, $html_completo;

    if (($janela!=0) and ($tem_bordas)) {
        $html_completo .=
            "</td></tr></table><!-- Fecha fundo branco -->\n".
            "</td><!-- Fecha miolo -->\n".
            '<td background="/sistema/library/phpdbform/themes/tecnix/images/lgth_ct_dr.png"><img src="/sistema/library/phpdbform/themes/tecnix/images/spacer.gif" width="8" height="128" border="0" title=""></td>'.
            "</tr></table></td></tr>\n". // Fecha Miolo

            "<!-- Fechamento janela -->\n".
            "<tr><td><table cellspacing=0 cellpadding=0 width=100%><tr>".
            '<td><img src="/sistema/library/phpdbform/themes/tecnix/images/lgth_ct_bx_eq.png" width="8" height="8" border="0" title=""></td>'.
            '<td width=100% background="/sistema/library/phpdbform/themes/tecnix/images/lgth_ct_bx_md.png"><img src="/sistema/library/phpdbform/themes/tecnix/images/spacer.gif" width="1" height="8" border="0" title=""></td>'.
            '<td><img src="/sistema/library/phpdbform/themes/tecnix/images/lgth_ct_bx_dr.png" width="8" height="8" border="0" title=""></td>'.
            "</tr></table></td></tr>\n". // Fecha rodape

            "</table>".
            "</td></tr></table>".
            '<map name="Menu">';
        if ($_SESSION["dbform"]["logged"]) {
            $html_completo .= "<area title=\"Fechar\" coords=\"40,6,56,20\" href=\"{$dbcfg["conteudo"]}\">";
        }
        $html_completo .= "</map>\n";
    }
    $html_completo .= "\n<script>\n";
    if ($it<$total_reg) {
        $html_completo .=
            "// Desativa botoes esta na ultima pagina\n".
            "try {\n".
            "  btn_prox.style.visibility=\"hidden\";\n".
            "  btn_fim.style.visibility=\"hidden\"; }\n".
            "catch(erro) {}\n";
    }

    // 04/01/07 18:48:13 - Corrigi bug do explorer 7 (Tela fica em branco...) mudando os scripts para apos o BODY !!
    $html_completo .=
        "// Eventos\n".
        $events.
        "</script>\n".
        "</table>\n".
        "<!-- Fim tabela janela toda -->\n".
        "</body></html>\n".
        "<!-- Fim da pagina -->\n";

    if ($db->database<>"") {
        @$db->close();
    }

    // Descarrega pagina montada
    print $html_completo;
    flush();
}

function botoes_procura()
{
    global $script, $extras;
    $html = "";
    $html .= "<td>";
    $html .= "<input type=text id=procura name=procura size=20 valign=center onkeyup=ProcessaEnter(event); tabindex=90>";
    if (isset($extras->Hdr_procura)) {
        $html .= $extras->Hdr_procura;
    }
    $html .= "</td>\n";
    $html .= "<td>";
    $html .= "<a id=procura_href href=\"javascript: localiza('{$script}');\">";
    $html .= "<img border=0 src='/sistema/imagens/localiza.gif' title='Procura'></a>";
    $html .= "</td>\n";
    return $html;
}

function botoes_pagina()
{
    global $pc, $tp;
    $html = "";
    if ($pc>1) {
        $html .= '<td>';
        $html .= '<a href="javascript: inicio();">';
        $html .= '<img border=0 src="/sistema/imagens/setacima.gif" title="Inicio"></a>';
        $html .= "</td>\n";
        $html .= '<td>';
        $html .= '<a href="javascript: anterior();">';
        $html .= '<img border=0 src="/sistema/imagens/anterior.gif" title="Anterior"></a>';
        $html .= "</td>\n";
    }
    if ($pc<$tp) {
        $html .= '<td>';
        $html .= '<a href="javascript: proxima();" >';
        $html .= '<img border=0 src="/sistema/imagens/proxima.gif" title="Proxima"></a>';
        $html .= '</td>';

        if ($tp<999999) {
            $html .= '<td>';
            $html .= '<a href="javascript: fim();" name="btn_fim">';
            $html .= '<img border=0 src="/sistema/imagens/setabaixo.gif" title="Ultima"></a>';
            $html .= '</td>';
        }
    }
    return $html;
}
