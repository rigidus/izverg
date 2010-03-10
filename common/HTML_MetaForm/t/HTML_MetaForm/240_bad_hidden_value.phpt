--TEST--
HTML_MetaFormAction: bad "select multiple" tag value
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$form = '
    <form method="post" action="abc">
      <input type=hidden name=hid value=123>
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('hid'=>1, 'doSave'=>1));
printr($MetaFormAction->process(), 'bad action');
printr($MetaFormAction->getErrors(), 'errors');

$form = '
    <form method="post" action="abc">
      <input type=hidden name=hid value=123 meta:dynamic="44 1 33">
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('hid'=>1, 'doSave'=>1));
printr($MetaFormAction->process(), 'good action');
printr($MetaFormAction->getErrors(), 'errors');
?>



--EXPECT--
bad action: NULL
errors: array (
  0 => 
  array (
    'name' => 'hid',
    'message' => 
    array (
      0 => 'Field "%s" (%s) contains invalid value: expected %s, got %s!',
      1 => 'hid',
      2 => 'text',
      3 => '\'123\'',
      4 => '\'1\'',
    ),
    'validator' => NULL,
    'meta' => 
    array (
      'type' => 'text',
      'original' => '123',
      'name' => 'hid',
      'value' => 1,
    ),
  ),
)
good action: 'doSave'
errors: array (
)

