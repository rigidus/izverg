<?php

// ADD_BREADCRUMBS
$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);


if (!$bFlagLastModule) return;

// HEAD_TITLE
$_t->assign('head_title', $sTitle);

// BREADCRUMBS
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());

// OPEN
if ($sModuleTpl != '') {
	$tpl = new KTemplate(FLGR_TEMPLATES.'/'.$sModuleTpl.'.htm');
} else {
	$tpl = $_t->fetchBlock('ContentBlock');
}

// CONTENT
$tpl->assign('title', $sTitle);
$tpl->assign('content', crbr(($sText)));

// ANNOTATIONS
$sql = "SELECT `id`, `key`, `title`, `annotation`, `draft` 
		FROM `".DB_PREFIX.DB_TBL_PAGES."` 
		WHERE (parent = ".$nId.")
		ORDER BY `order`";
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aPage = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aPage[] = $row;
}
// Извлекаем текущую подверсию страницы, если надо
foreach ($aPage as $k=>$v) {
	if ($v['draft'] == 1) {
		$aTree = array();
		getSubVersionsRecursive($v['id']);
		foreach ($aTree as $kk=>$vv) {
			if ($vv['draft'] == 0) {
				$aPage[$k] = $vv;
				break;
			}
		}
	}
}
// Выводим
foreach ($aPage as $k=>$v) {
	if ($v['draft'] == 0) {
		$tpl->assign('content', crbr('<div style="font-size: 90%; margin-bottom: 8px;"><a href="'.$sRequest.'/'.$v['key'].'">'.$v['title'].'</a><br />'.$v['annotation'].'</div>'));
	}
}




include_once(FLGR_COMMON.'/stemmers.php');

if (isset($_GET['q'])) {
	
	$tpl->assign('search_query', $q);
	$_t->assign('search_query', $q);

	// Разбиваем на слова
	$aWords = preg_split("/[\s\W]+/", $q, -1, PREG_SPLIT_NO_EMPTY);
	
	// Формируем прямой запрос
	$q = implode(' ', $aWords);
	cStat::bSaveEvent(EVENT_SEARCH, $q);
	// Ищем в страницах
	$aMatch = getMatch($q);
	// Ищем в новостях
	$aMatchNews = getMatchNews($q);
	// Сливаем результаты
	$aOutMatch = array_merge($aMatch, $aMatchNews);
	// Сортируем по релевантности
 	uasort($aOutMatch, 'relcmp');
	// Выводим
	foreach ($aOutMatch as $k=>$v) {
		$tplSearchResults = $tpl->fetchBlock('SearchResults');
		$tplSearchResults->assign($v);
		$tplSearchResults->assign('path', $k);
		$tpl->assign('SearchResults', $tplSearchResults);
		$tplSearchResults->reset();
	}
	
	
	// Формируем стемминг-запрос
	$stem = '';
	$Stem = new Lingua_Stem_Ru();
	foreach ($aWords as $k=>$v) {
		$stem .= $Stem->stem_word($v).'* ';
	}
	$tpl->assign('stem', $stem);
	// Ищем в страницах
	$aStem = getStem($stem);
	// Ищем в новостях
	$aStemNews = getStemNews($stem);
	// Сливаем результаты
	$aOutStem = array_merge($aStem, $aStemNews);
	// Сортируем по релевантности
 	uasort($aOutStem, 'relcmp');
	// Выводим
	foreach ($aOutStem as $k=>$v) {
		$tplSearchStemResults = $tpl->fetchBlock('SearchStemResults');
		$tplSearchStemResults->assign('title', $v['title']);
		$tplSearchStemResults->assign('rel', $v['rel']);
		$tplSearchStemResults->assign('path', $k);
		$tpl->assign('SearchStemResults', $tplSearchStemResults);
		$tplSearchStemResults->reset();
	}

	
	if (empty($aMatch) && empty($aStemMatch)) {
		$tpl->assign('SearchResults', 'Ничего не найдено.');
	}
	
	$tpl->assign('SearchResults', '');
	$tpl->assign('SearchStemResults', '');
	
}
	
// CLOSE
$_t->assign('ContentBlock', $tpl);
$tpl->reset();

// SEO
$_t->assign('seo_title', $sSeoTitle);
$_t->assign('seo_keywords', $sSeoKeywords);
$_t->assign('seo_description', $sSeoDescription);

$bFlagCache = true;


function getPathPageFromId($id, $path='') {
	
	$sql = "SELECT 	`id`, `parent`, `key`
			FROM `".DB_PREFIX.DB_TBL_PAGES."`
			WHERE `id` = $id";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	$aPage = array();
	while ($row = mysql_fetch_assoc($sql)) {
		$aPage[] = $row;
	}
	$aPage = current($aPage);
	
	$path = $aPage['key'].'/'.$path;
	
	if ($aPage['parent'] != 0) {
		return getPathPageFromId($aPage['parent'], $path);
	}
	
	return($path);
}


function relcmp($a, $b)
{
    if ($a['rel'] == $b['rel']) {
        return 0;
    }
    return ($a['rel'] > $b['rel']) ? -1 : 1;
}

