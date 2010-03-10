<?php ## t_errors.php: display validation errors.
include_once "../../lib/config.php";
include_once "../../HTML_FormPersister/lib/config.php";
include_once "HTML/MetaForm.php"; 
include_once "HTML/MetaFormAction.php"; 
include_once "HTML/FormPersister.php"; 

// Assign output processor to pass metadata.
$metaForm =& new HTML_MetaForm('secret_digital_signature_YS0lTgit');
ob_start(array(&$metaForm, 'process'));

// Turn on FormPersister for all HTML forms.
ob_start(array('HTML_FormPersister', 'ob_formpersisterhandler'));

// Now process the form (if posted).
$metaFormAction =& new HTML_MetaFormAction($metaForm);
switch ($metaFormAction->process()) {
	case 'INIT':
		// Called when script is called via GET method.
		// No buttons are pressed yet, initialize form fields.
		$_POST['age'] = rand(10, 20);
		break;
		
	case 'doSend':
		// Called when doSend is pressed and THERE ARE 
		// NO VALIDATION ERRORS! Process the form.
		break;
}

// Return true if value is natural number.
function validator_natural($value)
{
    return is_numeric($value) && $value >= 1;
}
?>

<!-- Show error texts. -->
<div style="background:#FFBBBB; margin-bottom:1em">
  <?foreach ($metaFormAction->getErrors() as $e) {?>
    <?if ($m = $e['message']) {?>
      <?=join(", ", $m)?>
    <?} else {?>
      "<?=preg_replace('/.*::/', '', $e['validator'])?>"
      failed for field "<?=$e['meta']['label']?>"!
    <?}?>
    <br>
  <?}?>
</div>

<!-- Display the form. -->
<form method="POST">
  <label for="t1">First name</label>:
  <input type="text" name="first" id="t1" meta:validator="filled"><br>
  <label for="t2">Age</label>:
  <input type="text" name="age" id="t2" meta:validator="natural"><br>
  <input type="submit" name="doSend" value="Send!">
</form>

<!-- Highlight error fields. -->
<script>
<?foreach ($metaFormAction->getErrors() as $e) {?>
  var e = document.getElementById('<?=@$e['meta']['id']?>')
    || document.getElementsByName('<?=@$e['name']?>')[0];
  e.style.border = '2px solid red';
<?}?>
</script>
