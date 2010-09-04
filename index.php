<?php

// Включаем сессии - до вывода чего-либо в броузер
session_start();

// Переменная отладочного лога
$dbglog = '';

// Определяем базовый путь
// Важно использовать DIRECTORY_SEPARATOR вместо '/' при разборе
$tmp = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
define('FLGR_BASE', implode('/', $tmp));

// Включаем конфигурационный файл
require_once(FLGR_BASE.'/config.php');

// Включаем common libs
require_once(FLGR_COMMON.'/common.php');

// Включаем почту для оправки писем о сбоях
require_once(FLGR_COMMON.'/mime_mail.php'); 	// Используется

// Сервер может быть настроен так, чтобы экранировать
// слеши во входных массивах. Восстанавливаем
// нормальное состояние массивов.
if (get_magic_quotes_gpc()) {
  strips($_GET);
  strips($_POST);
  strips($_FILES);
  strips($_COOKIE);
  strips($_REQUEST);
  if (isset($_SERVER['PHP_AUTH_USER'])) strips($_SERVER['PHP_AUTH_USER']);
  if (isset($_SERVER['PHP_AUTH_PW']))   strips($_SERVER['PHP_AUTH_PW']);
}

// $aGetQuery - массив содержащий все get-параметры
// $sRequest  - строка запроса, $nRequest - ee длина
// $aRequest  - массив элементов запроса
$url = parse_url($_SERVER['REQUEST_URI']);
if (isset($url['query'])) {
  parse_str($url['query'], $url['query']);
  if (get_magic_quotes_gpc()) {
	strips($url['query']);
  }
  $aGetQuery = $url['query'];
} else {
  $aGetQuery = array();
}
$sRequest  = unslashify($url['path']);
$nRequest  = strlen($sRequest);
$aRequest = explode('/', $sRequest);


// db connecting
$_db = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (false == $_db) {
  my_die('database is down');
}
$db_selected = @mysql_select_db(DB_NAME);
if (!$db_selected) {
  my_die('dbconnect is down');
}
mysql_query('SET NAMES cp1251');

// Stat - мало ли какие ошибки произойдут дальше, а статитистику мы сохраним
require_once(FLGR_COMMON.'/stat.php');
if (!defined(LOCALHOST)) {
  cStat::bSaveStat();
}



// Processing

$aProcess = array();
$bFlag404 = false;
$nParent = 0;
foreach ($aRequest as $nLevel=>$sKey) {
  $sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_PAGES."`
			WHERE ( (`parent`=$nParent) AND (`key`='$aRequest[$nLevel]') AND (`subversion` = 0) )";
  $sql = mysql_query($sql);
  if (false == $sql) my_die();
  $sql = mysql_fetch_assoc($sql);
  if (false === $sql) {
	$bFlag404 = true;
	$nLevel--;
	break;
  } else {
	$nParent = $sql['id'];
	$aProcess[$sql['id']] = $sql;
  }
}
//dbg($aProcess);

// Versioning

$aProcessVersions = array();
foreach ($aProcess as $k=>$v) {
  if ($v['draft'] == 0) {
	$aProcessVersions[$k] = $v;
  } else {
	$aTree = array();
	getSubVersionsRecursive($k);
	foreach ($aTree as $kk=>$vv) {
	  if ($vv['draft'] == 0) {
		$aProcessVersions[$kk] = $vv;
		break;
	  }
	}
  }
}
$aProcess = $aProcessVersions;


// Set Last Id

end($aProcess);
$bFlagLastModule = false;
$nLastId = key($aProcess);
reset($aProcess);


// Debug KRNL info

if (defined('KRNL')) {
  dbglog($bFlag404, '$bFlag404');
  dbglog($nLevel, '$nLevel');
  dbglog(count($aRequest)-1, 'count($aRequest)-1');
  dbglog($aRequest, '$aRequest');
  dbglog($aProcess, '$aProcess');
  dbglog($nLastId, '$nLastId');
}


// Libs, подключаемые до включения модулей

// KTemplate
include_once(FLGR_COMMON.'/class_ktemplate.php');
$_t = new KTemplate(FILE_MAIN_TEMPLATE);

// BreadCrumbs
include_once(FLGR_COMMON.'/bread-crumbs.php');
$BreadCrumbs = new cBreadCrumbs();

// Permissions
include_once(FLGR_COMMON.'/permissions.php');
$Permissions = new cPermissions();


// Sape
require_once($_SERVER['DOCUMENT_ROOT'].'/'._SAPE_USER.'/sape.php');
$sape = new SAPE_client();
$sape_context = new SAPE_context();




// Включение модулей

