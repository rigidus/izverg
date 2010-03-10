--TEST--
HTML_MetaFormAction: bad action attribute
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$form = '
    <form method="post" action="abc">
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('def', $form, array('doSave'=>1));
printr($MetaFormAction->process(), 'action');
printr($MetaFormAction->getErrors(), 'errors');
//printr(_getPlainMetaOnly($MetaFormAction->metaForm), 'meta');
?>



--EXPECT--
action: NULL
errors: array (
  0 => 
  array (
    'name' => NULL,
    'message' => 
    array (
      0 => 'Bad FORM "action" attribute: expected %s, got %s!',
      1 => '\'abc\'',
      2 => '\'def\'',
    ),
    'validator' => NULL,
    'meta' => NULL,
  ),
)

