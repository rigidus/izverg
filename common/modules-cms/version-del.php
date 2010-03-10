<?php

// BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

if (!$bFlag404) {
	// Не задан параметр - сообщим об ошибке
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Ошибочный идентификатор удаляемой страницы!');
	$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
	return;
} else {
	$del_id = $aRequest[$nLevel+1];
	$bFlag404 = false;
}

$act = 'del';


$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_PAGES.'` WHERE id = '.$del_id;
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aDel = mysql_fetch_assoc($sql);

if (empty($aDel)) {
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Удаляемая версия не существует!');
	$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
	return;
}


// POST

if ( (isset($_POST['act'])) && ($_POST['act'] == $act) ) {
	
	
	// Двигаемся до базовой версии
	$candidat_id = $aDel['parent'];
	$bFlagNonSubVersion = true;
	while ($bFlagNonSubVersion) {
		$sql = "SELECT `id`, `parent`, `subversion` 
				FROM `".DB_PREFIX.DB_TBL_PAGES."`
				WHERE `id` = '$candidat_id'";
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		$aCandidat = mysql_fetch_assoc($sql);
		if ($aCandidat['subversion'] != 0) {
			$candidat_id = $aCandidat['parent'];
		} else {
			$bFlagNonSubVersion = false;
			$base_id = $aCandidat['id'];
		}
	}
	
	del_page($del_id);
	header('Location: '.$aCmsModules['edit']['key'].'/'.$base_id);
}


// GET





// TEMPLATE

$tpl = new KTemplate();
$_s = file_get_contents(__FILE__);
$_s = substr($_s, strpos($_s, '?'.'>')+2);
$tpl->loadTemplateContent($_s);

$tpl->assign('h_title', 'Удаление версии');
$tpl->assign($aDel);

// out
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
$_t->assign('content', $tpl);


function del_page($id)
{
	$sql = 'SELECT `id` FROM `'.DB_PREFIX.DB_TBL_PAGES.'` WHERE `parent` = '.$id;
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	$aChilds = array();
	while ($row = mysql_fetch_assoc($sql)) {
		$aChilds[] = current($row);
	}
	foreach ($aChilds as $v) {
		del_page($v);
	}
	$sql = "DELETE FROM `".DB_PREFIX.DB_TBL_PAGES."` WHERE `id` = ".$id;
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
}

return;
?>

<h2>{h_title}</h2>

Вы действительно хотите удалить версию <b>{title}</b> ?

<form method="post">
<input type="submit" value="Да" />
&nbsp;
<input type="button" value="Нет" onclick="javascript: history.back();" />
<input type="hidden" name="act" value="del" />
</form>