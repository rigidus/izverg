<?php

// BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);



// edit_id

if (!$bFlag404) {
	// Не задан параметр - сообщим об ошибке
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Ошибочный идентификатор редактируемой страницы!');
	return;
} else {
	$edit_id = $aRequest[$nLevel+1];
	if (isset($aRequest[$nLevel+2])) {
		if (is_numeric($aRequest[$nLevel+2])) {
			$version_id = $aRequest[$nLevel+2];
		} else {
			$_t->assign('content', '<span style="color: red">Ошибка:</span> Ошибочный идентификатор версии!');
			return;
		}
	}
	if (!isset($aRequest[$nLevel+3])) {
		$bFlag404 = false;
	}
}



// GET

$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_PAGES.'` WHERE id = ';
if (!isset($version_id)) {
	$sql .= $edit_id;
} else {
	$sql .= $version_id;
}
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aEdit = mysql_fetch_assoc($sql);

if (empty($aEdit)) {
	$_t->assign('content', '<span style="color: red">Ошибка:</span> Этой страницы не существует!');
	return;
}


// Versioning Data

$aTree = array();
getSubVersionsRecursive($edit_id);


// POST

if (isset($_POST['act'])) :
	switch ($_POST['act']) :
	
		case 'edit':
			unset($_POST['act']);

			$_POST['changer'] = mysql_escape_string($_SESSION['user']['id']);
			if (!isset($_POST['hidden'])) 			$_POST['hidden'] = 0;
			if (!isset($_POST['hidden_menu'])) 		$_POST['hidden_menu'] = 0;
			
			$sql = "UPDATE `".DB_PREFIX.DB_TBL_PAGES."` SET ";
			foreach ($_POST as $k=>$v) {
				$sql .= "\r\n `$k` = '".mysql_escape_string($v)."', ";
			}
			$sql = substr($sql, 0, strlen($sql)-2);
			
			if (isset($version_id)) {
				$sql .= "\r\nWHERE `id` ='$version_id' LIMIT 1;";
			} else {
				$sql .= "\r\nWHERE `id` ='$edit_id' LIMIT 1;";
			}
			
			$sql = mysql_query($sql);
			if (false == $sql) my_die();
			
			// Clear Cache
			if (defined('CACHE_ON')) {
				$Cashe->DelPage($edit_id);
			}
		
			if (!isset($version_id)) {
				if (isset($_SESSION['mod_structure']['Vetka'])) {
					header('Location: '.$aCmsModules['structure']['key'].'/'.$_SESSION['mod_structure']['Vetka']);
				} else {
					header('Location: '.$aCmsModules['structure']['key']);
				}
			} else {
				header('Location: '.$aCmsModules['edit']['key'].'/'.$edit_id.'/'.$version_id);
			}
			break;
			
			
		case 'draft':
			$sql = "UPDATE `".DB_PREFIX.DB_TBL_PAGES."` 
					SET `draft` = 1 
					WHERE ( ";
			foreach ($aTree as $k=>$v) {
				$sql .= "(`id` = $k) OR ";
			}
			$sql = substr($sql, 0, strlen($sql)-4);
			$sql .= ')';
			$sql = mysql_query($sql);
			if (false == $sql) my_die();
			
			$sql = "UPDATE `".DB_PREFIX.DB_TBL_PAGES."` 
					SET `draft` = 0 
					WHERE `id` = ".$_POST['draft'];
			$sql = mysql_query($sql);
			if (false == $sql) my_die();
			
			// Clear Cache
			if (defined('CACHE_ON')) {
				$Cashe->DelPage($edit_id);
			}
			
			@header('Location: '.$aCmsModules['edit']['key'].'/'.$edit_id.'/'.$version_id);
			break;
	endswitch;
endif;









// tpl

$tpl = new KTemplate(FLGR_CMS_TEMPLATES.'/edit.htm');
$tpl->assign('h_title', 'Редактирование страницы');



// Versioning View

$aOutTree = array();
DendroId($edit_id, array());

foreach ($aOutTree as $k=>$v) {
	$tplMenuElt = $tpl->fetchBlock('MenuElt');
	$tplMenuElt->assign('level', count($v['level'])*20);
	$tplMenuElt->assign('ver', $v['id']);
	
	if ($v['draft'] != 1) {
		$tplMenuElt->assign('bg', '#FFFFCC');
		$tplMenuElt->assign('brdr', '#CCCCCC');
		$tplMenuElt->assign('checked', 'checked');
	} else {
		$tplMenuElt->assign('bg', '#FFFFFF');
		$tplMenuElt->assign('brdr', '#FFFFFF');
		$tplMenuElt->assign('checked', '');
	}
	
	$ca = array('version-del', 'version-add');
	foreach ($ca as $w) {
		if ( ($v['id'] != $edit_id) || ($w != 'version-del') ) {
			$tplControls = $tplMenuElt->fetchBlock('Controls');
			$tplControls->assign('key', $aCmsModules[$w]['key'].'/'.$v['id']);
			$tplControls->assign('gif', "/img/$w.gif");
			$tplMenuElt->assign('Controls', $tplControls);
			$tplControls->reset();
		}
	}
	
	if ($v['hidden_menu'] != 0) {
		$v['title'] = '('.$v['title'].')';
	}
	
	if ($v['hidden'] == 0) {
		$tplHyperLink = $tplMenuElt->fetchBlock('hyperlink');
		$tplHyperLink->assign('link', $aCmsModules['edit']['key'].'/'.$edit_id.'/'.$v['id']);
		if ( (isset($version_id)) && ($version_id == $v['id']) ) {
			$tplHyperLink->assign('title', '<b>'.$v['title'].'</b>');
		} else {
			$tplHyperLink->assign('title', $v['title']);
		}
		$tplMenuElt->assign('hyperlink', $tplHyperLink);
		$tplHyperLink->reset();
	} else {
		$tplMenuElt->assign('hyperlink', '&nbsp;<a style="color: #999999">'.$v['title'].'</a>');
	}
	
	$tpl->assign('MenuElt', $tplMenuElt);
	$tplMenuElt->reset();
}


// FCK

if (!defined('FCK')) {
	// TEXT
	$tplFck = $tpl->fetchBlock('text');
	$tplFck->assign($aEdit);
	$tpl->assign('text', $tplFck);
	$tplFck->reset();
	unset($aEdit['text']);
	// ANNOTATION
	if (!defined('PAGE_NO_ANNOTATION')) {
		$tplFck = $tpl->fetchBlock('annotation');
		$tplFck2 = $tplFck->fetchBlock('annotation');
		$tplFck2->assign($aEdit);
		$tplFck->assign('annotation', $tplFck2);
		$tpl->assign('annotation', $tplFck);
		$tplFck->reset();
		unset($aEdit['text']);
	} else {
		unset($aEdit['annotation']);
		$tpl->assign('annotation', '');
	}
} else {
	// TEXT
	$tpl->assign('text', overbox('fck', array('text', $aEdit['text'])));
	unset($aEdit['text']);
	// ANNOTATION
	if (!defined('PAGE_NO_ANNOTATION')) {
		$tplFck = $tpl->fetchBlock('annotation');
		//$tplFck->assign($aEdit);
		$tplFck->assign('annotation', overbox('fck', array('annotation', $aEdit['annotation'])));
		$tpl->assign('annotation', $tplFck);
		$tplFck->reset();
		unset($aEdit['text']);
	} else {
		unset($aEdit['annotation']);
		$tpl->assign('annotation', '');
	}
}


if ($aEdit['draft'] == 1) {
	$tplLink = $tpl->fetchBlock('Link');
	$tplLink->assign('key', $aEdit['key']);
	$tpl->assign('Link', $tplLink);
	$tplLink->reset();
} else {
	$tplLink = $tpl->fetchBlock('Link');
	$tplLink->assign('key', $aEdit['key']);
	$tpl->assign('Link', $tplLink);
	$tplLink->reset();
}

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


// SEOPAGES
if (!defined('SEOPAGES')) {
	$tpl->assign('seopages', '');
} else {
	$tplSeo = $tpl->fetchBlock('seopages');
	$tplSeo->assign($aEdit);
	$tpl->assign('seopages', $tplSeo);
	$tplSeo->reset();
}

unset($aEdit['annotation']);
unset($aEdit['text']);
$tpl->assign($aEdit);

if ($aEdit['hidden'] != 0) {
	$tpl->assign('hidden_checked', 'checked');
} else {
	$tpl->assign('hidden_checked', '');
}
if ($aEdit['hidden_menu'] != 0) {
	$tpl->assign('hidden_menu_checked', 'checked');
} else {
	$tpl->assign('hidden_menu_checked', '');
}
$tpl->assign('act', 'edit');


//out
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
$_t->assign('content', $tpl);

?>