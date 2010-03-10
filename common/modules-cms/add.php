<?php

// BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

if (!$bFlag404) {
	// Не задан параметр - будем создавать страницу от корня
	$parent_id = 1;
} else {
	$parent_id = $aRequest[$nLevel+1];
	$bFlag404 = false;
}



// POST

if ( (isset($_POST['act'])) && ($_POST['act'] == 'add') ) {
	if (!isset($_POST['hidden'])) 			$_POST['hidden'] = 0;
	if (!isset($_POST['hidden_menu'])) 		$_POST['hidden_menu'] = 0;
	if (!isset($_POST['annotation'])) 		$_POST['annotation'] = '';
	$sql = "INSERT INTO `".DB_PREFIX.DB_TBL_PAGES."` (
`id`,
`parent`,
`key`,
`title_menu`,
`title`,
`text`,
`annotation`,
`module`,
`param`,
`tpl`,
`order`,\r\n";
if (isset($_POST['seo_title'])) $sql .= "`seo_title`, \r\n";
if (isset($_POST['seo_description'])) $sql .= "`seo_description`, \r\n";
if (isset($_POST['seo_keywords'])) $sql .= "`seo_keywords`, \r\n";
$sql .= "`hidden`,
`hidden_menu`,
`owner`
) VALUES (
'',
'".mysql_escape_string($_POST['parent'])."',
'".mysql_escape_string($_POST['key'])."',
'".mysql_escape_string($_POST['title_menu'])."',
'".mysql_escape_string($_POST['title'])."',
'".mysql_escape_string($_POST['text'])."',
'".mysql_escape_string($_POST['annotation'])."',
'".mysql_escape_string($_POST['module'])."',
'".mysql_escape_string($_POST['param'])."',
'".mysql_escape_string($_POST['tpl'])."',
'".mysql_escape_string($_POST['order'])."', \r\n";

if (isset($_POST['seo_title'])) $sql .= "'".mysql_escape_string($_POST['seo_title'])."', \r\n";
if (isset($_POST['seo_description'])) $sql .= "'".mysql_escape_string($_POST['seo_description'])."', \r\n";
if (isset($_POST['seo_keywords'])) $sql .= "'".mysql_escape_string($_POST['seo_keywords'])."', \r\n";
$sql .="'".mysql_escape_string($_POST['hidden'])."',
'".mysql_escape_string($_POST['hidden_menu'])."',
'".mysql_escape_string($_SESSION['user']['id'])."'
)";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	header('Location: '.$aCmsModules['structure']['key']);
}



// GET


// parent verify
$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_PAGES.'` WHERE id = '.$parent_id;
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aParent = mysql_fetch_assoc($sql);

if (empty($aParent)) {
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Родительская страница не существует!');
	return;
}

foreach ($aParent as $k => $v) {
	$aParent[$k] = '';
}


// load template
$tpl = new KTemplate(FLGR_CMS_TEMPLATES.'/edit.htm');

if (!defined('FCK')) {
	// TEXT
	$tplFck = $tpl->fetchBlock('text');
	$tplFck->assign($aParent);
	$tpl->assign('text', $tplFck);
	$tplFck->reset();
	unset($aParent['text']);
	// ANNOTATION
	if (!defined('PAGE_NO_ANNOTATION')) {
		$tplFck = $tpl->fetchBlock('annotation');
		$tplFck2 = $tplFck->fetchBlock('annotation');
		$tplFck2->assign($aParent);
		$tplFck->assign('annotation', $tplFck2);
		$tpl->assign('annotation', $tplFck);
		$tplFck->reset();
		unset($aParent['text']);
	} else {
		unset($aParent['annotation']);
		$tpl->assign('annotation', '');
	}
} else {
	// TEXT
	$tpl->assign('text', overbox('fck', array('text', $aParent['text'])));
	unset($aParent['text']);
	// ANNOTATION
	if (!defined('PAGE_NO_ANNOTATION')) {
		$tplFck = $tpl->fetchBlock('annotation');
		//$tplFck->assign($aParent);
		$tplFck->assign('annotation', overbox('fck', array('annotation', $aParent['annotation'])));
		$tpl->assign('annotation', $tplFck);
		$tplFck->reset();
		unset($aParent['text']);
	} else {
		unset($aParent['annotation']);
		$tpl->assign('annotation', '');
	}
}

$tplLink = $tpl->fetchBlock('Link');
$tplLink->assign('key', '');
$tpl->assign('Link', $tplLink);
$tplLink->reset();

// Выпадающий список клиентских модулей
$aSelectModules = array();
$d = dir(FLGR_MODULES);
while (false !== ($entry = $d->read())) {
	if ( ($entry != '.') && ($entry != '..') ){
		$aSelectModules[] = basename($entry, '.php');
	}
}
$d->close();
foreach ($aSelectModules as $v) {
	$tplSelectModules = $tpl->fetchBlock('SelectModules');
	$tplSelectModules->assign('ModuleName', $v);
	$tpl->assign('SelectModules', $tplSelectModules);
	$tplSelectModules->reset();
}


// Выпадающий список клиентских шаблонов
$aSelectTpls = array();
$d = dir(FLGR_TEMPLATES);
while (false !== ($entry = $d->read())) {
	if ( ($entry != '.') && ($entry != '..') ){
		$aSelectTpls[] = basename($entry, '.htm');
	}
}
$d->close();
foreach ($aSelectTpls as $v) {
	$tplSelectTpls = $tpl->fetchBlock('SelectTpls');
	$tplSelectTpls->assign('TplName', $v);
	$tpl->assign('SelectTpls', $tplSelectTpls);
	$tplSelectTpls->reset();
}

if (!defined('SEOPAGES')) {
	$tpl->assign('seopages', '');
} else {
	$tplSeo = $tpl->fetchBlock('seopages');
	$tplSeo->assign($aParent);
	$tpl->assign('seopages', $tplSeo);
	$tplSeo->reset();
}


$tpl->assign($aParent);
$tpl->assign('MenuElt', '');
$tpl->assign('parent', $parent_id);
$tpl->assign('act', 'add');


// out
$tpl->assign('h_title', 'Создание страницы');
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
$_t->assign('content', $tpl);
?>