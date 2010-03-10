--TEST--
HTML_MetaFormAction: "image" action
--FILE--
<?php
require dirname(__FILE__) . '/init.php';
$form = '
    <form method="post" action="abc">
      <input type=image name="im1">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('im1_x'=>10, 'im1_y'=>20));
printr(_getPlainMetaOnly($MetaFormAction->metaForm), 'image with simple name');
printr($MetaFormAction->process(), 'action: image with simple name');

$form = '
    <form method="post" action="abc">
      <input type=image name="im2[aa][bb]">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('im2'=>array('aa'=>array('bb'=>30))));
printr(_getPlainMetaOnly($MetaFormAction->metaForm), 'image with complex name');
printr($MetaFormAction->process(), 'action: image with complex name');
?>



--EXPECT--
image with simple name: array (
  'original' => 'abc',
  'name' => NULL,
  'type' => 'form',
  'id' => NULL,
  'items' => 
  array (
    'im1' => 
    array (
      'type' => 'action',
      'name' => 'im1',
      'value' => 
      array (
        0 => 10,
        1 => 20,
      ),
    ),
  ),
)
action: image with simple name: 'im1'
image with complex name: array (
  'original' => 'abc',
  'name' => NULL,
  'type' => 'form',
  'id' => NULL,
  'items' => 
  array (
    'im2[aa][bb]' => 
    array (
      'type' => 'action',
      'name' => 'im2[aa][bb]',
      'value' => 30,
    ),
  ),
)
action: image with complex name: 'im2[aa][bb]'
