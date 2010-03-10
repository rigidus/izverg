--TEST--
HTML_MetaForm: case of missing signature
--FILE--
<?php
require dirname(__FILE__) . '/init.php';
$MetaForm->MF_POST['something'] = 'value';
$MetaForm->getFormMeta();
printr($MetaForm->getLastError());
?>

--EXPECT--
array (
  0 => 'Hidden field "%s" required for POST form!',
  1 => 'HTML_MetaForm',
)

