<?php

// SPECAL MODULES

if ($bFlagLastModule) {
	if ($bFlag404) {
		foreach ($aSpecialModules as $v) {
			if ($aRequest[1] == $v) {
				include_once(FLGR_CMS_MODULES.'/'.$v.'.php');
			}
		}
	}
}


// login
if ((isset($_POST['act'])) && ($_POST['act'] == 'login')) {
	$Permissions->login($_POST['login'], $_POST['password']);
	header('Location: '.$_SERVER['HTTP_REFERER']);
	include(FLGR_COMMON.'/exit.php');
}


// {search_query}
$_t->assign('search_query', '');

// {sape} {mylinks}
$_t->assign('my_links', '<small><br />'.$sape->return_links().'</small>');


if (!defined('STATISTIC_ON')) {
	$_t->assign('STATISTIC', '');
} else {
	$_t->assign('STATISTIC', $_t->fetchBlock('STATISTIC'));
}


define('TOP_MENU_ID', 20);
define('TOP_MENU_KEY', 'info');
define('TOP_MENU_LEVEL', 1);
define('SHOW_MENU_LEVELS', 3);
foreach (aGetMenu() as $k=>$v) {

	if ( 	(isset($v['level'][TOP_MENU_LEVEL])) &&
			($v['level'][TOP_MENU_LEVEL] == TOP_MENU_KEY) &&
			($v['parent'] == TOP_MENU_ID) ) {
		$tplMenuElt = $_t->fetchBlock('TopMenuElt');
		$tplMenuElt->assign('title_menu', $v['title_menu']);
		$link = implode('/', $v['level']);
		if ($link == '') { $link = '/';	}
		$tplMenuElt->assign('link', $link);
		$_t->assign('TopMenuElt', $tplMenuElt);
		$tplMenuElt->reset();
	} elseif ($v['id'] != TOP_MENU_ID) {
		if (count($v['level']) <= SHOW_MENU_LEVELS) {
			$tplMenuElt = $_t->fetchBlock('MainMenuElt');
			$tplMenuElt->assign('key', $v['key']);
			$tplMenuElt->assign('title', $v['title']);
			$tplMenuElt->assign('level', count($v['level'])*15);
			$tplMenuElt->assign('if_admin', '');
			$link = implode('/', $v['level']);
			if ($link == '') { $link = '/';	}
			$tplMenuElt->assign('link', $link);
			$_t->assign('MainMenuElt', $tplMenuElt);
			$tplMenuElt->reset();
		}
	}

}

if (!$Permissions->bIsLogged()) {
	$_t->assign('logon', $_t->fetchBlock('logon'));
	$_t->assign('logout', '');
} else {
	$_t->assign('logon', '');
	$tplLogout = $_t->fetchBlock('logout');
	$tplLogout->assign('user_name', $_SESSION['user']['name']);
	$_t->assign('logout', $tplLogout);
}

// ADD_BREADCRUMBS
$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);





if (!$bFlagLastModule) return;





// HEAD_TITLE
$_t->assign('head_title', HEAD_TITLE);

// BREADCRUMBS
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());

/*
// OPEN
if ($sModuleTpl != '') {
	$tpl = new KTemplate(FLGR_TEMPLATES.'/'.$sModuleTpl.'.htm');
} else {
	$tpl = $_t->fetchBlock('ContentBlock');
}

// CONTENT
$tpl->assign('title', $sTitle);
$tpl->assign('content', crbr(($sText)));

// CLOSE
$_t->assign('ContentBlock', $tpl);
$tpl->reset();

*/

// SEO
$_t->assign('seo_title', $sSeoTitle);
$_t->assign('seo_keywords', $sSeoKeywords);
$_t->assign('seo_description', $sSeoDescription);



// BLOG

define('POST_PER_PAGE', 6);
$nStart = 0;

if ($bFlag404) {

	if (count($aRequest)-1 > $nLevel) {

		// micromodules
		if (count($aRequest)-1 == $nLevel+1) {
			// logout
			if ($aRequest[$nLevel+1] == 'logout') {
				$Permissions->logout();
				header('Location: '.$_SERVER['HTTP_REFERER']);
				include(FLGR_COMMON.'/exit.php');
			}
			// number of post page
			if (is_numeric($aRequest[$nLevel+1])) {
				$nStart = $aRequest[$nLevel+1];
				if ($nStart <= 0) {
					$nStart = 0;
				}
				$bFlag404 = false;
			}
		}

	}

}

$_t->assign('title', 'Последние записи');

$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_POSTS.'` ORDER BY `t` DESC LIMIT '.$nStart.','.POST_PER_PAGE;
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aPosts = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aPosts[] = $row;
}

if (empty($aPosts)) {
	$_t->assign('ContentBlock', 'Больше записей нет');
}

$tplBlog = new KTemplate(FLGR_TEMPLATES.'/blog.htm');

$tplPost = $tplBlog->fetchBlock('post');
foreach ($aPosts as $k=>$v) {
	$tplPost->assign('link', $v['id']);
	$tplPost->assign('title', $v['title']);
//	dbg($sape_context);
	$tplPost->assign('text', crbr($sape_context->replace_in_text_segment($v['text'])));
	$tplPost->assign('t', str_replace(' ', '&nbsp;&nbsp;&nbsp;', date_humanize($v['t'])));
	$tplPost->assign('comments', nGetCountComments_FromPostId($v['id']));
	$_t->assign('ContentBlock', $tplPost);
	$tplPost->reset();
}

$sql = 'SELECT count(*) FROM `'.DB_PREFIX.DB_TBL_POSTS.'`';
$sql = mysql_query($sql);
if (false == $sql) my_die();
$nCountPost = current(mysql_fetch_assoc($sql));

$tplPrevNext = $tplBlog->fetchBlock('prev_next');
if ($nCountPost > ($nStart+POST_PER_PAGE)) {
	$tplPrev = $tplPrevNext->fetchBlock('block_prev');
	$tplPrev->assign('prev', $nStart+POST_PER_PAGE);
	$tplPrevNext->assign('block_prev', $tplPrev);
} else {
	$tplPrevNext->assign('block_prev', '');
}

$aReqLinks = array();
$j = 1;
for ($i=1; $i<=$nCountPost; $i=$i+POST_PER_PAGE) {
	$h = $i-1;
	if ($h == 0) {
		$h = '';
	}
	$aReqLinks[$h] = $j;
	$j++;
}


$aReqLinks = array_reverse($aReqLinks, true);

foreach ($aReqLinks as $k=>$v) {
	$tplRec = $tplPrevNext->fetchBlock('block_record');
	$tplRec->assign('num', $k);
	$tplRec->assign('num_page', $v);
	$tplPrevNext->assign('block_record', $tplRec);
	$tplRec->reset();
}


if ($nStart > 0) {
	$tplNext = $tplPrevNext->fetchBlock('block_next');
	$nCandidat = $nStart-POST_PER_PAGE;
	if ($nCandidat <= 0) {
		$nCandidat = '';
	}
	$tplNext->assign('next', $nCandidat);
	$tplPrevNext->assign('block_next', $tplNext);
} else {
	$tplPrevNext->assign('block_next', '');
}
$_t->assign('ContentBlock', $tplPrevNext);



function nGetCountComments_FromPostId($nId)
{
	$sql = 'SELECT count(*) FROM `'.DB_PREFIX.DB_TBL_COMMENTS.'` WHERE `post` = '.$nId;
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	return current($row = mysql_fetch_assoc($sql));
}

?>