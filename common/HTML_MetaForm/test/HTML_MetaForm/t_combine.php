<?php 
include_once "../../lib/config.php";
include_once "../../HTML_FormPersister/lib/config.php";
include_once "HTML/FormPersister.php"; 
include_once "HTML/MetaForm.php"; 
include_once "HTML/MetaFormAction.php"; 

$SemiParser =& new HTML_SemiParser();
ob_start(array(&$SemiParser, 'process'));

$MetaForm =& new HTML_MetaForm('secret_secret');
$SemiParser->addObject($MetaForm);

$FormPersister =& new HTML_FormPersister();
$SemiParser->addObject($FormPersister);

$metaFormAction =& new HTML_MetaFormAction($MetaForm);
$metaFormAction->process();
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