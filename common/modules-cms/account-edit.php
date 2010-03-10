<?php

// BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());

if (!$bFlag404) {
	// Не задан параметр - сообщим об ошибке
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Ошибочный идентификатор редактируемого аккунта!');
	return;
} else {
	$edit_id = $aRequest[$nLevel+1];
	if (!is_numeric($edit_id)) {
		$_t->assign('content', '<span style="color: red">Ошибка:</span> Нечисловой идентификатор редактируемого аккаунта!');
		return;
	}
	$bFlag404 = false;
}

$act='account-edit';

// POST

if (isset($_POST['act'])):
	switch ($_POST['act']):
		case $act:
			$f_not_notify = 0;
			if (isset($_POST['not_notify'])) {
				$f_not_notify = $_POST['not_notify'];
			}
			$sql = "UPDATE `".DB_PREFIX.DB_TBL_USERS."` SET 
				`id` = '".$edit_id."', 
				`login` = '".$_POST['login']."', 
				`name` = '".$_POST['name']."', 
				`password` = '".$_POST['password']."', 
				`email` = '".$_POST['email']."', 
				`not_notify` = '".$f_not_notify."', 
				`text` = '".$_POST['text']."',
				`icq` = '".$_POST['icq']."',
				`site` = '".$_POST['site']."',
				`telephone` = '".$_POST['telephone']."',
				`f` = '".$_POST['f']."',
				`i` = '".$_POST['i']."',
				`o` = '".$_POST['o']."'
			WHERE `id` ='".$edit_id."' LIMIT 1;";
			$sql = mysql_query($sql);
			if (false == $sql) my_die();
			header('Location: '.$aCmsModules['accounts']['key']);
			break;
	endswitch;
endif;

// GET

// verify
$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_USERS.'` WHERE id = '.$edit_id;
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aEdit = mysql_fetch_assoc($sql);

if (empty($aEdit)) {
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Редактируемый аккаунт не существует!');
	$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
	return;
}

// TEMPLATE

$tpl = new KTemplate(FLGR_CMS_TEMPLATES.'/account-edit.htm');
$tpl->assign('h_title', 'Редактирование аккаунта');
$tpl->assign('act', $act);
$tpl->assign($aEdit);
if ($aEdit['not_notify']) {
	$tpl->assign('not_notify_checked', 'checked');
} else {
	$tpl->assign('not_notify_checked', '');
}


// OUT

$_t->assign('content', $tpl);

?>