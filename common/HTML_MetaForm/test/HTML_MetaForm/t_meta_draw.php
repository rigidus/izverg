<?php ## t_metainfo_draw.php: draw the form with attached metadata.
include_once "../../lib/config.php";
include_once "../../HTML_FormPersister/lib/config.php";
include_once "HTML/MetaForm.php"; 
// Create new MetaForm object (processor).
$metaForm =& new HTML_MetaForm('secret_digital_signature_YS0lTgit');
// Parse HTML output & extract form meta-information.
ob_start(array(&$metaForm, 'process'));
?>
<form action="t_meta_process.php" method="POST">
  <label for="t">Anything</label>:
  <input type="text" name="test" id="t" meta:validator="filled"><br>
  Select: 
  <select name="sel">
    <option value="a">aaa</option>
    <option value="b">bbb</option>
  </select><br>
  <input type="submit" name="doSend" value="Send!">
</form>