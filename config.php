<?php

if($_SERVER['REMOTE_ADDR'] == "127.0.0.1") {
	define('LOCALHOST', 1);
}

define('SITE', '');
define('HEAD_TITLE', 'Боевые искусства в Санкт петербурге. Ролевое и историческое фехтование. Фехтование в ролевых играх. ');
define('_SAPE_USER', 'ec412122841ba6bb52b8920985b75eda');

//define('DEBUG', 1);
//define('KRNL', 1);
define('MENU_GEN', 1);
//define('CACHE_ON', 1);
//define('PAGE_NO_ANNOTATION', 1);
//define('FCK', 1);
define('SEOPAGES', 1);
define('CHARSET', 'windows-1251');
//define('CART', 1);

include_once(FLGR_BASE.'/dbconfig.php');

define('DB_TBL_PAGES', 'pages');
define('DB_TBL_USERS', 'users');
define('DB_TBL_STAT', 'stat');
define('DB_TBL_EVENTS', 'events');
define('DB_TBL_NAT', 'nat');
define('DB_TBL_ALBUMS', 'albums');
define('DB_TBL_IMAGES', 'img');
define('DB_TBL_POSTS', 'posts');
define('DB_TBL_COMMENTS', 'comments');


define('EVENT_404', '404');					// ok
define('EVENT_301', '301');					// ok
define('EVENT_SENDMAIL', 'sendmail');		// ok
define('EVENT_NEWUSER', 'newuser');			// ok
define('EVENT_UPDATEUSER', 'updateuser'); 	// ok
define('EVENT_LOGIN', 'login');				// ok
define('EVENT_LOGINFAILED', 'loginfailed');	// ok
define('EVENT_LOGOUT', 'logout');			// ok
define('EVENT_ADDCOMMENT', 'addcomment');	// ok
define('EVENT_SEARCH', 'search');			// ok
define('EVENT_CRON', 'cron');
define('EVENT_PERMDENIED', 'permdenied');
define('EVENT_TEST', 'test');


define('ADDR_BASE', '');
define('ADDR_FILES', 				ADDR_BASE.'/files');
define('ADDR_PHOTOS', 				ADDR_BASE.'/photos');
define('ADDR_PHOTOS_THUMBNAILS', 	ADDR_PHOTOS.'/thumbnails');
define('ADDR_PHOTOS_BIGS', 			ADDR_PHOTOS.'/bigs');
define('ADDR_PHOTOS_NORMALS', 		ADDR_PHOTOS.'/normals');


$tmp = explode('/', FLGR_BASE);
/* unset($tmp[count($tmp)-1]); */
define('FLGR_COMMON', implode('/', $tmp).'/common');


define('FLGR_CACHE', 		FLGR_BASE.'/cache');
define('FLGR_CONTENT', 		FLGR_BASE.'/content');
define('FLGR_IMG', 			FLGR_BASE.'/img');
define('FLGR_LIB', 			FLGR_BASE.'/lib');
define('FLGR_MODULES', 		FLGR_BASE.'/modules');
define('FLGR_CMS_MODULES',	FLGR_COMMON.'/modules-cms');
define('FLGR_TEMPLATES', 	FLGR_BASE.'/templates');
define('FLGR_CMS_TEMPLATES',FLGR_COMMON.'/templates-cms');
define('FLGR_MESSAGES',		FLGR_BASE.'/messages');
define('FLGR_FILES',		FLGR_BASE.'/files');
define('FLGR_PHOTOS', 		FLGR_BASE.'/photos');
define('FLGR_PHOTOS_THUMBNAILS', 	FLGR_PHOTOS.'/thumbnails');
define('FLGR_PHOTOS_BIGS', 			FLGR_PHOTOS.'/bigs');
define('FLGR_PHOTOS_ORDERS',		FLGR_PHOTOS.'/orders');
define('FLGR_UPLOAD', FLGR_BASE.'/upload');
define('FLGR_AVATAR', FLGR_PHOTOS.'/avatar');
define('FLGR_LOGS', FLGR_BASE.'/logs');
define('FILE_CACHE_TREE', 	FLGR_CACHE.'/mainmenu.bin');
define('FILE_CACHE_AGENTS',	FLGR_CACHE.'/agents.bin');

define('FILE_MAIN_TEMPLATE', FLGR_TEMPLATES.'/izverg.htm');
define('FILE_LOG', FLGR_CACHE.'/log.txt');