function getMatch($q)
{
	$aMatch = array();
	
	// Сначала ищем совпадения по `title`
	$sql = "SELECT 	`id`, `title`, MATCH (`title`, `title_menu`) AGAINST ('".$q."') AS `rel`
			FROM `".DB_PREFIX.DB_TBL_PAGES."`
			WHERE MATCH (`title`, `title_menu`) AGAINST ('".$q."')";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	while ($row = mysql_fetch_assoc($sql)) {
		$row['rel'] = $row['rel']+6;
		$aMatch[getPathPageFromId($row['id'])] = $row;
	}
	
	// Ищем совпадения по `text`
	$sql = "SELECT 	`id`, `title`, MATCH (`text`) AGAINST ('".$q."') AS `rel`
			FROM `".DB_PREFIX.DB_TBL_PAGES."`
			WHERE MATCH (`text`) AGAINST ('".$q."')";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	while ($row = mysql_fetch_assoc($sql)) {
		$row['rel'] = $row['rel']+3;
		// Если эта страница уже найдена - добавить ей веса
		$path = getPathPageFromId($row['id']);
		if (!isset($aMatch[$path])) {
			$aMatch[$path] = $row;
		} else {
			$aMatch[$path]['rel'] = $aMatch[$path]['rel'] + $row['rel'];
		}
	}
	
	return $aMatch;
}


function getMatchNews($q)
{
	$aMatchNews = array();
	// Сначала ищем совпадения по `title`
	$sql = "SELECT 	`id`, `title`, MATCH (`title`) AGAINST ('".$q."') AS `rel`
			FROM `".DB_PREFIX.DB_TBL_POSTS."`
			WHERE MATCH (`title`) AGAINST ('".$q."')";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	while ($row = mysql_fetch_assoc($sql)) {
		$path = '/postid/'.$row['id'];
		$row['rel'] = $row['rel']+6;
		$aMatchNews[$path] = $row;
	}
	
	// Ищем совпадения по `text`
	$sql = "SELECT 	`id`, `title`, MATCH (`text`) AGAINST ('".$q."') AS `rel`
			FROM `".DB_PREFIX.DB_TBL_POSTS."`
			WHERE MATCH (`text`) AGAINST ('".$q."')";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	while ($row = mysql_fetch_assoc($sql)) {
		$row['rel'] = $row['rel']+3;
		$path = '/postid/'.$row['id'];
		// Если эта страница уже найдена - добавить ей веса
		if (!isset($aMatchNews[$path])) {
			$aMatchNews[$path] = $row;
		} else {
			$aMatchNews[$path]['rel'] = $aMatchNews[$path]['rel'] + $row['rel'];
		}
	}
	
	return $aMatchNews;
}



function getStem($q) 
{
	global $aMatch;
	
	$aStem = array();
	
	// Сначала ищем совпадения по `title`
	$sql = "SELECT 	`id`, `title`, MATCH (`title`, `title_menu`) AGAINST ('".$q."' IN BOOLEAN MODE) AS `rel`
			FROM `".DB_PREFIX.DB_TBL_PAGES."`
			WHERE MATCH (`title`, `title_menu`) AGAINST ('".$q."' IN BOOLEAN MODE)";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	while ($row = mysql_fetch_assoc($sql)) {
		$aStem[getPathPageFromId($row['id'])] = $row;
	}
	
	// Ищем совпадения по `text`
	$sql = "SELECT 	`id`, `title`, MATCH (`text`) AGAINST ('".$q."' IN BOOLEAN MODE) AS `rel`
			FROM `".DB_PREFIX.DB_TBL_PAGES."`
			WHERE MATCH (`text`) AGAINST ('".$q."' IN BOOLEAN MODE)";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	while ($row = mysql_fetch_assoc($sql)) {
		// Если эта страница уже найдена - добавить ей веса
		$path = getPathPageFromId($row['id']);
		if (!isset($aStem[$path])) {
			$aStem[$path] = $row;
		} else {
			$aStem[$path]['rel'] = $aStem[$path]['rel'] + $row['rel'];
		}
	}
	
	// Если страницы, найденные по стеммингу уже были найдены обычным способом
	// то добавить им релевантности там, а здесь - удалить
	foreach ($aStem as $k=>$v) {
		if (isset($aMatch[$k])) {
			$aMatch[$k]['rel'] += $aStem[$k]['rel'];
		}
	}
	foreach ($aMatch as $k=>$v) {
		if (isset($aStem[$k])) {
			unset($aStem[$k]);
		}
	}
	return $aStem;
}



function getStemNews($q)
{
	global $aMatch;
		
	$aStemNews = array();
	// Сначала ищем совпадения по `title`
	$sql = "SELECT 	`id`, `title`, MATCH (`title`) AGAINST ('".$q."' IN BOOLEAN MODE) AS `rel`
			FROM `".DB_PREFIX.DB_TBL_POSTS."`
			WHERE MATCH (`title`) AGAINST ('".$q."' IN BOOLEAN MODE)";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	while ($row = mysql_fetch_assoc($sql)) {
		$path = '/postid/'.$row['id'];
		$aStemNews[$path] = $row;
	}
	
	// Ищем совпадения по `text`
	$sql = "SELECT 	`id`, `title`, MATCH (`text`) AGAINST ('".$q."' IN BOOLEAN MODE) AS `rel`
			FROM `".DB_PREFIX.DB_TBL_POSTS."`
			WHERE MATCH (`text`) AGAINST ('".$q."' IN BOOLEAN MODE)";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	while ($row = mysql_fetch_assoc($sql)) {
		$path = '/postid/'.$row['id'];
		// Если эта страница уже найдена - добавить ей веса
		if (!isset($aStemNews[$path])) {
			$aStemNews[$path] = $row;
		} else {
			$aStemNews[$path]['rel'] = $aStemNews[$path]['rel'] + $row['rel'];
		}
	}
	
	// Если страницы, найденные по стеммингу уже были найдены обычным способом
	// то добавить им релевантности там, а здесь - удалить
	foreach ($aStemNews as $k=>$v) {
		if (isset($aMatch[$k])) {
			$aMatch[$k]['rel'] += $aStemNews[$k]['rel'];
		}
	}
	foreach ($aMatch as $k=>$v) {
		if (isset($aStemNews[$k])) {
			unset($aStemNews[$k]);
		}
	}
	
	return $aStemNews;
}

?>
