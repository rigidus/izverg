<?php

$_t->assign('title', 'Редактировать коментарий');

// ADD_BREADCRUMBS
$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);
$BreadCrumbs->addBreadCrumbs($aRequest[$nLevel+1].'/'.$aRequest[$nLevel+2], 'Редактировать комментарий');

// permissions
if (!$Permissions->bisAdmin()) {
	$_t->assign('ContentBlock', "Необходимы права администратора");
	cStat::bSaveEvent(EVENT_PERMDENIED);
	return;
}


$act = 'commentedit';


$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_COMMENTS.'` WHERE `id` = '.$aRequest[$nLevel+2];
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aComment = mysql_fetch_assoc($sql);

if (empty($aComment)) {
	$_t->assign('ContentBlock', 'Нет такого комментария');
	return;
}


if ( (isset($_POST['act'])) && ($_POST['act'] == $act) ) {
	$sql = "UPDATE `".DB_PREFIX.DB_TBL_COMMENTS."` SET `text` = '".$_POST['text']."', `t` = '".$aComment['t']."' WHERE `id` ='".$aRequest[$nLevel+2]."'";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	header('Location: /postid/'.$aComment['post']);
	$bFlag404 = false;
	$bFlagLastModule = false;
	return;
}


$tplComment = new KTemplate(FLGR_TEMPLATES.'/comment.htm');
$tplComment->assign($aComment);
$tplComment->assign('act', $act);
$_t->assign('ContentBlock', $tplComment);

// BREADCRUMBS
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());

$bFlag404 = false;
$bFlagLastModule = false;

?>