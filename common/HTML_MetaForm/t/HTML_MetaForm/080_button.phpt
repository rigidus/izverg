--TEST--
HTML_MetaForm: BUTTON container
--FILE--
<?php
require dirname(__FILE__) . '/init.php';
ob_start();
?>
<form method="post" action="abc">
  <input type=text name=txt1 default="1.1" value="" meta:a="b"><br/>
  <button type="submit" name="doSave">Save!</button>
</form>
<?php
printr(_getMeta($MetaForm->process(ob_get_clean())));
?>



--EXPECT--
array (
  'original' => 'abc',
  'name' => NULL,
  'type' => 'form',
  'id' => NULL,
  'items' => 
  array (
    'txt1' => 
    array (
      'a' => 'b',
      'type' => 'text',
      'name' => 'txt1',
      'value' => NULL,
    ),
    'doSave' => 
    array (
      'type' => 'action',
      'name' => 'doSave',
      'value' => NULL,
    ),
  ),
  'tree' => 
  array (
    'txt1' => 
    array (
      'a' => 'b',
      'type' => 'text',
      'name' => 'txt1',
      'value' => NULL,
    ),
    'doSave' => 
    array (
      'type' => 'action',
      'name' => 'doSave',
      'value' => NULL,
    ),
  ),
  'value' => 
  array (
    'txt1' => NULL,
    'doSave' => NULL,
  ),
)

