--TEST--
HTML_MetaForm: using of SESSION to store sigature
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$form = '
    <form method="post">
    <select name="sel">
    <option value="val1">key1</option>
    <option value="val2" selected>key2</option>
    </select>
    </form>
';
$MetaForm->MF_USE_SESSION = true;
printr(_getMeta($MetaForm->process($form)));
printr($_SESSION);
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
  'HTML_MetaForm' => 
  array (
    '47a3aa50641e9690988aaafc32264274' => 'dafcce62f69397b63b747733f9658bf2-enicTU5BCsMwDPuLX7Cm6xjKH/YHw7wSmqSjKYMy8vfFa0pzsiTLkhkDvgl30Ly40UX2ZBMuIB1XUOQgZB87Wbe3VP01L0GhAbnnbhgKXCUksoxOQ3tQEq+0V9oE3MrGxdH/SXtnqvHDvqtNk2wHLKo51QJz8+PZl3P+AfcHQBQ=',
  ),
)

