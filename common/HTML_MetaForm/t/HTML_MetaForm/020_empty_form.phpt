--TEST--
HTML_MetaForm: empty form
--FILE--
<?php
require dirname(__FILE__) . '/init.php';
ob_start(array(&$MetaForm, 'process'));
?>
<form method="get">
</form>
<form method="pOst">
</form>

--EXPECT--
<form method="get">
</form>
<form method="pOst"><input type="hidden" name="HTML_MetaForm" value="572413e99ae434b06acd7447bc0958fb-enicS7QytaoutrKwUsovykzPzEvMUbIutjKwUgJRJlZKeYm5qUrWfhBOSWVBKlQ8Lb8oF8Q0slLKTIEoMAUyS1Jzi5WsE4EGVNfWAgD70xui" />
</form>

