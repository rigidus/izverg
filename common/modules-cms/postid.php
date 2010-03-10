<?php

$bFlagErrorPostId = false;

if (!isset($aRequest[$nLevel+2])) {
	$bFlagErrorPostId = true;
} elseif (!is_numeric($aRequest[$nLevel+2])) {
	$bFlagErrorPostId = true;
}

if ($bFlagErrorPostId) {
	// HEAD_TITLE
	$_t->assign('head_title', '');

	// ADD_BREADCRUMBS
	$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

	// BREADCRUMBS
	$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());

	$tpl = $_t->fetchBlock('ContentBlock');

	// CONTENT
	$tpl->assign('title', 'Ошибка');
	$tpl->assign('content', 'Неправильный адрес новости');

	// CLOSE
	$_t->assign('ContentBlock', $tpl);
	$tpl->reset();

	// SEO
	$_t->assign('seo_title', $sSeoTitle);
	$_t->assign('seo_keywords', $sSeoKeywords);
	$_t->assign('seo_description', $sSeoDescription);

	$bFlag404 = false;
	$bFlagLastModule = false;
	return;
}

if ( (isset($_POST['act'])) && ($_POST['act'] == 'add_comment') && isset($_SESSION['user'])) {
	$_POST['user'] = $_SESSION['user']['id'];
	$_POST['text'] = sFilter($_POST['text']);
	$_POST['post'] = $aRequest[$nLevel+2];
	$sql = 'INSERT INTO `'.DB_PREFIX.DB_TBL_COMMENTS.'` ( `id` , `post` , `parent` , `user` , `t` , `text` ) ';
	$sql .= "VALUES ('', '".$_POST['post']."', '".$_POST['parent']."', '".$_POST['user']."', NOW( ) , '".$_POST['text']."')";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	cStat::bSaveEvent(EVENT_ADDCOMMENT, $aRequest[$nLevel+2].' '.' '.$_POST['text']);
	// notify
	$post = $_POST['post'];
	$aNotifyUsers = array();
	$parent = $_POST['parent'];
	while ($parent != 0) {
		$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_COMMENTS."` WHERE ( (`post` = $post) AND ( `id` = $parent) )";
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		$aParentPost = mysql_fetch_assoc($sql);
		$aNotifyUsers[$aParentPost['user']] = array();
		$parent = $aParentPost['parent'];
	}
	if (!empty($aNotifyUsers)) {
		$bTmp = 0;
		$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_USERS."` WHERE ( ";
		foreach ($aNotifyUsers as $k=>$v) {
			if ($bTmp) {
				$sql .= ' OR ';
			}
			$sql .= " (`id` = $k) ";
			$bTmp = 1;
		}
		$sql .= " )";
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		$aNotifyUsers = array();
		while ($row = mysql_fetch_assoc($sql)) {
			$aNotifyUsers[$row['id']] = $row;
		}
		$aEmailsUsers = array();
		foreach ($aNotifyUsers as $k=>$v) {
			if ( ($v['not_notify'] == 0) && (!empty($v['email'])) && ($v['email'] != $_SESSION['user']['email']) ) {
				$aEmailsUsers[] = $v['email'];
			}
		}
		$message = 'Пользователь '.$_SESSION['user']['name'].' ответил на ваш комментарий в обсуждении на странице http://'.HOST.$sRequest;
		$subject = 'Ответ на ваш комментарий на сайте '.HOST;
		foreach (array_flip($aEmailsUsers) as $k=>$v) {
			 my_mail($message, $subject, $k);
		}
	}
}


$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_POSTS.'` WHERE `id` = '.$aRequest[$nLevel+2];
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aPost = mysql_fetch_assoc($sql);

