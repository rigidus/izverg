<?php

include(FLGR_COMMON.'/kcaptcha/kcaptcha.php');
$captcha = new KCAPTCHA();
$_SESSION['captcha_keystring'] = $captcha->getKeyString();
$bFlag404 = false;


?>