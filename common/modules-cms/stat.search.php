<?php

// breadcrumbs
$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

if (!$bFlagLastModule) return;


$sql = "SELECT `from`, `t`, `to`, `sessid` FROM `".DB_PREFIX.DB_TBL_STAT."` WHERE (
(`from` != '') AND
(`from` NOT REGEXP 'http://(www\.)?".str_replace('.', '\.', HOST)."') \r\n";
$sql .= "AND (`from` NOT REGEXP 'http://(www\.)?hghltd.yandex.com') \r\n";
$sql .= "AND (`from` NOT REGEXP 'http://(www\.)?209.85.135.104') \r\n";
$sql .= "AND (`from` NOT REGEXP 'http://(www\.)?209.85.129.104') \r\n";
$sql .= "AND (`from` NOT REGEXP 'http://(www\.)?64.233.183.104') \r\n";
$sql .= "AND (`from` NOT REGEXP 'http://(www\.)?66.102.9.104') \r\n";
$sql .= "AND (`from` NOT REGEXP 'http://(www\.)?".str_replace('.', '\.', HOST)."*') \r\n";
$sql .= ") ORDER BY `t` DESC LIMIT 100";

$sql = mysql_query($sql);
if (false == $sql) my_die();
$aStat = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aStat[] = $row;
}

foreach ($aStat as $k=>$v) {

	// get refferer host

	$nPositionOfHostEndSeparator = strpos($v['from'], '/', 7)-7;
	if ($nPositionOfHostEndSeparator > 0) {
		$aStat[$k]['host'] = substr($v['from'], 7, $nPositionOfHostEndSeparator);
	} else {
		$aStat[$k]['host'] = substr($v['from'], 7);
	}

	// get params

	$aParsed = parse_url($v['from']);
	if (isset($aParsed['query'])) {
		parse_str($aParsed['query'], $aStat[$k]['params']);

		// Специфичное для разных поисковиков

		if (file_exists(FLGR_IMG.'/search/'.$aStat[$k]['host'].'.gif')) {
			$aStat[$k]['icon'] = '<img src="/img/search/'.$aStat[$k]['host'].'.gif" border="0" />';
		} else {
			$aStat[$k]['icon'] = $aStat[$k]['host'];
		}

		foreach ($aIniSearch as $x=>$y) {
			if ($aStat[$k]['host'] == $x) {
				if (isset($aStat[$k]['params'][$y])) {
					$aStat[$k]['query'] = urldecode($aStat[$k]['params'][$y]);
				} else {
					//dbg($aStat[$k]);
				}
				break;
			}
		}

		if ('www.google.' == substr($aStat[$k]['host'], 0, 11)) {
			if (isset($aStat[$k]['params']['q'])) {
				$aStat[$k]['query'] = urldecode($aStat[$k]['params']['q']);
			} else {
				$aStat[$k]['query'] = urldecode($aStat[$k]['params']['as_epq']);
			}
			$aStat[$k]['icon'] = '<img src="/img/search/www.google.gif" border="0" />';
		} elseif ('sm.aport.ru' == $aStat[$k]['host']) {
			/*
			$aStat[$k]['query'] = explode(',', urldecode($aStat[$k]['params']['old']));
			$params = array();
			foreach ($aStat[$k]['query'] as $v) {
				$offset_equal = strpos($v, '=');
				if (false !== $offset_equal) {
					$params[substr($v, 0, $offset_equal)] = substr($v, $offset_equal+1);
				}
			}
			$aStat[$k]['query'] = $params['BsR'].' '. utf_to_cp1251(urldecode($aStat[$k]['params']['r']));
			*/
			$aStat[$k]['query'] = '';
			if (isset($aStat[$k]['params']['BsR'])) {
				$aStat[$k]['query'] = urldecode($aStat[$k]['params']['BsR']);
			}
			if ($aStat[$k]['params']['r']) {
				$aStat[$k]['query'] .= ' & '.urldecode($aStat[$k]['params']['r']);
			}
		} elseif ( ('www.nigma.ru' == $aStat[$k]['host']) || ('nigma.ru' == $aStat[$k]['host']) ) {
			if (isset($aStat[$k]['params']['q'])) {
				$aStat[$k]['query'] = urldecode($aStat[$k]['params']['q']);
			} elseif (isset($aStat[$k]['params']['request_str'])) {
				$aStat[$k]['query'] = urldecode($aStat[$k]['params']['request_str']);
			}
		} elseif ('www.rambler.ru' == $aStat[$k]['host']) {
			if ( (isset($aStat[$k]['params']['oe'])) && ($aStat[$k]['params']['oe'] == 'koi8r') ) {
				$aStat[$k]['query'] = convert_cyr_string($aStat[$k]['query'], 'k', 'w');
			}
		} elseif ('vkontakte.ru' == trim($aStat[$k]['host'])) {
			$aStat[$k]['icon'] = '<img src="/img/search/vkontakte.ru.gif" border="0" />';
			$aStat[$k]['from'] = urldecode($aStat[$k]['from']);
		}

		if ( (isset($aStat[$k]['query'])) && (!isCorrectText($aStat[$k]['query'])) ) {
			$aStat[$k]['query'] = utf_to_cp1251($aStat[$k]['query']);
		}

	} else {
		// нет GET-параметров
		// ...
	}
}



$tplStatSearch = new KTemplate(FLGR_CMS_TEMPLATES.'/stat.search.htm');



foreach ($aStat as $k=>$v) {
	/* dbg($v); */
	$tplStatElt = $tplStatSearch->fetchBlock('stat_elt');
	$v['t'] = date_humanize($v['t']);
	if (!isset($v['icon'])) {
		$v['icon'] = $v['host'];
	}
	if (!isset($v['query'])) {
		$v['query'] = $v['from'];
	}
	$tplStatElt->assign($v);
	$tplStatSearch->assign('stat_elt', $tplStatElt);
	$tplStatElt->reset();
}

$_t->assign('content', $tplStatSearch);
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());

?>