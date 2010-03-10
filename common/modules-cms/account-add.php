<?php

// BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

// POST
if (isset($_POST['act'])) :
	switch ($_POST['act']):
		case 'create-account':
			$f_not_notify = 0;
			if (isset($_POST['not_notify'])) {
				$f_not_notify = $_POST['not_notify'];
			}
			$sql = "INSERT INTO `".DB_PREFIX.DB_TBL_USERS."` 
				( `id` , `login` , `name` , `password` , `email` , `not_notify` , `text` , `icq`, `site`, `telephone`, `f`, `i`, `o` )
				VALUES (
				'', 
				'".$_POST['login']."', 
				'".$_POST['name']."', 
				'".$_POST['password']."', '".
				$_POST['email']."', 
				'$f_not_notify', 
				'".$_POST['text']."', 
				'".$_POST['icq']."', 
				'".$_POST['site']."', 
				'".$_POST['telephone']."',
				'".$_POST['f']."',
				'".$_POST['i']."',
				'".$_POST['o']."'
				);";
			$sql = mysql_query($sql);
			if (false == $sql) my_die();
			header('Location: '.$aCmsModules['accounts']['key']);
			break;
	endswitch;
endif;


// GET

// load template
$tpl = new KTemplate(FLGR_CMS_TEMPLATES.'/account-edit.htm');

// h_title
$tpl->assign('h_title', 'Создание аккаунта');



// prepare form
$aPrepare = array_flip(array('login', 'password', 'name', 'email', 'text', 'icq', 'site', 'telephone', 'not_notify_checked', 'f', 'i', 'o'));
foreach ($aPrepare as $k=>$v) {
	$aPrepare[$k] = '';
}

$tpl->assign($aPrepare);
$tpl->assign('act', 'create-account');

//out
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
$_t->assign('content', $tpl);

?>