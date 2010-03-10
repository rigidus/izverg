--TEST--
HTML_MetaFormAction: bad "select multiple" tag value
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$form = '
    <form method="post" action="abc">
      <select name=sel[] multiple>
      <option value="1">
      <option value="2">
      </option>
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('sel'=>array(0, 1), 'doSave'=>1));
printr($MetaFormAction->process(), 'bad action');
printr($MetaFormAction->getErrors(), 'errors');
//printr(_getPlainMetaOnly($MetaFormAction->metaForm), 'meta');

$MetaFormAction =& _newMetaFormAction('abc', $form, array('sel'=>array(1, 2), 'doSave'=>1));
printr($MetaFormAction->process(), 'good action');
?>



--EXPECT--
bad action: NULL
errors: array (
  0 => 
  array (
    'name' => 'sel[]',
    'message' => 
    array (
      0 => 'Field "%s" (%s) contains non-existed value(s): expected %s, got %s!',
      1 => 'sel[]',
      2 => 'multiple',
      3 => '(1|2)',
      4 => '(0, 1)',
    ),
    'validator' => NULL,
    'meta' => 
    array (
      'type' => 'multiple',
      'items' => 
      array (
        1 => '',
        2 => '',
      ),
      'name' => 'sel',
      'value' => 
      array (
        0 => 0,
        1 => 1,
      ),
    ),
  ),
)
good action: 'doSave'