define('EMAIL_CONTACTS', 'avenger-f@ya.ru');
define('EMAIL_ADMIN', 'avenger-f@ya.ru');
define('HOST', $_SERVER['HTTP_HOST']);

define('IMG_BIG_ADDR', ADDR_PHOTOS.'/bigs');
define('IMG_NORMAL_ADDR', ADDR_PHOTOS.'/normals');
define('IMG_THUMBNAIL_ADDR', ADDR_PHOTOS.'/thumbnails');
define('IMG_BIG_DIR', FLGR_PHOTOS.'/bigs');
define('IMG_NORMAL_DIR', FLGR_PHOTOS.'/normals');
define('IMG_THUMBNAIL_DIR', FLGR_PHOTOS.'/thumbnails');


$aSpecialModules = array(
'kcaptcha',
'rss',
'postid',
'commentedit',
'commentdel'
);

$aSearchEngineHosts = array(
'www.yandex.ru',
'yandex.ru',
'www.google',
'www.rambler.ru',
'go.mail.ru',
'lemmefind.ru.',
'www.lemmefind.ru',
'images.yandex.ru',
'results.metabot.ru',
'search.live.com',
'sm.aport.ru',
'win.mail.ru',
'search.yahoo.com',
'search.msn.com',
'209.85.135.104',
'209.85.129.104',
'64.233.183.104',
'search.zahav.ru',
'mail.yandex.ru',
'hghltd.yandex.com',
'www.webalta.ru',
'www.nigma.ru',
'www.what.ru',
'search.icq.com',
'nigma.ru',
'search.rambler.ru',
'ru.search.yahoo.com',
'www.avantfind.com',
'siteexplorer.search.yahoo.com',
'ie5.rambler.ru',
'search.yahoo.com',
'www.altavista.com'
);

$aIniSearch = array(
'www.google'=>'q',
'www.yandex.ru'=>'text',
'yandex.ru'=>'text',
'yandex.ua'=>'text',
'www.rambler.ru'=>'words',
'images.yandex.ru'=>'text',
'go.mail.ru'=>'q',
'search.live.com'=>'q',
'www.webalta.ru'=>'q',
'search.msn.com'=>'q',
'results.metabot.ru'=>'st',
'www.icq.com'=>'q',
'search.rambler.ru'=>'words',
'www.avantfind.com'=>'Keywords',
'siteexplorer.search.yahoo.com'=>'p',
'ie5.rambler.ru'=>'words',
'search.yahoo.com'=>'p',
'www.altavista.com'=>'q',
'search.icq.com'=>'q',
'poisk.ru'=>'text'
);


$aCmsModules = array();
$aCmsModules['admin']			= array('key'=>'/cms');

$aCmsModules['albums']			= array('key'=>$aCmsModules['admin']['key'].'/albums');
$aCmsModules['album-del']		= array('key'=>$aCmsModules['albums']['key'].'/album-del');
$aCmsModules['album-edit']		= array('key'=>$aCmsModules['albums']['key'].'/album-edit');

$aCmsModules['accounts']		= array('key'=>$aCmsModules['admin']['key'].'/accounts');
$aCmsModules['account-add']		= array('key'=>$aCmsModules['accounts']['key'].'/account-add');
$aCmsModules['account-edit']	= array('key'=>$aCmsModules['accounts']['key'].'/account-edit');
$aCmsModules['account-del']		= array('key'=>$aCmsModules['accounts']['key'].'/account-del');

$aCmsModules['structure']		= array('key'=>$aCmsModules['admin']['key'].'/structure');
$aCmsModules['add']				= array('key'=>$aCmsModules['structure']['key'].'/add');
$aCmsModules['edit']			= array('key'=>$aCmsModules['structure']['key'].'/edit');
$aCmsModules['prop-edit']		= array('key'=>$aCmsModules['structure']['key'].'/prop-edit');
$aCmsModules['del']				= array('key'=>$aCmsModules['structure']['key'].'/del');
$aCmsModules['version-add']		= array('key'=>$aCmsModules['structure']['key'].'/version-add');
$aCmsModules['version-del']		= array('key'=>$aCmsModules['structure']['key'].'/version-del');

$aCmsModules['stat']            = array('key'=>$aCmsModules['admin']['key'].'/stat');

?>
