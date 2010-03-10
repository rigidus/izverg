<?php

// BREADCRUMBS
$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);


if (!$bFlagLastModule) return;



if (!isset($_SESSION['mod_structure'])) {
	$_SESSION['mod_structure'] = array();
}

if ( ($bFlag404) && (isset($aRequest[$nLevel+1])) ) {
	$_SESSION['mod_structure']['Vetka'] = $aRequest[$nLevel+1];
} else {
	unset($_SESSION['mod_structure']['Vetka']);
}


//

function aSpecialGetMenu($param)
{
	global $Permissions;

	$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_PAGES."` WHERE ((`key`='".$param."') AND (`subversion` = 0))";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	$row = mysql_fetch_assoc($sql);
	$param = $row['id'];

	global $aTree;
	global $aOutTree;
	if (MENU_GEN or(!file_exists(FILE_CACHE_TREE))) {

		if (!$Permissions->bIsAdmin()) {
			$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_PAGES."` WHERE ((`key` != 'cms') AND (`subversion` = 0)) ORDER BY `order`";
		} else {
			$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_PAGES."` WHERE (`subversion` = 0) ORDER BY `order`";
		}

		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		$aTree = array();
		while ($row = mysql_fetch_assoc($sql)) {
			$row['title'] = str_replace(' ', '&nbsp;', $row['title']);
			$aTree[$row['id']] = $row;
		}
		$aOutTree = array();
		DendroId($param, array());
		safewrite(FILE_CACHE_TREE, serialize($aOutTree));
	} else {
		$aOutTree = unserialize(file_get_contents(FILE_CACHE_TREE));
	}
	return $aOutTree;
}


//

function tplSpecialGetMenu($param)
{
	global $tpl, $aCmsModules;
	foreach (aSpecialGetMenu($param) as $k=>$v) {
		$tplMenuElt = $tpl->fetchBlock('MenuElt');
		$tplMenuElt->assign('key', $v['key']);
		$tplMenuElt->assign('title', $v['title']);
		$tplMenuElt->assign('level', count($v['level'])*20);
		if ( ($v['hidden'] == 0) || ( ($v['key'] != $param) || ($v['key'] == 'cms') ) ) {
			$ca = array('edit', 'del', 'add', 'prop-edit');
			foreach ($ca as $w) {
				$tplControls = $tplMenuElt->fetchBlock('Controls');
				$tplControls->assign('key', $aCmsModules[$w]['key'].'/'.$v['id']);
				$tplControls->assign('gif', "/img/$w.gif");
				$tplMenuElt->assign('Controls', $tplControls);
				$tplControls->reset();
			}
			$tplMenuElt->assign('Controls', '<input type="text" name="order['.$v['id'].']" value="'.$v['order'].'" style="width: 30px; padding-left: 6px; border: 1px solid #999999" />&nbsp;');
		} else {
			/*
			$tplMenuElt->assign('if_admin', '');
			*/
		}
		$link = implode('/', $v['level']);
		if ($link == '') { $link = '/';	}

		if ($v['hidden_menu'] != 0) {
			$v['title'] = '('.$v['title'].')';
		}

		if ($v['hidden'] == 0) {
			$tplHyperLink = $tplMenuElt->fetchBlock('hyperlink');
			$tplHyperLink->assign('link', $link);
			$tplHyperLink->assign('title', $v['title']);
			$tplMenuElt->assign('hyperlink', $tplHyperLink);
			$tplHyperLink->reset();
		} else {
			$tplMenuElt->assign('hyperlink', '&nbsp;<a style="color: #999999">'.$v['title'].'</a>');
		}

		$tpl->assign('MenuElt', $tplMenuElt);
		$tplMenuElt->reset();
	}
}


// POST

if ( (isset($_POST['act'])) && ($_POST['act'] == 'order') ) {
	//dbg($_POST);
	foreach ($_POST['order'] as $k=>$v) {
		$sql = "UPDATE `".DB_PREFIX.DB_TBL_PAGES."` SET `order` = '$v' WHERE `id` ='".$k."'";
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
	}
}

if (!$bFlagLastModule) return;


// GET
if (empty($sModuleTpl)) {
	$sModuleTpl = 'structure';
}
$tpl = new KTemplate(FLGR_CMS_TEMPLATES.'/'.$sModuleTpl.'.htm');



// Получаем страницы верхнего уровня

$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_PAGES."` WHERE ( (`parent`='1') AND (`subversion` = 0) ) ORDER BY `order`";
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aList = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aList[] = $row;
}

$tplVetka = $tpl->fetchBlock('Vetka');
$tplVetka->assign('key', '');
$tplVetka->assign('title', 'Все ветви');
$tpl->assign('Vetka', $tplVetka);
$tplVetka->reset();
foreach ($aList as $v) {
	if ($Permissions->bIsAdmin() || $v['key'] != 'cms') {
		$tplVetka = $tpl->fetchBlock('Vetka');
		$tplVetka->assign($v);
		if ( (isset($aRequest[$nLevel+1])) && ($aRequest[$nLevel+1] == $v['key']) ) {
			$tplVetka->assign('selected', 'selected');
			$bFlag404 = false;
		} else {
			$tplVetka->assign('selected', '');
		}
		$tpl->assign('Vetka', $tplVetka);
		$tplVetka->reset();
	}
}
$tpl->assign('module_url', $aCmsModules['structure']['key']);

if (isset($aRequest[$nLevel+1])) {
	tplSpecialGetMenu($aRequest[$nLevel+1]);
} else {
	tplSpecialGetMenu('');
}



// OUT

$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
$_t->assign('content', $tpl);

?>