<?php

$_t->assign('title', 'Статистика');

// breadcrumbs
$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

if (!$bFlagLastModule) return;

$_t->assign('content', "Статистика:<ul>");

$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_PAGES."` WHERE (
							(`parent` = '".$nId."') AND
							(`hidden` = '1')
						) ORDER BY `order`";
$sql = mysql_query($sql);
if (false == $sql) my_die();
while ($row = mysql_fetch_assoc($sql)) {
  $_t->assign('content', '<li><a href="'.$aCmsModules['stat']['key'].'/'.$row['key'].'">'.$row['title'].'</a></li>' );
}
$_t->assign('content', '</ul>' );

$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());

?>