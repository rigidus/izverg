<?php ## t_action.php: process simple action.
include_once "../../lib/config.php";
include_once "../../HTML_FormPersister/lib/config.php";
include_once "HTML/MetaForm.php"; 
include_once "HTML/MetaFormAction.php"; 

// Assign output processor to pass metadata.
$metaForm =& new HTML_MetaForm('secret_digital_signature_YS0lTgit');
ob_start(array(&$metaForm, 'process'));

// Now process the form (if posted).
$metaFormAction =& new HTML_MetaFormAction($metaForm);
switch ($metaFormAction->process()) {
	case 'INIT':
		// Called when script is called via GET method.
		// No buttons are pressed yet, initialize form fields.
		break;
		
	case 'doSend':
		// Called when doSend is pressed and THERE ARE 
		// NO VALIDATION ERRORS! Process the form.
		break;
}
?>

<form method="POST">
  <label for="t">Anything</label>:
  <input type="text" name="test" id="t" meta:validator="filled"><br>
  Select:
  <select name="sel">
    <option value="a">aaa</option>
    <option value="b">bbb</option>
  </select><br>
  <input type="submit" name="doSend" value="Send!">
</form>
<pre>
Errors: <?print_r($metaFormAction->getErrors())?>
</pre>