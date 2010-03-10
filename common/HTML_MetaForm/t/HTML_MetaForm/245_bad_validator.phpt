--TEST--
HTML_MetaFormAction: bad "select multiple" tag value
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$form = '
    <form method="post" action="abc">
      <input type=text name=aaa meta:validator="unreged">
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('hid'=>1, 'doSave'=>1));
printr($MetaFormAction->process(), 'bad action');
printr($MetaFormAction->getErrors(), 'errors');
?>



--EXPECT--
bad action: NULL
errors: array (
  0 => 
  array (
    'name' => 'aaa',
    'message' => 
    array (
      0 => 'Validator %s is not registered!',
      1 => 'validator_unreged',
    ),
    'validator' => NULL,
    'meta' => 
    array (
      'validator' => 'unreged',
      'type' => 'text',
      'name' => 'aaa',
      'value' => NULL,
    ),
  ),
)

