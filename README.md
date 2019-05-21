# phpdbform
scripts PHP para ajudar a gerenciar um formulário html para os dados do administrador de banco de dados. com apenas algumas linhas de código php você obtém um formulário html. recursos: fácil gerenciamento, personalizações, listas suspensas fáceis e outros.

Projeto original: https://sourceforge.net/projects/phpdbform/

Basic Usage
```php
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
/**
 * Campo não faz parte da tabela então não faz update
 */
$form->fields['nomes']->updatable = false;
/**
 * Display do formulario na tela
 */
echo $form->fields['nomes']->getString();
echo "<br>";
echo $form->fields['nome']->getString();
echo "<br>";
print $form->fields['obs']->getString();
print "<br>";
/**
 * Criando submit
 */
print $form->draw_submit('Enviar');
print $form->process();

$db->close();
```
By Packagist
cd <your project>
composer require 'phpdbform/phpdbform:dev-master'