if (empty($aPost)) {
	// HEAD_TITLE
	$_t->assign('head_title', '');

	// ADD_BREADCRUMBS
	$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

	// BREADCRUMBS
	$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());

	$tpl = $_t->fetchBlock('ContentBlock');

	// CONTENT
	$tpl->assign('title', 'Ошибка');
	$tpl->assign('content', 'Нет такой новости!');

	// CLOSE
	$_t->assign('ContentBlock', $tpl);
	$tpl->reset();

	// SEO
	$_t->assign('seo_title', $sSeoTitle);
	$_t->assign('seo_keywords', $sSeoKeywords);
	$_t->assign('seo_description', $sSeoDescription);

	$bFlag404 = false;
	$bFlagLastModule = false;
	return;
}


// ADD_BREADCRUMBS
$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);
$BreadCrumbs->addBreadCrumbs($aRequest[$nLevel+1].'/'.$aRequest[$nLevel+2], $aPost['title']);


$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_COMMENTS.'` WHERE `post` = '.$aPost['id'].' ORDER BY `t` ASC';
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aComments = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aComments[$row['id']] = $row;
}

$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_USERS;
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aUsers = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aUsers[$row['id']] = $row;
}


// HEAD_TITLE
$_t->assign('head_title', $aPost['title']);

// BREADCRUMBS
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());

// OPEN
if ($sModuleTpl != '') {
	$tpl = new KTemplate(FLGR_TEMPLATES.'/'.$sModuleTpl.'.htm');
} else {
	$tpl = $_t->fetchBlock('ContentBlock');
}

// CONTENT
$tpl->assign('title', $aPost['title']);
$tpl->assign('content', '<div style="position: relative; top: -15px; font-size: 80%">'.str_replace(' ', '&nbsp;', date_humanize($aPost['t'])).'</div>');
$tpl->assign('content', crbr($aPost['text'].'<br /><br />'));




$tplBlog = new KTemplate(FLGR_TEMPLATES.'/blog.htm');

if ($Permissions->bIsLogged()) {
	$tpl->assign('content', $tplBlog->fetchBlock('stub'));
	$tpl->assign('content', $tplBlog->fetchBlock('post_comment_top'));
} else {
	$tpl->assign('content', '<center><small>Зарегистрируйтесь или войдите чтобы оставить комментарий</small><center><br />');
}



global $aTree;
$aTree = $aComments;
global $aOutTree;
$aOutTree = array();

foreach ($aComments as $k=>$v) {
	if ($v['parent'] == 0) {
		DendroId($v['id'], array());
	}
}

foreach ($aOutTree as $v) {
	$v['text'] = str_replace('<br /><br />', '<br />', normalize($v['text']));
	$v['t'] = str_replace(' ', '&nbsp;', date_humanize($v['t']));
	$v['level'] = count($v['level'])*20-20;
	$v['user'] = $Permissions->getUserNameFromId($v['user']);
	$tplComment = $tplBlog->fetchBlock('comment');
	$tplComment->assign($v);
	if ($Permissions->bIsAdmin()) {
		$tplComment->assign('if_admin', '<a href="/commentedit/'.$v['id'].'">'.'<img src="/img/edit.gif">'.'</a>&nbsp;');
		$tplComment->assign('if_admin', '<a href="/commentdel/'.$v['id'].'">'.'<img src="/img/del.gif">'.'</a>&nbsp;');
	} else {
		$tplComment->assign('if_admin', '');
	}
	if (isset($_SESSION['user'])) {
		$tplIsLogged = $tplComment->fetchBlock('is_logged');
		$tplIsLogged->assign('id', $v['id']);
		$tplComment->assign('is_logged', $tplIsLogged);
		$tplIsLogged->reset();
	} else {
		$tplComment->assign('is_logged', '');
	}
	$tpl->assign('content', $tplComment);
	$tplComment->reset();
}

if (isset($_SESSION['user'])) {
	if (!empty($aComments)) {
		$tpl->assign('content', $tplBlog->fetchBlock('post_comment_bottom'));
	}
}


// CLOSE
$_t->assign('ContentBlock', $tpl);
$tpl->reset();

// SEO
$_t->assign('seo_title', $sSeoTitle);
$_t->assign('seo_keywords', $sSeoKeywords);
$_t->assign('seo_description', $sSeoDescription);

$bFlag404 = false;
$bFlagLastModule = false;

?>