<?php

// ADD_BREADCRUMBS
$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);



if (!$bFlagLastModule) return;



// HEAD_TITLE
$_t->assign('head_title', $sTitle);

// BREADCRUMBS
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());


// OPEN
$tpl = $_t->fetchBlock('ContentBlock');

// CONTENT
$tpl->assign('title', $sTitle);
$tpl->assign('content', crbr(($sText)));


$tplSitemap = new KTemplate(FLGR_TEMPLATES.'/'.$sModuleTpl.'.htm');


foreach (aGetMenu() as $k=>$v) {
	$tplMenuElt = $tplSitemap->fetchBlock('MainMenuElt');
	$tplMenuElt->assign('key', $v['key']);
	$tplMenuElt->assign('title', $v['title']);
	$tplMenuElt->assign('level', count($v['level'])*15);
	$tplMenuElt->assign('if_admin', '');
	$link = implode('/', $v['level']);
	if ($link == '') { $link = '/';	}
	$tplMenuElt->assign('link', $link);
	$tplSitemap->assign('MainMenuElt', $tplMenuElt);
	$tplMenuElt->reset();
}

$tpl->assign('content', $tplSitemap);

// CLOSE
$_t->assign('ContentBlock', $tpl);
$tpl->reset();



// SEO
$_t->assign('seo_title', $sSeoTitle);
$_t->assign('seo_keywords', $sSeoKeywords);
$_t->assign('seo_description', $sSeoDescription);

?>