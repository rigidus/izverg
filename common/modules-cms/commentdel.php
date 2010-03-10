<?php

$_t->assign('title', 'Удаление комментария');

// ADD_BREADCRUMBS
$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);
$BreadCrumbs->addBreadCrumbs($aRequest[$nLevel+1].'/'.$aRequest[$nLevel+2], 'Удалить комментарий');

// permissions
if (!$Permissions->bisAdmin()) {
	$_t->assign('ContentBlock', "Необходимы права администратора");
	cStat::bSaveEvent(EVENT_PERMDENIED);
	return;
}

$act = 'commentdel';


$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_COMMENTS.'` WHERE id = '.$aRequest[$nLevel+2];
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aDel = mysql_fetch_assoc($sql);


if ( (isset($_POST['act'])) && ($_POST['act'] == $act) ) {
	del_comment($aRequest[$nLevel+2]);
	header('Location: /postid/'.$aDel['post']);
}



if (empty($aDel)) {
	$_t->assign('ContentBlock', 'Нет такого комментария');
	return;
}

$tplPostDel = new KTemplate(FLGR_TEMPLATES.'/commentdel.htm');
$tplPostDel->assign($aDel);
$tplPostDel->assign('act', $act);
$_t->assign('ContentBlock', $tplPostDel);

function del_comment($id)
{
	$sql = 'SELECT `id` FROM `'.DB_PREFIX.DB_TBL_COMMENTS.'` WHERE `parent` = '.$id;
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	$aChilds = array();
	while ($row = mysql_fetch_assoc($sql)) {
		$aChilds[] = current($row);
	}
	foreach ($aChilds as $v) {
		del_comment($v);
	}
	$sql = "DELETE FROM `".DB_PREFIX.DB_TBL_COMMENTS."` WHERE `id` = ".$id;
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
}

$bFlag404 = false;
$bFlagLastModule = false;

?>