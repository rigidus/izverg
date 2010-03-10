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
$tpl->assign('content', crbr($sape_context->replace_in_text_segment($sText)));

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

// CLOSE
$_t->assign('ContentBlock', $tpl);
$tpl->reset();

// SEO
$_t->assign('seo_title', $sSeoTitle);
$_t->assign('seo_keywords', $sSeoKeywords);
$_t->assign('seo_description', $sSeoDescription);

$bFlagCache = true;

?>
