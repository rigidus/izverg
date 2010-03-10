<?php

// SET BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());


if (!$bFlag404) {
	// Не задан параметр - сообщим об ошибке
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Ошибочный идентификатор удаляемого аккаунта!');
	$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
	return;
} else {
	$del_id = $aRequest[$nLevel+1];
	if (!is_numeric($del_id)) {
		$_t->assign('content', '<span style="color: red">Ошибка:</span> Нечисловой идентификатор удаляемого аккаунта!');
		$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
		return;
	}
	$bFlag404 = false;
}

$act = 'account-del';


// POST

if (isset($_POST['act'])):
	switch ($_POST['act']):
		case $act:
			$sql = "DELETE FROM `".DB_PREFIX.DB_TBL_USERS."` WHERE `id` = ".$del_id;
			$sql = mysql_query($sql);
			if (false == $sql) my_die();
			header('Location: '.$aCmsModules['accounts']['key']);
			break;
	endswitch;
endif;



// GET

// verify
$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_USERS.'` WHERE id = '.$del_id;
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aDel = mysql_fetch_assoc($sql);

if (empty($aDel)) {
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Удаляемый аккаунт не существует!');
	$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
	return;
}


// TEMPLATE

$tpl = new KTemplate();
$_s = file_get_contents(__FILE__);
$_s = substr($_s, strpos($_s, '?'.'>')+2);
$tpl->loadTemplateContent($_s);

$tpl->assign('h_title', 'Удаление аккаунта');
$tpl->assign($aDel);
$tpl->assign('act', $act);

// OUT

$_t->assign('content', $tpl);

return;

?>

<h2>{h_title}</h2>

Вы действительно хотите удалить аккаунт <b>{name}</b> ({login}) ?

<form method="post">
<input type="submit" value="Да" />
&nbsp;
<input type="button" value="Нет" onclick="javascript: history.back();" />
<input type="hidden" name="act" value="{act}" />
</form>