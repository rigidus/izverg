<?php
header("Content-type: text/plain");
chdir(dirname(__FILE__));
include_once "../../lib/config.php";
include_once "../../HTML_FormPersister/lib/config.php";
include_once "HTML/MetaForm.php"; 
include_once "HTML/MetaFormAction.php"; 
include_once "HTML/FormPersister.php"; 

$MetaForm =& _newMetaForm();

// Extract value of hidden field from specified form.
function _extractHidden($form)
{
    if (!preg_match('/<input[^>]+name="HTML_MetaForm"[^>]*value="(.+?)"[^>]*>/s', $form, $p)) return null;
    return $p[1];
}

// Return value of hidden field built based on HTML form $form.
function _getMetaValueForForm($form)
{
    $tmpMetaForm =& _newMetaForm();
    $form = $tmpMetaForm->process($form);
    if (!preg_match('/<input[^>]+name="HTML_MetaForm"[^>]*value="(.+?)"[^>]*>/s', $form, $p)) return null;
    return $p[1];
}

// Return parsed representation of hidden field in form $formWithHidden.
function _getMeta($formWithHidden)
{
    $tmpMetaForm =& _newMetaForm();
    $tmpMetaForm->MF_POST[$tmpMetaForm->MF_META_ELT] = _extractHidden($formWithHidden);
    return $tmpMetaForm->getFormMeta();
}

// Create & initialize new MetaForm object.
function& _newMetaForm()
{
    $MetaForm =& new HTML_MetaForm('test_signature');
    return $MetaForm;
}

// Create new MetaFormAction object based on HTML form and POST data.
// If $post is null, create without POST request.
function& _newMetaFormAction($requestUri, $form, $post=null, $errorHandler = null)
{
    $tmpMetaForm =& _newMetaForm();
    if ($post !== null) {
        $post[$tmpMetaForm->MF_META_ELT] = _getMetaValueForForm($form); 
        $tmpMetaForm->MF_POST = $post;
        $tmpMetaForm->MF_REQUEST_URI = $requestUri;
    }
    $MetaFormAction =& new HTML_MetaFormAction($tmpMetaForm, $errorHandler);
    return $MetaFormAction;
}

// Clean tree & value fields of metadata returned by MetaForm.
function _getPlainMetaOnly($metaForm)
{
    $meta = $metaForm->getFormMeta();
    unset($meta['tree']);
    unset($meta['value']);
    return $meta;
}

// Debug human-readable output of any variable.
function printr($value, $comment=null)
{
    if ($comment !== null) echo "$comment: ";
    var_export($value);
    echo "\n";
}
?>