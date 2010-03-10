<?php

switch ($_POST['act']):

	case 'reorder-img';
		foreach ($_POST['order'] as $k=>$v) {
			$sql = "UPDATE `".DB_PREFIX.DB_TBL_IMAGES."` SET `order` = '$v' WHERE `id` ='".$k."'";
			$sql = mysql_query($sql);
			if (false == $sql) my_die();
		}
		break;

	case 'newalbum':
		$sql = "INSERT INTO `".DB_PREFIX.DB_TBL_ALBUMS."` (`name`) VALUES ('".$_POST['name']."')";
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		break;
		
	case 'delimage':
		$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_IMAGES."` WHERE `id` = ".$_POST['id'];
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		$aImg = array();
		while ($row = mysql_fetch_assoc($sql)) {
			$aImg = $row;
		}
		if (!empty($aImg)) {
			unlink(IMG_BIG_DIR.'/'.$aImg['file']);
			unlink(IMG_NORMAL_DIR.'/'.$aImg['file']);
			unlink(IMG_THUMBNAIL_DIR.'/'.$aImg['file']);
			$sql = "DELETE FROM `".DB_PREFIX.DB_TBL_IMAGES."` WHERE `id` = ".$aImg['id'];
			$sql = mysql_query($sql);
			if (false == $sql) my_die();
		}
		header('Location: '.$aCmsModules['albums']['key'].'/'.$aRequest[$nLevel+1]);
		break;
		
	case 'descr':
		//dbg($_POST);
		if ($_POST['id'] == 'stubid') {
			break;
		}
		$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_IMAGES."` WHERE `id` = ".$_POST['id'];
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		$aList = array();
		while ($row = mysql_fetch_assoc($sql)) {
			$aList[] = $row;
		}
		if (!empty($aList)) {
			$sql = "UPDATE `".DB_PREFIX.DB_TBL_IMAGES."` SET 
					`descr` = '".mysql_escape_string($_POST['descr'])."', 
					`title` = '".mysql_escape_string($_POST['title'])."' 
				WHERE `id` ='".$_POST['id']."' LIMIT 1;";
			//dbg($sql);
			$sql = mysql_query($sql);
			if (false == $sql) my_die();
			header('Location: '.$aCmsModules['albums']['key'].'/'.$aList[0]['album'].'/'.$aList[0]['id']);
		}
		break;
		
	case 'imgupload':
		if(isset($_FILES["file"]["error"])) {
			// Create picture link
			if ($_FILES['file']['error'] != 4) {
		
				// Save BIG picture
				$filename = md5(getmicrotime()).'.jpg';
				move_uploaded_file($_FILES["file"]["tmp_name"], IMG_BIG_DIR.'/'.$filename);
				//chmod($path.'/'.$_FILES["file"]["name"], 0777);
				
				// Processed
				
					// Открываем файл
					$rImg = imagecreatefromjpeg(IMG_BIG_DIR.'/'.$filename);
					
					// Определяем, горизонтальный он или вертикальный
					$nWidth = imagesx($rImg);
					$nHeight = imagesy($rImg);
					
					// normal
					$nPixLimit = 400;
					
					// Определяем новые размеры
					if ($nWidth > $nHeight) {
						$orientation = 'horizontal';
						$nX = $nPixLimit;
						$nTmp = $nWidth / $nPixLimit;
						$nY = round($nHeight / $nTmp);
					} elseif ($nWidth < $nHeight) {
						$orientation = 'vertical';
						$nY = $nPixLimit;
						$nTmp = $nWidth * $nPixLimit;
						$nX = round($nTmp / $nHeight);
					} else {
						$orientation = 'square';
						$nX = $nPixLimit;
						$nY = $nPixLimit;
					}
					
					// Масштабируем
					$rNewImg = imagecreatetruecolor($nX ,$nY);
					imagecopyresampled($rNewImg, $rImg,
						0,0,0,0,
						$nX, $nY,
						$nWidth, $nHeight);
						
					// Сохраняем
					imagejpeg($rNewImg, IMG_NORMAL_DIR.'/'.$filename);
					
					// thumbnail
					$nPixLimit = 170;
					define('BRUTTO', 185);
					
					// Определяем новые размеры
					if ($nWidth > $nHeight) {
						$orientation = 'horizontal';
						$nX = $nPixLimit;
						$nTmp = $nWidth / $nPixLimit;
						$nY = round($nHeight / $nTmp);
					} elseif ($nWidth < $nHeight) {
						$orientation = 'vertical';
						$nY = $nPixLimit;
						$nTmp = $nWidth * $nPixLimit;
						$nX = round($nTmp / $nHeight);
					} else {
						$orientation = 'square';
						$nX = $nPixLimit;
						$nY = $nPixLimit;
					}
					
					// Масштабируем
					$rNewImg = imagecreatetruecolor($nX ,$nY);
					imagecopyresampled($rNewImg, $rImg,
						0,0,0,0,
						$nX, $nY,
						$nWidth, $nHeight);
				
					// Добавляем контур
					$rStroke = imagecreatetruecolor($nX+2, $nY+2);
					imagecopy($rStroke, $rNewImg, 1, 1, 0, 0, $nX+2, $nY+2);
					imagerectangle($rStroke, 0, 0, $nX+2-1, $nY+2-1, 0x7F7F7F);
					
					// Добавляем поля
					$rThumbNail = imagecreatetruecolor(BRUTTO, BRUTTO);
					imagefill($rThumbNail, 0, 0, 0xFFFFFF);
					imagecopy($rThumbNail, $rStroke, (BRUTTO - $nX+2) / 2 - 1, (BRUTTO - $nY+2) / 2 - 1, 0, 0, $nX+2, $nY+2);
					
					// Добавляем второй контур
					imagerectangle($rThumbNail, 0, 0, BRUTTO-1,  BRUTTO-1, 0x7F7F7F);
		
					// Сохраняем
					imagejpeg($rThumbNail, IMG_THUMBNAIL_DIR.'/'.$filename);
				
				
				// Save to bd
				$sql = "INSERT INTO `".DB_PREFIX.DB_TBL_IMAGES."` (`album`, `name`, `file`) VALUES ('".$_POST['album']."', '".$_FILES['file']['name']."', '$filename')";
				$sql = mysql_query($sql);
				if (false == $sql) my_die();
		
			} else {
				echo 'error upload';
			}
		} else {
			echo 'interface error';
		}
		break;
endswitch;

?>