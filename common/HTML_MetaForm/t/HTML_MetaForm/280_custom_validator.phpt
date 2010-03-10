--TEST--
HTML_MetaFormAction: meta:validator attribute
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$form = '
    <form method="post" action="abc">
      <input type="text" name="txt" meta:validator="my">
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('txt'=>'', 'doSave'=>1));
printr($MetaFormAction->process(), 'action');
printr($MetaFormAction->getErrors(), 'errors');

function validator_my($value)
{
    printr('validator called!');
    return true;
}


$form = '
    <form method="post" action="abc">
      <input type="text" name="txt" meta:validator="my_array">
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('txt'=>'', 'doSave'=>1));
printr($MetaFormAction->process(), 'action');
printr($MetaFormAction->getErrors(), 'errors');

function validator_my_array($value)
{
    printr('validator called!');
    return array('error key', 1, 2, 3);
}
?>



--EXPECT--
'validator called!'
action: 'doSave'
errors: array (
)
'validator called!'
action: NULL
errors: array (
  0 => 
  array (
    'name' => 'txt',
    'message' => 
    array (
      0 => 'error key',
      1 => 1,
      2 => 2,
      3 => 3,
    ),
    'validator' => 'validator_my_array',
    'meta' => 
    array (
      'validator' => 'my_array',
      'type' => 'text',
      'name' => 'txt',
      'value' => '',
    ),
  ),
)

