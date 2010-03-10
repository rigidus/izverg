<?php

// breadcrumbs
$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

if (!$bFlagLastModule) return;

$sql = "SELECT DISTINCT `agent` FROM `".DB_PREFIX.DB_TBL_STAT."`";
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aAgents = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aAgents[] = current($row);
}

$act = 'agents';

if ( (isset($_POST['act'])) && ($_POST['act'] == $act) ) {
	$aHashAgents = array();
	foreach ($aAgents as $k=>$v) {
		$aHashAgents[md5($v)] = $v; 
	}
	foreach ($_POST['agents'] as $k=>$v) {
		$_POST['agents'][$k] = $aHashAgents[$k];
	}
	safewrite(FILE_CACHE_AGENTS, serialize($_POST['agents']));
}

if (file_exists(FILE_CACHE_AGENTS)) {
	$aAgentList = unserialize(saferead(FILE_CACHE_AGENTS));
}

$out = '<form method="post">';
foreach ($aAgents as $k=>$v) {
	$w = md5($v);
	if (isset($aAgentList[$w])) {
		$out .= '<input type="checkbox" name="agents['.$w.']" value="1" checked />'.$v.'<br />';
	} else {
		$out .= '<input type="checkbox" name="agents['.$w.']" value="1" />'.$v.'<br />';
	}
}
$out .= '<input type="hidden" name="act" value="'.$act.'" />';
$out .= '<input type="submit" value="Сохранить" />';
$out .= '</form>';
$_t->assign('content', $out);

$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());

?>