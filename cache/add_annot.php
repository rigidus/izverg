<?php

$sql = "SELECT `key`, `id` FROM `".DB_PREFIX.DB_TBL_PAGES."` WHERE 1 ORDER BY `id`";
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aPages = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aPages[$row['id']] = $row;
}

foreach ($aPages as $k=>$v) {
	$sql = "SELECT `page_annotation` FROM `izverg_pages` WHERE `page_skey`='".$v['key']."'";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	$row = mysql_fetch_assoc($sql);
	if (is_array($row)) {
		$row = current($row);
	} else {
		$row = '';
	}
	$aPages[$k]['annotation'] = $row;
}

foreach ($aPages as $v) {
	$sql = "UPDATE `".DB_PREFIX.DB_TBL_PAGES."` SET `annotation` = '".$v['annotation']."' WHERE `id` =".$v['id']." LIMIT 1 ;";
	$sql = mysql_query($sql);
}

dbg($aPages);

?>