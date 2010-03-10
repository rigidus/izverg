<?php ## t_meta_process.php: process metadata attached to form.
include_once "../../lib/config.php";
include_once "../../HTML_FormPersister/lib/config.php";
include_once "HTML/MetaForm.php"; 
$metaForm =& new HTML_MetaForm('secret_digital_signature_YS0lTgit');
echo "<pre>";
print_r($metaForm->getFormMeta()); 
echo "</pre>";
?>
