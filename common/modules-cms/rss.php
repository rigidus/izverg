<?php

$tplRSS = new KTemplate();
$tplRSS->loadTemplateContent('<?xml version="1.0" encoding="windows-1251"?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
  <title>{site}</title>
  <link>{site_link}</link>
  <description>{site_description}</description>
  <language>ru</language>
  <managingEditor>{site_email}</managingEditor>
  <generator>na_kolenke</generator>
  <lastBuildDate>{last_build_date}</lastBuildDate>
  <image>
    <url>http://{site}/img/icon_for_rss.jpg</url>
    <title>{site}</title>
    <link>{site_link}</link>
    <width>100</width>
    <height>131</height>
  </image>
  
<!-- BEGIN item --> 
  <item>
    <guid isPermaLink=\'true\'>http://{site}/postid/{id}</guid>
    <title>{title}</title>
    <link>http://{site}/postid/{id}</link>
    <comments>http://{site}/postid/{id}</comments>
    <description><![CDATA[ {text} ]]></description>
    <pubDate>{date}</pubDate>
  </item>
<!-- END item -->
	
</channel>
</rss>');

$tplRSS->assign('site', HOST);
$tplRSS->assign('site_description', HEAD_TITLE);
$tplRSS->assign('site_link', 'http://'.HOST);
$tplRSS->assign('site_email', EMAIL_CONTACTS);


$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_POSTS.'` ORDER BY `t` DESC';
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aPosts = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aPosts[] = $row;
}
	
foreach ($aPosts as $v) {
	$tplItem = $tplRSS->fetchBlock('item');
	$tplItem->assign('site', HOST);
	$tplItem->assign('id', $v['id']);
	$tplItem->assign('title', $v['title']);
	$tplItem->assign('text', normalize($v['text']));
	$tplItem->assign('date', formatDate($v['t']));
	$tplRSS->assign('item', $tplItem);
	$tplItem->reset();
}

$a = $aPosts[0];
$tplRSS->assign('last_build_date', formatDate($a['t']));

$tplRSS->out();
exit();

function formatDate($p)
{
	$year = substr($p, 0, 4);
	$month = substr($p, 5, 2);
	$day = substr($p, 8, 2);
	$hour = substr($p, 11, 2);
	$min = substr($p, 14, 2);
	$sec = substr($p, 17, 2);
	
	$r = mktime($hour,$min,$sec,$month,$day,$year);
	$r = gmdate('D, d M Y H:i:s +0400', $r);
	
	return $r;
}

?>