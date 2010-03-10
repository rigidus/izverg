<?php

// BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

if (!$bFlag404) {
	// Не задан параметр - сообщим об ошибке
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Ошибочный идентификатор редактируемой страницы!');
	return;
} else {
	$edit_id = $aRequest[$nLevel+1];
	$bFlag404 = false;
}

// POST
if ( (isset($_POST['act'])) && ($_POST['act'] == 'edit') ) {
	unset($_POST['act']);

	$_POST['changer'] = mysql_escape_string($_SESSION['user']['id']);
	if (!isset($_POST['hidden'])) 			$_POST['hidden'] = 0;
	if (!isset($_POST['hidden_menu'])) 		$_POST['hidden_menu'] = 0;
	
	$sql = "UPDATE `".DB_PREFIX.DB_TBL_PAGES."` SET ";
	foreach ($_POST as $k=>$v) {
		$sql .= "\r\n `$k` = '".mysql_escape_string($v)."', ";
	}
	$sql = substr($sql, 0, strlen($sql)-2);
	$sql .= "\r\nWHERE `id` ='$edit_id' LIMIT 1;";

	$sql = mysql_query($sql);
	if (false == $sql) my_die();

	if (isset($_SESSION['mod_structure']['Vetka'])) {
		header('Location: '.$aCmsModules['structure']['key'].'/'.$_SESSION['mod_structure']['Vetka']);
	} else {
		header('Location: '.$aCmsModules['structure']['key']);
	}
}



// GET

$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_PAGES.'` WHERE id = '.$edit_id;
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aEdit = mysql_fetch_assoc($sql);

if (empty($aEdit)) {
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Этой страницы не существует!');
	return;
}

$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_USERS;
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aList = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aList[$row['id']] = $row;
}

if (isset($aList[$aEdit['owner']])) {
	$aEdit['owner'] = $aList[$aEdit['owner']]['name'];
}

if (isset($aList[$aEdit['changer']])) {
	$aEdit['changer'] = $aList[$aEdit['changer']]['name'];
}

// load template
$tpl = new KTemplate(FLGR_CMS_TEMPLATES.'/prop-edit.htm');

// h_title
$tpl->assign('h_title', 'Редактирование свойств страницы');


if (!defined('FCK')) {
	// TEXT
	$tplFck = $tpl->fetchBlock('text');
	$tplFck->assign($aEdit);
	$tpl->assign('text', $tplFck);
	$tplFck->reset();
	unset($aEdit['text']);
	// ANNOTATION
	if (!defined('PAGE_NO_ANNOTATION')) {
		$tplFck = $tpl->fetchBlock('annotation');
		$tplFck2 = $tplFck->fetchBlock('annotation');
		$tplFck2->assign($aEdit);
		$tplFck->assign('annotation', $tplFck2);
		$tpl->assign('annotation', $tplFck);
		$tplFck->reset();
		unset($aEdit['text']);
	} else {
		unset($aEdit['annotation']);
		$tpl->assign('annotation', '');
	}
} else {
	// TEXT
	$tpl->assign('text', overbox('fck', array('text', $aEdit['text'])));
	unset($aEdit['text']);
	// ANNOTATION
	if (!defined('PAGE_NO_ANNOTATION')) {
		$tplFck = $tpl->fetchBlock('annotation');
		//$tplFck->assign($aEdit);
		$tplFck->assign('annotation', overbox('fck', array('annotation', $aEdit['annotation'])));
		$tpl->assign('annotation', $tplFck);
		$tplFck->reset();
		unset($aEdit['text']);
	} else {
		unset($aEdit['annotation']);
		$tpl->assign('annotation', '');
	}
}

//if (!defined('SEOPAGES')) {
//	$tpl->assign('seopages', '');
//} else {
	$tplSeo = $tpl->fetchBlock('seopages');
	$tplSeo->assign($aEdit);
	$tpl->assign('seopages', $tplSeo);
	$tplSeo->reset();
//}

$tpl->assign($aEdit);
if ($aEdit['hidden'] != 0) {
	$tpl->assign('hidden_checked', 'checked');
} else {
	$tpl->assign('hidden_checked', '');
}
if ($aEdit['hidden_menu'] != 0) {
	$tpl->assign('hidden_menu_checked', 'checked');
} else {
	$tpl->assign('hidden_menu_checked', '');
}
$tpl->assign('act', 'edit');


//out
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
$_t->assign('content', $tpl);

?>