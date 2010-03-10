--TEST--
HTML_MetaForm: case of bad signature
--FILE--
<?php
require dirname(__FILE__) . '/init.php';
$MetaForm->MF_POST['HTML_MetaForm'] = 'some fake value';
$MetaForm->getFormMeta();
printr($MetaForm->getLastError());
?>

--EXPECT--
array (
  0 => 'Form data signature check failed!',
)