$bFlagStop = false;
foreach ($aProcess as $nId => $v) {

  if ($nId === $nLastId) {
	$bFlagLastModule  = true;
  }

  $sTitle = $v['title'];
  $sTitleMenu = $v['title_menu'];
  $sText = $v['text'];
  $sKey = $v['key'];
  $sModuleParam = $v['param'];
  $sModuleTpl = $v['tpl'];
  $sSeoDescription = $v['seo_description'];
  $sSeoKeywords = $v['seo_keywords'];
  $sSeoTitle = $v['seo_title'];

  $sModuleName = $v['module'];
  if ($sModuleName == '') {
	$sModuleName = 'default';
  }

  $sModuleFileName = $sModuleName.'.php';

  if (!$bFlagStop) {
	if (file_exists(FLGR_MODULES.'/'.$sModuleFileName)) {
	  $sModuleFileName = FLGR_MODULES.'/'.$sModuleFileName;
	}  elseif (file_exists(FLGR_CMS_MODULES.'/'.$sModuleFileName)) {
	  $sModuleFileName = FLGR_CMS_MODULES.'/'.$sModuleFileName;
	} else {
	  dbglog('Error: "Module not found" in index at line '.__LINE__);
	  $sModuleFileName = false;
	}
	if ($sModuleFileName !== false) {
	  if (defined('KRNL')) {
		dbglog('===========================================',
			   '===========================================');
		dbglog($nId, '$nId');
		dbglog($sTitle, '$sTitle');
		dbglog($bFlagLastModule, '$bFlagLastModule');
		dbglog($sModuleName, 'include');
	  }
	  include($sModuleFileName);
	}
  }

}


// Обработка 404, 301

if ($bFlag404) {

  // nat
  $sql = "SELECT `to` FROM `".DB_PREFIX.DB_TBL_NAT."` WHERE `from` = '".$sRequest."'";
  $sql = mysql_query($sql);
  $sql = mysql_fetch_assoc($sql);
  if (!empty($sql)) {
	// 301
	cStat::bSaveEvent(EVENT_301);
	$nat = current($sql);
	header('301 Moved Permanently');
	header('Location: '.$nat);
	die("<h1>301 Moved Permanently</h1>".'<a href="'.$nat.'">http://'.HOST.$nat.'</a>');
  } else {
	// 404
	header('HTTP/1.1 404 Not Found');
	$subject = $_SERVER['HTTP_HOST'].' '.'404 Not Found';
	$message = my_info();
	my_mail($message, $subject);
	cStat::bSaveEvent(EVENT_404);
	//die('404 Not Found');

	// Этот кусок зависит от корневого шаблона

	$BreadCrumbs = new cBreadCrumbs();
	$BreadCrumbs->addBreadCrumbs('', 'Главная');

	$_t = new KTemplate(FILE_MAIN_TEMPLATE);

	$sTitle = '404 Not Found - Страница не найдена';
	$sText  = 'Мы обыскали весь сервер, но не смогли найти запрошенной вами страницы. <br />';
	$sText  .= 'Проверьте адрес в адресной строке броузера. <br />';
	$sModuleTpl = 'sitemap';
	include_once(FLGR_MODULES.'/sitemap.php');

	if (LOCALHOST) {
	  $_t->assign('AdSence', '');
	  $_t->assign('STATISTIC', '');
	  $_t->assign('mylinks', '');
	} else {
	  $_t->assign('AdSence', $_t->fetchBlock('AdSence'));
	  $_t->assign('STATISTIC', $_t->fetchBlock('STATISTIC'));
	  if (count($aRequest) == 1) {
		$_t->assign('mylinks', $_t->fetchBlock('mylinks'));
	  } else {
		$_t->assign('mylinks', '<small><br />'.$sape->return_links().'</small>');
	  }
	}

	$_t->assign('Cart', '');
	$_t->assign('MenuLevelOne', '');
	$_t->assign('MenuLevelTwo', '');
	$_t->assign('MenuLevelThree', '');
	$_t->assign('TopMenuElt', '');
	$_t->assign('logon', '');
	$_t->assign('logout', '');
	$_t->assign('search_query', '');
	$_t->assign('my_links', '');

  }
}


// Вывод

header('Content-Type: text/html; charset='.CHARSET);
$sOut = $_t->get();
if (defined('CACHE_ON')) {
  if ($bFlagCache) {
	$Cashe->Add($sRequest, $nLastId, $sOut);
  }
}
//echo preg_replace('/\s{2,}/', ' ', $_t->get());
echo($sOut);
if (defined('DEBUG')) {
  if (!empty($dbglog)) {
	dbg($dbglog);
  }
}

include_once(FLGR_COMMON.'/exit.php');

?>