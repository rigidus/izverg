<?php

$sql = "SELECT * FROM `pochin_pages` ORDER BY `page_id`";
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aPages = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aPages[$row['page_id']] = $row;
}

$aTmp = $aPages;
$aPages = array();

foreach ($aTmp as $k=>$v) {
	switch ($v['page_skey']) {
		case 'root':
		case 'page-tree':
		case 'page-create':
		case 'page-edit':
		case 'page-del':
		case 'stat':
		case 'console':
		case 'prices':
		case 'users':
		case 'profile':
		case 'edit':
		case 'newuser':
		case 'update':
		case 'post-edit':
		case 'images':
		case 'contacts':
		case 'links':
		case 'post-del':
		case 'del':
		case 'admin':
			break;
	
		default:
			$w = array();
			$w['id'] = $v['page_id'];
			$w['parent'] = $v['page_parent'];
			$w['key'] = $v['page_skey'];
			$w['title'] = $v['page_title'];
			$w['text'] = $v['page_text'];
			$w['annotation'] = $v['page_annotation'];
			$w['module'] = $v['page_module'];
			if ($w['module'] == 1) $w['module'] = '';
			$w['order'] = $v['page_order'];
			$aPages[$k] = $w;
			break;
	}
}

foreach ($aPages as $k=>$v) {
	$parent = $v['parent'];
	if (!isset($aPages[$parent])) {
		$aPages[$k]['parent'] = 1;
	}
}

$sql = "TRUNCATE TABLE `".DB_PREFIX.DB_TBL_PAGES."`";
$sql = mysql_query($sql);
if (false == $sql) my_die();


$sql = "INSERT INTO `".DB_PREFIX.DB_TBL_PAGES."` VALUES (1, 0, '', 'Главная', '', '', 'root', 0);";
$sql = mysql_query($sql);
if (false == $sql) my_die();
$sql = "INSERT INTO `".DB_PREFIX.DB_TBL_PAGES."` VALUES (20, 1, 'info', 'Информация', 'Инфо.', '', '', 0);";
$sql = mysql_query($sql);
if (false == $sql) my_die();
$sql = "INSERT INTO `".DB_PREFIX.DB_TBL_PAGES."` VALUES (21, 20, 'contacts', 'Контакты', '', '', 'contacts', 0);";
$sql = mysql_query($sql);
if (false == $sql) my_die();

foreach ($aPages as $k=>$v) {
	$sql = "INSERT INTO `".DB_PREFIX.DB_TBL_PAGES."` (`id`, `parent`, `key`, `title`, `text`, `annotation`, `module`, `order`) VALUES ('".$k."', '".$v['parent']."', '".$v['key']."', '".$v['title']."', '".$v['text']."', '".$v['annotation']."', '".$v['module']."', '".$v['order']."' )";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
}

dbg($aPages);

?>