<?php

// POST
if (isset($_POST['act'])):
	switch ($_POST['act']):
		case 'login':
			$Permissions->login($_POST['login'], $_POST['password']);
			break;
		case 'logout':
			$Permissions->logout();
			break;
	endswitch;
endif;

// LOGIN FORM
if (!$Permissions->bIsAdmin()) {
	// TEMPLATE
	$_t = new KTemplate(FLGR_CMS_TEMPLATES.'/login-form.htm');
	return;
}

// TEMPLATE

$_t = new KTemplate(FLGR_CMS_TEMPLATES.'/'.$sModuleTpl.'.htm');

// BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

// TOPMENU

$sql = "SELECT `key`, `title`, `annotation`
		FROM `".DB_PREFIX.DB_TBL_PAGES."`
		WHERE parent = ".$nId." ORDER BY `order`";
$sql = mysql_query($sql);
if (false == $sql) my_die();
while ($row = mysql_fetch_assoc($sql)) {
	$tpl_topmenu = $_t->fetchBlock('topmenu');
	$tpl_topmenu->assign('topmenu_key', '/'.$sKey.'/'.$row['key']);
	$tpl_topmenu->assign('topmenu_title', $row['title']);
	$_t->assign('topmenu', $tpl_topmenu);
	$tpl_topmenu->reset();
}

// PERMISSIONS

if (!$Permissions->bIsAdmin()) {
	$_t->assign('content', "Доступ запрещен!");
	$bFlagStop = true;
	cStat::bSaveEvent(EVENT_PERMDENIED);
	return;
}

if (defined('VERSION')) {
	$_t->assign('VERSION', VERSION);
} else {
	$_t->assign('VERSION', '5.0');
}


if (!$bFlagLastModule) return;

// GET

$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
$_t->assign('content', 'Добро пожаловать, Админ!');

return;

?>