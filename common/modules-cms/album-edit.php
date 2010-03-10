<?php

// SET BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);


if (!$bFlag404) {
	// Не задан параметр - сообщим об ошибке
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Ошибочный идентификатор переименовываемого альбома!');
	$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
	return;
} else {
	$rename_id = $aRequest[$nLevel+1];
	if (!is_numeric($rename_id)) {
		$_t->assign('content', '<span style="color: red">Ошибка:</span> Нечисловой идентификатор переименовываемого альбома!');
		$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
		return;
	}
	$bFlag404 = false;
}

$act = 'album-rename';


// POST

if (isset($_POST['act'])):
	switch ($_POST['act']):
		case $act:
			
		$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_ALBUMS."` WHERE `id` = ".$_POST['id'];
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		$aList = array();
		while ($row = mysql_fetch_assoc($sql)) {
			$aList[] = $row;
		}
		// Если альбом не существует - выводим ошибку и завершаем работу, сбрасывая флаг 404
		if (empty($aList)) {
			$tpl->assign('content', '<span style="color: red">Ошибка:</span> Альбом не найден');
			$_t->assign('content', $tpl);
			$bFlag404 = false;
			return;
		}
		
		
		$sql = "UPDATE `".DB_PREFIX.DB_TBL_ALBUMS."` SET 
				`name` = '".$_POST['name']."' 
			WHERE `id` ='".$rename_id."' LIMIT 1;";
		$sql = mysql_query($sql);
		if (false == $sql) my_die();

		header('Location: '.$aCmsModules['albums']['key']);
		break;
	endswitch;
endif;



// GET

// verify
$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_ALBUMS.'` WHERE id = '.$rename_id;
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aDel = mysql_fetch_assoc($sql);

if (empty($aDel)) {
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Переименовываемый альбом не существует!');
	$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
	return;
}


// TEMPLATE

$tpl = new KTemplate();
$_s = file_get_contents(__FILE__);
$_s = substr($_s, strpos($_s, '?'.'>')+2);
$tpl->loadTemplateContent($_s);

$tpl->assign('h_title', 'Переименование альбома');
$tpl->assign($aDel);
$tpl->assign('act', $act);

// OUT

$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
$_t->assign('content', $tpl);

return;

?>

<h2>{h_title}</h2>

Задайте новое название альбома <b>{name}</b>

<form method="post">
<input type="text" name="name" value="" size="40" />
&nbsp;
<input type="submit" value="Сохранить" />
&nbsp;
<input type="button" value="Отмена" onclick="javascript: history.back();" />
<input type="hidden" name="id" value="{id}" />
<input type="hidden" name="act" value="{act}" />
</form>