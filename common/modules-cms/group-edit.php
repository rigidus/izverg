<?php

// SET BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);


if (!$bFlag404) {
	// Не задан параметр - сообщим об ошибке
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Ошибочный идентификатор изменяемой группы!');
	$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
	return;
} else {
	$edit_id = $aRequest[$nLevel+1];
	if (!is_numeric($edit_id)) {
		$_t->assign('content', '<span style="color: red">Ошибка:</span> Нечисловой идентификатор изменяемой группы!');
		$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
		return;
	}
	$bFlag404 = false;
}

$act = 'group-edit';


// POST

if (isset($_POST['act'])):
	switch ($_POST['act']):
		case $act:
			
		$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_GROUPS."` WHERE `id` = ".$_POST['id'];
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		$aList = array();
		while ($row = mysql_fetch_assoc($sql)) {
			$aList[] = $row;
		}
		// Если группа не существует - выводим ошибку и завершаем работу, сбрасывая флаг 404
		if (empty($aList)) {
			$tpl->assign('content', '<span style="color: red">Ошибка:</span> Группа не найдена');
			$_t->assign('content', $tpl);
			$bFlag404 = false;
			return;
		}
		
		$sql = "UPDATE `".DB_PREFIX.DB_TBL_GROUPS."` SET 
				`name` = '".$_POST['name']."' 
				WHERE `id` ='".$edit_id."' LIMIT 1;";
		$sql = mysql_query($sql);
		if (false == $sql) my_die();

		header('Location: '.$aCmsModules['catalogs']['key'].'/'.$aList[0]['catalog']);
		break;
	endswitch;
endif;



// GET

// verify
$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_GROUPS.'` WHERE id = '.$edit_id;
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aDel = mysql_fetch_assoc($sql);

if (empty($aDel)) {
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Группа не существует!');
	$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
	return;
}


// TEMPLATE 

$tpl = new KTemplate(FLGR_CMS_TEMPLATES.'/group-edit.htm');

$tpl->assign('h_title', 'Редактирование группы');
$tpl->assign($aDel);
$tpl->assign('act', $act);

// OUT

$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
$_t->assign('content', $tpl);

return;

?>