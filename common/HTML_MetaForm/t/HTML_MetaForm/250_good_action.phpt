--TEST--
HTML_MetaFormAction: action
--FILE--
<?php
require dirname(__FILE__) . '/init.php';
$form = '
    <form method="post" action="abc">
      <input type="submit" name="doSave">
    </form>
';
$MetaFormAction =& _newMetaFormAction('abc', $form, array('doSave'=>1));
printr($MetaFormAction->process(), 'action');
printr($MetaFormAction->getErrors(), 'errors');
//printr(_getPlainMetaOnly($MetaFormAction->metaForm), 'meta');

$MetaFormAction =& _newMetaFormAction('abc', $form, null);
printr($MetaFormAction->process(null, 'test'), 'default action');
?>



--EXPECT--
action: 'doSave'
errors: array (
)
default action: 'test'

