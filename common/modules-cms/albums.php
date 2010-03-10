<?php

// BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

if (!$bFlagLastModule) return;

// POST 

if ( (isset($_POST['act'])) ) {
	include_once(FLGR_CMS_MODULES.'/albums-dispatcher.php');
}

// GET

$tpl = new KTemplate(FLGR_CMS_TEMPLATES.'/albums.htm');


// Получаем все альбомы
$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_ALBUMS."`";
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aList = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aList[] = $row;
}

if (empty($aList)) {
	$tpl->assign('albums', 'Нет ни одного альбома');
} else {
	// Выводим альбомы
	foreach ($aList as $k=>$v) {
		$tplAlbums = $tpl->fetchBlock('albums');
		$tplAlbums->assign('album-edit', $aCmsModules['album-edit']['key'].'/'.$v['id']);
		$tplAlbums->assign('album-del', $aCmsModules['album-del']['key'].'/'.$v['id']);
		$tplAlbums->assign('album-to', $aCmsModules['albums']['key'].'/'.$v['id']);
		if (isset($aRequest[$nLevel+1]) && $aRequest[$nLevel+1] == $v['id']) {
			$tplAlbums->assign('album-name', '<b>'.$v['name'].'</b>');
		} else {
			$tplAlbums->assign('album-name', $v['name']);
		}
		$tpl->assign('albums', $tplAlbums);
		$tplAlbums->reset();
	}
}

// Right

if (!$bFlag404) {
	$tpl->assign('upload', '');
	$tpl->assign('reorder-img', '');
	$tpl->assign('imgs', '');
	$tpl->assign('preview', '');
} else {
	if (!isset($aRequest[$nLevel+3])) {
	
		// Показать альбом
		
		// Проверяем существование альбома
		$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_ALBUMS."` WHERE `id` = ".$aRequest[$nLevel+1];
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		$aList = array();
		while ($row = mysql_fetch_assoc($sql)) {
			$aList[] = $row;
		}
		
		// Если альбом не существует - выводим ошибку и завершаем работу, сбрасывая флаг 404
		if (empty($aList)) {
			$_t->assign('content', '<span style="color: red">Ошибка:</span> Альбом не найден');
			$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
			$bFlag404 = false;
			return;
		}
		
		// Форма аплоада
		$tplUpload = $tpl->fetchBlock('upload');
		$tplUpload->assign('album', $aRequest[$nLevel+1]);
		$tpl->assign('upload', $tplUpload);
		$tplUpload->reset();
		
		// Кнопка пересортировки
		$tplReorder = $tpl->fetchBlock('reorder-img');
		$tpl->assign('reorder-img', $tplReorder);
		$tplReorder->reset();
		
		// Получаем фотографии альбома
		$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_IMAGES."` WHERE `album` = ".$aRequest[$nLevel+1]." ORDER BY `order`";
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		$aImgs = array();
		while ($row = mysql_fetch_assoc($sql)) {
			$aImgs[$row['id']] = $row;
		}
		
		if (empty($aImgs)) {
			$tpl->assign('imgs', 'Нет изображений в этом альбоме');
		} else {
			// Выводим фотографии альбома
			foreach ($aImgs as $v) {
				$tplImgs = $tpl->fetchBlock('imgs');
				$tplImgs->assign('id', $v['id']);
				$tplImgs->assign('album', $v['album']);
				$tplImgs->assign('order', $v['order']);
				$tplImgs->assign('album-to', $aCmsModules['albums']['key']);
				$tplImgs->assign('file', ADDR_PHOTOS_THUMBNAILS.'/'.$v['file']);
				if ( (isset($aRequest[$nLevel+2])) && ($aRequest[$nLevel+2]) == $v['id'] ) {
					$tplImgs->assign('name', '<b>'.$v['name'].'<b>');
				} else {
					$tplImgs->assign('name', $v['name']);
				}
				$tplImgs->assign('descr', $v['descr']);
				$tplImgs->assign('title', $v['title']);
				$tpl->assign('imgs', $tplImgs);
				$tplImgs->reset();
			}
		}
		
		// Блок для превьюшек фотографий
		$tplPreview = $tpl->fetchBlock('preview');
		if (!isset($aRequest[$nLevel+2])) {
			$tplPreview->assign('file', '/img/spacer.gif');
			$tplPreview->assign('id', 'stubid');
			$tplPreview->assign('descr', '');
			$tplPreview->assign('title', '');
		} else {
			$tplPreview->assign('file', ADDR_PHOTOS_NORMALS.'/'.$aImgs[$aRequest[$nLevel+2]]['file']);
			$tplPreview->assign('id', $aRequest[$nLevel+2]);
			$tplPreview->assign('descr', $aImgs[$aRequest[$nLevel+2]]['descr']);	
			$tplPreview->assign('title', $aImgs[$aRequest[$nLevel+2]]['title']);
		}
		$tpl->assign('preview', $tplPreview);
		$tplPreview->reset();
		
		// Если все ок - снимаем флаг 404
		$bFlag404 = false;
	}
}


// OUT
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());
$_t->assign('content', $tpl);

?>
