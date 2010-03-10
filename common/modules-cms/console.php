<?php 

if (!$bFlagLastModule) return;

$_t->assign('title', 'Консоль');

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

// permissions
if (!$Permissions->bIsAdmin()) {
	$_t->assign('content', "Необходимы права администратора");
	cStat::bSaveEvent(EVENT_PERMDENIED);
	return;
}

$tplConsole = new KTemplate(FLGR_CMS_TEMPLATES.'/console.htm');

if ( (isset($_POST['act'])) && ($_POST['act'] == 'console') && ($_POST['console'] != '') ) { 
	$f = create_function('', $_REQUEST['console']);
	$tplConsole->assign('result', overbox($f));
	$tplConsole->assign('console', $_REQUEST['console']);
} else {
	$tplConsole->assign('result', '');
	$tplConsole->assign('console', '');
}

$_t->assign('content', $tplConsole);
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
	
?>