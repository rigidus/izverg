--TEST--
HTML_MetaForm: store OPTION labels
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$form = '
    <form method="post">
    <select name="sel">
    <option value="val1">key1</option>
    <option value="val2">key2</option>
    </select>
    </form>
';
printr(_getMeta($MetaForm->process($form)));

$form = '
    <form method="post">
    <select name="sel">
    <option value="val1">key1</option>
    <option value="val2">key2</option>
    </select>
    </form>
';
$MetaForm =& new HTML_MetaForm('test_signature');
$MetaForm->MF_STORE_LABELS = false;
printr(_getMeta($MetaForm->process($form)));
?>


--EXPECT--
array (
  'original' => '',
  'name' => NULL,
  'type' => 'form',
  'id' => NULL,
  'items' => 
  array (
    'sel' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        'val1' => 'key1',
        'val2' => 'key2',
      ),
      'name' => 'sel',
      'value' => NULL,
    ),
  ),
  'tree' => 
  array (
    'sel' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        'val1' => 'key1',
        'val2' => 'key2',
      ),
      'name' => 'sel',
      'value' => NULL,
    ),
  ),
  'value' => 
  array (
    'sel' => NULL,
  ),
)
array (
  'original' => '',
  'name' => NULL,
  'type' => 'form',
  'id' => NULL,
  'items' => 
  array (
    'sel' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        'val1' => NULL,
        'val2' => NULL,
      ),
      'name' => 'sel',
      'value' => NULL,
    ),
  ),
  'tree' => 
  array (
    'sel' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        'val1' => NULL,
        'val2' => NULL,
      ),
      'name' => 'sel',
      'value' => NULL,
    ),
  ),
  'value' => 
  array (
    'sel' => NULL,
  ),
)

