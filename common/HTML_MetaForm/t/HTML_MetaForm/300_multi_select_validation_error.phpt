--TEST--
HTML_MetaFormAction: error generation for SELECTs
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

function errorHandler($error)
{
    printr($error, 'error');
}

echo "SELECT MULTIPLE with wrong option\n";
$form = '
    <form method="post" action="abc">
      <select name=sel[a][] multiple>
      <option value="1">aaa
      <option value="2">bbb
      </option>
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('sel'=>array('a'=>array(0, 2)), 'doSave'=>1), 'errorHandler');
printr($MetaFormAction->process(), 'action');
echo "\n";


echo "SELECT SINGLE with wrong option\n";
$form = '
    <form method="post" action="abc">
      <select name=sel>
      <option value="1">aaa
      <option value="2">bbb
      </option>
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('sel'=>3, 'doSave'=>1), 'errorHandler');
printr($MetaFormAction->process(), 'action');
echo "\n";


echo "SELECT MULTIPLE with failed validator\n";
$form = '
    <form method="post" action="abc">
      <label for="sel">Select!</label>
      <select name=sel[a][] multiple id="sel" meta:validator="filled">
      <option value="1">aaa
      <option value="2">bbb
      </option>
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('sel'=>array('a'=>array()), 'doSave'=>1), 'errorHandler');
printr($MetaFormAction->process(), 'action');
?>


--EXPECT--
SELECT MULTIPLE with wrong option
error: array (
  'name' => 'sel[a][]',
  'message' => 
  array (
    0 => 'Field "%s" (%s) contains non-existed value(s): expected %s, got %s!',
    1 => 'sel[a][]',
    2 => 'multiple',
    3 => '(1|2)',
    4 => '(0, 2)',
  ),
  'validator' => NULL,
  'meta' => 
  array (
    'type' => 'multiple',
    'items' => 
    array (
      1 => 'aaa',
      2 => 'bbb',
    ),
    'name' => 'sel[a]',
    'value' => 
    array (
      0 => 0,
      1 => 2,
    ),
  ),
)
action: NULL

SELECT SINGLE with wrong option
error: array (
  'name' => 'sel',
  'message' => 
  array (
    0 => 'Field "%s" (%s) contains non-existed value(s): expected %s, got %s!',
    1 => 'sel',
    2 => 'single',
    3 => '(1|2)',
    4 => '\'3\'',
  ),
  'validator' => NULL,
  'meta' => 
  array (
    'type' => 'single',
    'items' => 
    array (
      1 => 'aaa',
      2 => 'bbb',
    ),
    'name' => 'sel',
    'value' => 3,
  ),
)
action: NULL

SELECT MULTIPLE with failed validator
error: array (
  'name' => 'sel[a][]',
  'message' => NULL,
  'validator' => 'html_metaformaction::validator_filled',
  'meta' => 
  array (
    'validator' => 'filled',
    'type' => 'multiple',
    'id' => 'sel',
    'items' => 
    array (
      1 => 'aaa',
      2 => 'bbb',
    ),
    'name' => 'sel[a]',
    'label' => 'Select!',
    'value' => 
    array (
    ),
  ),
)
action: NULL

