--TEST--
HTML_MetaFormAction: hidden field auto-arrays
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$form = '
    <form method="post" action="abc">
      <input type="hidden" name="array[]" value="111">
      <input type="hidden" name="array[]" value="222">
      <input type="hidden" name="array[]" value="333">
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('array'=>array(111, 222, 333), 'doSave'=>1));
printr($MetaFormAction->metaForm->getFormMeta(), 'meta');
printr($MetaFormAction->process(), 'good action');
printr($MetaFormAction->getErrors(), 'errors');
?>



--EXPECT--
meta: array (
  'original' => 'abc',
  'name' => NULL,
  'type' => 'form',
  'id' => NULL,
  'items' => 
  array (
    'array[]' => 
    array (
      'type' => 'text',
      'original' => '333',
      'name' => 'array[]',
      'value' => 111,
    ),
    'doSave' => 
    array (
      'type' => 'action',
      'name' => 'doSave',
      'value' => 1,
    ),
  ),
  'tree' => 
  array (
    'array' => 
    array (
      0 => 
      array (
        'type' => 'text',
        'original' => '333',
        'name' => 'array[]',
        'value' => 111,
      ),
    ),
    'doSave' => 
    array (
      'type' => 'action',
      'name' => 'doSave',
      'value' => 1,
    ),
  ),
  'value' => 
  array (
    'array' => 
    array (
      0 => 111,
    ),
    'doSave' => 1,
  ),
)
good action: NULL
errors: array (
  0 => 
  array (
    'name' => 'array[]',
    'message' => 
    array (
      0 => 'Field "%s" (%s) contains invalid value: expected %s, got %s!',
      1 => 'array[]',
      2 => 'text',
      3 => '\'333\'',
      4 => '\'111\'',
    ),
    'validator' => NULL,
    'meta' => 
    array (
      'type' => 'text',
      'original' => '333',
      'name' => 'array[]',
      'value' => 111,
    ),
  ),
)

