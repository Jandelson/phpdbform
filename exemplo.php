<?php

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Cria conexão com o banco de dados
 */
$db = new phpdbform\phpdbform_pdo("phpdbform", "localhost", "root", "");
$db->connect();

/**
 * Consulta banco de dados
 */
$sql = $db->query("select nome from contato");
$nomes = $virgula = '';
while ($dad = $db->fetch_array($sql)) {
    $nomes .= $virgula . $dad['id_contato'] . ';' . $dad['nome'];
    $virgula = ',';
}
/**
 * Criação dos forms
 */
$form = new \phpdbform\phpdbform_db($db, 'contato', 'id_contato', '', '');
$form->draw_header();
$form->add_textbox('nome', 'Nome:', 70, 0, true);
$form->add_textarea('obs', 'Observações', 70, 5);
$form->add_static_listbox('nomes', 'Contatos Adicionados', $nomes);

$form->add_static_radiobox('genero_vertical', 'Genero: ', '1;Masculino,2;Feminino,3;Outros', 'v');
$form->add_static_radiobox('genero_orizontal', 'Genero: ', '1;Masculino,2;Feminino,3;Outros', 'h');
/**
 * Campo não faz parte da tabela então não faz update
 */
$form->fields['nomes']->updatable = false;
$form->fields['genero_vertical']->updatable = false;
$form->fields['genero_orizontal']->updatable = false;
/**
 * Display do formulario na tela
 */
echo $form->fields['nomes']->getString();
echo "<br>";
echo $form->fields['nome']->getString();
echo "<br>";
print $form->fields['obs']->getString();

print "<h2>Elemento Radio Button</h2>";
print $form->fields['genero_vertical']->getString();
print "<br>";
print $form->fields['genero_orizontal']->getString();
/**
 * Criando submit
 */
print $form->draw_submit('Enviar');
print $form->process();

$db->close();
