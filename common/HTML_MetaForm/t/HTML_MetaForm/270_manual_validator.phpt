--TEST--
HTML_MetaFormAction: meta:validator="...:manual"
--FILE--
<?php
require dirname(__FILE__) . '/init.php';
$form = '
    <form method="post" action="abc">
      <input type="text" name="txt" meta:validator="filled">
      <input type="text" name="m" meta:validator="filled:manual">
      <input type="text" name="m1" meta:validator="filled:manual">
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('txt'=>'123', 'doSave'=>1));
printr($MetaFormAction->process(), 'action');
printr($MetaFormAction->getErrors(), 'errors');

$MetaFormAction =& _newMetaFormAction('abc', $form, array('txt'=>'123', 'doSave'=>1));
printr($MetaFormAction->process(), 'action');
printr($MetaFormAction->process('m'), 'action');
printr($MetaFormAction->getErrors(), 'errors');

$MetaFormAction =& _newMetaFormAction('abc', $form, array('txt'=>'123', 'doSave'=>1));
printr($MetaFormAction->process(), 'action');
printr($MetaFormAction->process(array('m', 'm1')), 'action');
printr($MetaFormAction->getErrors(), 'errors');
?>



--EXPECT--
action: 'doSave'
errors: array (
)
action: 'doSave'
action: NULL
errors: array (
  0 => 
  array (
    'name' => 'm',
    'message' => NULL,
    'validator' => 'html_metaformaction::validator_filled',
    'meta' => 
    array (
      'validator' => 'filled:manual',
      'type' => 'text',
      'name' => 'm',
      'value' => NULL,
    ),
  ),
)
action: 'doSave'
action: NULL
errors: array (
  0 => 
  array (
    'name' => 'm',
    'message' => NULL,
    'validator' => 'html_metaformaction::validator_filled',
    'meta' => 
    array (
      'validator' => 'filled:manual',
      'type' => 'text',
      'name' => 'm',
      'value' => NULL,
    ),
  ),
  1 => 
  array (
    'name' => 'm1',
    'message' => NULL,
    'validator' => 'html_metaformaction::validator_filled',
    'meta' => 
    array (
      'validator' => 'filled:manual',
      'type' => 'text',
      'name' => 'm1',
      'value' => NULL,
    ),
  ),
)
