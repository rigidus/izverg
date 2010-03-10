--TEST--
HTML_MetaFormAction: meta:validator attribute
--FILE--
<?php
require dirname(__FILE__) . '/init.php';
$form = '
    <form method="post" action="abc">
      <input type="text" name="txt" meta:validator="filled">
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('txt'=>'', 'doSave'=>1));
printr($MetaFormAction->process(), 'action');
printr($MetaFormAction->getErrors(), 'errors');

$MetaFormAction =& _newMetaFormAction('abc', $form, array('txt'=>'aaa', 'doSave'=>1));
printr($MetaFormAction->process(), 'action');
printr($MetaFormAction->getErrors(), 'errors');

function validator_my($value)
{
    return false;
}
?>



--EXPECT--
action: NULL
errors: array (
  0 => 
  array (
    'name' => 'txt',
    'message' => NULL,
    'validator' => 'html_metaformaction::validator_filled',
    'meta' => 
    array (
      'validator' => 'filled',
      'type' => 'text',
      'name' => 'txt',
      'value' => '',
    ),
  ),
)
action: 'doSave'
errors: array (
)
