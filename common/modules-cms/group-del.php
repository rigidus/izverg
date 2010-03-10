<?php

// SET BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);


if (!$bFlag404) {
	// Не задан параметр - сообщим об ошибке
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Ошибочный идентификатор удаляемой группы!');
	$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
	return;
} else {
	$del_id = $aRequest[$nLevel+1];
	if (!is_numeric($del_id)) {
		$_t->assign('content', '<span style="color: red">Ошибка:</span> Нечисловой идентификатор удаляемой группы!');
		$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
		return;
	}
	$bFlag404 = false;
}

$act = 'group-del';


// POST

function group_del($group_id)
{
	// Находим все подгруппы и рекурсивно удаляем
	$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_GROUPS."` WHERE `parent` = ".$group_id;
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	$aList = array();
	while ($row = mysql_fetch_assoc($sql)) {
		$aList[] = $row;
	}
	foreach ($aList as $v) {
		group_del($v['id']);
	}
	
	// Удаляем все продукты группы
	$sql = "DELETE FROM `".DB_PREFIX.DB_TBL_PRODUCTS."` WHERE `group` = ".$group_id;
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	
	// Удаляем запись группы в базе данных
	$sql = "DELETE FROM `".DB_PREFIX.DB_TBL_GROUPS."` WHERE `id` = ".$group_id;
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
}

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
		// Удаляем 
		group_del($_POST['id']);
		header('Location: '.$aCmsModules['catalogs']['key'].'/'.$aList[0]['catalog']);
		break;
	endswitch;
endif;



// GET

// verify
$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_GROUPS.'` WHERE id = '.$del_id;
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aDel = mysql_fetch_assoc($sql);

if (empty($aDel)) {
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Удаляемая группа не существует!');
	$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
	return;
}


// TEMPLATE

$tpl = new KTemplate();
$_s = file_get_contents(__FILE__);
$_s = substr($_s, strpos($_s, '?'.'>')+2);
$tpl->loadTemplateContent($_s);

$tpl->assign('h_title', 'Удаление группы');
$tpl->assign($aDel);
$tpl->assign('act', $act);

// OUT

$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
$_t->assign('content', $tpl);

return;

?>

<h2>{h_title}</h2>

Вы действительно хотите удалить группу <b>{name}</b> ?

<form method="post">
<input type="submit" value="Да" />
&nbsp;
<input type="button" value="Нет" onclick="javascript: history.back();" />
<input type="hidden" name="id" value="{id}" />
<input type="hidden" name="act" value="{act}" />
</form>