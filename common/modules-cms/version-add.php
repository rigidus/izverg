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
`owner`,
`subversion`,
`draft`
) VALUES (
'',
'".mysql_escape_string($_POST['parent'])."',
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
'".mysql_escape_string($_SESSION['user']['id'])."',
'1',
'1'
)";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	$insert_id = mysql_insert_id();
	
	// Двигаемся до базовой версии
	$candidat_id = $_POST['parent'];
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
	
	header('Location: '.$aCmsModules['edit']['key'].'/'.$base_id.'/'.$insert_id);
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

if (!defined('SEOPAGES')) {
	$tpl->assign('seopages', '');
} else {
	$tplSeo = $tpl->fetchBlock('seopages');
	$tplSeo->assign($aParent);
	$tpl->assign('seopages', $tplSeo);
	$tplSeo->reset();
}

$tpl->assign('Link', '');
$tpl->assign($aParent);
$tpl->assign('MenuElt', '');
$tpl->assign('parent', $parent_id);
$tpl->assign('act', 'add');


// out
$tpl->assign('h_title', 'Создание версии');
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
$_t->assign('content', $tpl);
?>