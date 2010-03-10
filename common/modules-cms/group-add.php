<?php

// BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());

if (!$bFlag404) {
	// Не задан параметр - сообщим об ошибке
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Ошибочный идентификатор родительской группы!');
	return;
} else {
	$bFlag404 = false;
	$parent_id = $aRequest[$nLevel+1];
	if (!is_numeric($parent_id)) {
		$_t->assign('content', '<span style="color: red">Ошибка:</span> Нечисловой идентификатор родительской группы!');
		return;
	}
	// Если $parent_id нулевой (создаем корневой элемент каталога)
	if ($parent_id == 0) {
		// то идентификатор каталога должен быть следующим параметром
		if (!isset($aRequest[$nLevel+2])) {
			$_t->assign('content', '<span style="color: red">Ошибка:</span> Ошибочный идентификатор каталога!');
			return;
		} else {
			// Проверяем существование этого каталога
			$sql = 'SELECT `id` FROM `'.DB_PREFIX.DB_TBL_CATALOGS.'` WHERE `id` = '.$aRequest[$nLevel+2];
			$sql = mysql_query($sql);
			if (false == $sql) my_die();
			$aCatalog = mysql_fetch_assoc($sql);
			if (empty($aCatalog)) {
				$_t->assign('content', '<span style="color: red">Ошибка:</span> Заданный каталог не существует!');
				return;
			}
			$catalog_id = current($aCatalog);
			// А теперь проверяем, есть ли корневой элемент в этом каталоге
			$sql = "SELECT `id` FROM `".DB_PREFIX.DB_TBL_GROUPS."` WHERE ( (`catalog` = '$catalog_id') AND (`parent` = 0) ) ";
			$sql = mysql_query($sql);
			if (false == $sql) my_die();
			$aCatalog = mysql_fetch_assoc($sql);
			if (!empty($aCatalog)) {
				$_t->assign('content', '<span style="color: red">Ошибка:</span> Заданный каталог уже содержит корневой элемент!');
				return;
			}
		}
	} else {
	// Если $parent_id НЕНУЛЕВОЙ - надо проверить существование родительской группы
	// и взять $catalog_id из нее
		$sql = 'SELECT `catalog` FROM `'.DB_PREFIX.DB_TBL_GROUPS.'` WHERE `id` = '.$parent_id;
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		$aParent = mysql_fetch_assoc($sql);
		if (empty($aParent)) {
			$_t->assign('content', '<span style="color: red">Ошибка:</span> Родительская группа не существует!');
			return;
		}
		$catalog_id = current($aParent);
	}
	
}

$act = 'group-add';


// POST

if ( (isset($_POST['act'])) && ($_POST['act'] == $act) ) {
	$sql = "INSERT INTO `".DB_PREFIX.DB_TBL_GROUPS."` (
`parent`,
`catalog`,
`name`
) VALUES (
'".mysql_escape_string($_POST['parent'])."',
'".mysql_escape_string($_POST['catalog'])."',
'".mysql_escape_string($_POST['name'])."'
)";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	header('Location: '.$aCmsModules['catalogs']['key'].'/'.$_POST['catalog']);
}



// GET

// Получаем все поля
$sql = "SHOW COLUMNS FROM `".DB_PREFIX.DB_TBL_GROUPS."` ";
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aParent = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aParent[$row['Field']] = '';
}
$aParent['catalog'] = $catalog_id;
$aParent['parent'] = $parent_id;

// TEMPLATE 

$tpl = new KTemplate(FLGR_CMS_TEMPLATES.'/group-edit.htm');

$tpl->assign($aParent);
$tpl->assign('act', $act);


// out
$tpl->assign('h_title', 'Создание группы');
$_t->assign('content', $tpl);

?>