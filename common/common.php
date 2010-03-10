<?php

/*

strips
// overbox | dbg | old_dbg | logfile | dbglog
saferead | safewrite
(un)slashify
normalize
aTreeGetChilds | DendroId | aGetMenu
int2ip | ip2int
date_humanize | date_mashinize
pluralForm | sTransliterator
tplList | sqlGetInsert | sqlGetSelect | sqlGetUpdate | sGetArray | array_serialize | array_fltr
my_die | my_info | my_mail
sGetQuery
getSubVersionsRecursive
isCorrectText
utf_to_cp1251
getmicrotime
isMailCorrect
mDownloadRemoteFile | mGetRemoteFile

*/


// strips


function strips(&$el) { 
  if (is_array($el)) 
    foreach($el as $k=>$v) 
      strips($el[$k]); 
  else $el = trim(stripslashes($el)); 
} 


// overbox | dbg | dbglog


function overbox($funcname, $params=array()) 
{
	ob_start();
	call_user_func_array($funcname, $params);
	$r = ob_get_contents();
	ob_end_clean();
	return $r;
}


function dbg($p, $s='')
{ 
	//print_r(debug_backtrace());
	echo("<hr><span style=\"color:red\">$s</span><br><pre>"); 
	$p = overbox('print_r', array($p));
	$p = str_replace('<', '&lt;', $p);
	echo($p);
	echo('</pre>'); 
}

function old_dbg($p, $s='')
{ 
	echo("<hr><pre>$s\r\n"); 
	print_r($p);
	echo("\r\n</pre>\r\n"); 
}

function logfile($p, $s)
{
	$t = overbox('old_dbg', $p);
	safewrite(FLGR_LOGS.'/'.$s.'_'.md5(microtime()).'.txt', $t);
}


function dbglog($var, $name='') 
{
	global $dbglog;
	$dbglog .= overbox('dbg', array($var, $name));
}


// saferead | safewrite


function saferead($file) {
	$buffer = "";
	ignore_user_abort(1);//слабонервных просим удалиться
	if(is_readable($file)) {
		$fp = fopen($file, "rb"); //открываем файлик
		if($fp) {
			$count = 100; //ставим лок на счетчик
			$lockbool=true; //включаем отработку лока
			while ($lockbool && $count>0) { //пока флок не вернул истину или не кончился счетчик
				//non-blocking use (у нас counter, с ним проще)
				$lockbool=!flock($fp, (LOCK_SH+LOCK_NB)); //пытаемся ставить флок - shared
				$count--;//уменьшаем каунтер
			}
			if (!$lockbool) { //lock отработал
					while (!feof($fp)) {
					  $buffer .= fread($fp, 2048); //дайте два кило!
					}
			    flock($fp, LOCK_UN); // отпираем файл
			} else {
			   //лок не отработал - т.е. вылет по каунтеру.
				 //Либо mandatory use (помним, что у него тоже false)
				 //Пытаемся прочитать "втупую" (на тупых системах read обычно неблокирующий)
					while (!feof($fp)) {
					  $buffer .= fread($fp, 512); //чуть меньшая порция, т.к. скорее всего ФС глюкавая (FAT)
					}
			}
			fclose($fp); //закрыть надо
		} else {
			//Не смогли открыть. Странно, но факт.
			return false;
		}
	} else {
		//Нет доступа. И не надо.
		return false;
	}
	ignore_user_abort(0);//можно выдохнуть
	return $buffer;
}


function safewrite($file, $data="") {
	ignore_user_abort(1);//слабонервных просим удалиться
	if(trim($file)!="" && (!file_exists($file) || is_writable($file))) {
		if(!file_exists($file)) {
			//создаем файл: если обращаться к существующему файлу как "w", то файл обрежется (с потерей данных, RTFM)
			$t = fopen($file, "w");
			fclose($t);
		}
		//код выше нужен для того, чтобы избежать "плевков" при "r"-режиме (он не создает файл)
		$fp = fopen($file, "r+b"); //открываем файлик. Без транка, именно в таком режиме.
		if($fp) {
			$count = 100; //ставим лок на счетчик
			$lockbool=true; //включаем отработку лока
			while ($lockbool && $count>0) { //пока флок не вернул истину или не кончился счетчик
				//тут NB не пройдет, поскольку нужен именно доступ процесса к файлу,
				//а не всякие "Delayed write (~failed)"
				$lockbool=!flock($fp, LOCK_EX); //пытаемся ставить флок
				$count--;//уменьшаем каунтер
			}
			if (!$lockbool) { //lock отработал
			   fwrite($fp, $data); //пишем что есть
			   ftruncate($fp,strlen($data));
			   flock($fp, LOCK_UN); // отпираем файл
			} else {
				//лок не отработал?..
				//либо файл блокирован наглухо, либо опять же mandatory use
				//работаем атомарной подменой
				$salt = md5(uniqid(rand(), true));
				//сначала закрыть существующий, мало ли что
				fclose($fp);
				//пишем в темповый
				$fp = fopen($file.$salt,"w+b");
			  fwrite($fp, $data);
				fclose($fp);
				//собссно подмена (Смирнов, salut!)
				rename($file,$file.$salt.".chk");
				rename($file.$salt,$file);
				//проверяем
				if(file_exists($file)) {
					//всё ок. Надо чанк прибить...
					@unlink($file.$salt.".chk");
					return true;
				} else {
					//dammit...надо вернуть как было
					//(не дай бог это пойдет в concurrent...н-да)
					@rename($file.$salt.".chk",$file);
					return false;
				}
			}
			fclose($fp); //закрыть надо
		} else {
			//Тупо не смогли открыть. Всякое бывает...
			return false;
		}
	} else {
		//Нет доступа. Значит, не судьба
		return false;
	}
	ignore_user_abort(0);//можно выдохнуть
	return true;
}


// (un)slashify


function slashify($path) {
    if ($path[strlen($path)-1] != '/') {
        $path = $path."/";
    }
    return $path;
}


function unslashify($path) {
    if ($path[strlen($path)-1] == '/') {
        $path = substr($path, 0, (strlen($path)-1) );
    }
    return $path;
}


// normalize


function normalize($text) {
	return $text;
	$out = trim($text);
	$out = preg_replace("#[\n\r]{3,}#si","<p>",$out);
	$out = preg_replace("#[\n\r]+#si","<br /><br />",$out);
	$out = preg_replace("#[\s]+#si"," ",$out);
	$expl = explode("<p>",$out);
	$out = "";
	foreach($expl as $chunk) $out .= "<p>".$chunk."</p>\n";
	return $out;
}


function crbr($text) {
	$out = trim($text);
	$out = preg_replace("#[\n\r]{3,}#si","<p>",$out);
	$out = preg_replace("#[\n\r]+#si","<br /><br />",$out);
	$out = preg_replace("#[\s]+#si"," ",$out);
	$expl = explode("<p>",$out);
	$out = "";
	foreach($expl as $chunk) $out .= "<p>".$chunk."</p>\n";
	return $out;
}


// aTreeGetChilds | DendroId | aGetMenu


function aTreeGetChilds($nId)
{
	global $aTree;
	$aResult = array();
	foreach ($aTree as $k=>$v) {
		if ($v['parent'] == $nId) {
			$aResult[] = $k;
		}
	}
	return $aResult;
}


function DendroId($nId, $aLevel)
{
	global $aTree;
	global $aOutTree;
	if (isset($aTree[$nId]['key'])) {
		$aLevel[] = $aTree[$nId]['key'];
	} else {
		$aLevel[] = $aTree[$nId]['id'];
	}
	$aNew = $aTree[$nId];
	$aNew['level'] = $aLevel;
	$aOutTree[] = $aNew;
	$aChilds = aTreeGetChilds($nId);
	foreach ($aChilds as $k=>$v) {
		DendroId($v, $aLevel);
	}
	return $aLevel;
}


function aGetMenu()
{
	global $aTree;
	global $aOutTree;
	if (MENU_GEN or(!file_exists(FILE_CACHE_TREE))) {
		$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_PAGES.'` WHERE ((`hidden` = 0)) ORDER BY `order`';
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		$aTreeMain = array();
		while ($row = mysql_fetch_assoc($sql)) {
			$row['title'] = str_replace(' ', '&nbsp;', $row['title']);
			$aTreeMain[$row['id']] = $row;
		}
		$aTreeNew = $aTreeMain;
		foreach ($aTreeMain as $k=>$v) {
			// Если встретилась страница-draft, 
			// которая не удалена из нового массива
			if ((isset($aTreeNew[$k])) && 
				($v['subversion'] == 0) && 
				($v['draft'] == 1)) 
			{
				// Получаем ее подверсии
				$aTree = array();
				getSubVersionsRecursive($v['id']);
				// Ищем среди них не-draft
				foreach ($aTree as $kk=>$vv) {
					// Когда находим
					if ($vv['draft'] == 0) {
						// Перезаписываем элемент страницы-draft в новом массиве
						// на найденный не-draft и ставим subversion=0
						$aTreeNew[$k] = $vv;
						$aTreeNew[$k]['subversion'] = 0;
						// Но id, parent и key оставляем
						$aTreeNew[$k]['id'] = $v['id'];
						$aTreeNew[$k]['parent'] = $v['parent'];
						$aTreeNew[$k]['key'] = $v['key'];
						break;
					}
					
				}
			}
		}
		// debug foreach ($aTreeNew as $k=>$v) {$aTreeNew[$k]['title']=$aTreeNew[$k]['title'].'_'.$aTreeNew[$k]['draft'].'_'.$aTreeNew[$k]['subversion'];}
		$aTree = array();
		// Оставляем в массиве только рабочие версии
		foreach ($aTreeNew as $k=>$v) {
			if (($v['subversion'] == 0) && ($v['draft'] == 0)) {
				$aTree[$k] = $v;
			}
		}
		$aOutTree = array();
		DendroId(1, array());
		safewrite(FILE_CACHE_TREE, serialize($aOutTree));
	} else {
		$aOutTree = unserialize(file_get_contents(FILE_CACHE_TREE));
	}
	return $aOutTree;
}


// int2ip | ip2int


function int2ip($i) 
{ // INET_NTOA(3520061480)
	$d[0]=(int)($i/256/256/256);
	$d[1]=(int)(($i-$d[0]*256*256*256)/256/256);
	$d[2]=(int)(($i-$d[0]*256*256*256-$d[1]*256*256)/256);
	$d[3]=$i-$d[0]*256*256*256-$d[1]*256*256-$d[2]*256;
	return "$d[0].$d[1].$d[2].$d[3]";
}


function ip2int($ip) 
{ // INET_ATON("209.207.224.40") 
	$a=explode(".",$ip);
	return $a[0]*256*256*256+$a[1]*256*256+$a[2]*256+$a[3];
}


// date_humanize | date_mashinize


function date_humanize($p)
{
	$year = substr($p, 0, 4);
	$month = substr($p, 5, 2);
	$day = substr($p, 8, 2);
	$hour = substr($p, 11, 2);
	$min = substr($p, 14, 2);
	$sec = substr($p, 17, 2);
	return ($day.'.'.$month.'.'.$year.' '.$hour.':'.$min.':'.$sec);
}

function date_mashinize($p)
{
	$year = substr($p, 0, 4);
	$month = substr($p, 5, 2);
	$day = substr($p, 8, 2);
	$hour = substr($p, 11, 2);
	$min = substr($p, 14, 2);
	$sec = substr($p, 17, 2);
	if(defined('LOCALHOST')) {
		return ($year.'-'.$month.'-'.$day.' '.$hour.':'.$min.':'.$sec);
	} else {
		return ($year.$month.$day.$hour.$min.$sec);
	}
	
}


// pluralForm | sTransliterator



// echo "В Вашем почтовом ящике $n ".pluralForm($n, "письмо", "письма", "писем");
function pluralForm($n, $form1, $form2, $form5)
{
    $n = abs($n) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) return $form5;
    if ($n1 > 1 && $n1 < 5) return $form2;
    if ($n1 == 1) return $form1;
    return $form5;
}


function sTransliterator($sInput)
{
	$sTmp = $sInput;
	$sResult = '';
	$nStrLen = strlen($sTmp);
	for ($i=0; $i<$nStrLen; $i++) {
		$sSymbol = substr($sTmp, $i, 1);
		switch ($sSymbol) {
			case 'а':
			case 'А': $sResult .= 'a'; break;
			case 'б':
			case 'Б': $sResult .= 'b'; break;
			case 'в':
			case 'В': $sResult .= 'v'; break;
			case 'г':
			case 'Г': $sResult .= 'g'; break;
			case 'д':
			case 'Д': $sResult .= 'd'; break;
			case 'е':
			case 'Е':
			case 'ё':
			case 'Ё': $sResult .= 'e'; break;
			case 'ж':
			case 'Ж':
			case 'з':
			case 'З': $sResult .= 'z'; break;
			case 'и':
			case 'И':
			case 'ы':
			case 'Ы':
			case 'й':
			case 'Й': $sResult .= 'i'; break;
			case 'к':
			case 'К': $sResult .= 'k'; break;
			case 'л':
			case 'Л': $sResult .= 'l'; break;
			case 'м':
			case 'М': $sResult .= 'm'; break;
			case 'н':
			case 'Н': $sResult .= 'n'; break;
			case 'о':
			case 'О': $sResult .= 'o'; break;
			case 'п':
			case 'П': $sResult .= 'p'; break;
			case 'р':
			case 'Р': $sResult .= 'r'; break;
			case 'с':
			case 'С': $sResult .= 's'; break;
			case 'т':
			case 'Т': $sResult .= 't'; break;
			case 'у':
			case 'У': $sResult .= 'u'; break;
			case 'ф':
			case 'Ф': $sResult .= 'f'; break;
			case 'х':
			case 'Х': $sResult .= 'h'; break;
			case 'ц':
			case 'Ц': $sResult .= 'c'; break;
			case 'ч':
			case 'Ч': $sResult .= 'ch'; break;
			case 'ш':
			case 'Ш': $sResult .= 'sh'; break;
			case 'щ':
			case 'Щ': $sResult .= 'sch'; break;
			case 'ъ':
			case 'Ъ':
			case 'ь':
			case 'Ь': break;
			case 'э':
			case 'Э': $sResult .= 'e'; break;
			case 'ю':
			case 'Ю': $sResult .= 'ju'; break;
			case 'я':
			case 'Я': $sResult .= 'ja'; break;
			case '1': $sResult .= '1'; break;
			case '2': $sResult .= '2'; break;
			case '3': $sResult .= '3'; break;
			case '4': $sResult .= '4'; break;
			case '5': $sResult .= '5'; break;
			case '6': $sResult .= '6'; break;
			case '7': $sResult .= '7'; break;
			case '8': $sResult .= '8'; break;
			case '9': $sResult .= '9'; break;
			case '0': $sResult .= '0'; break;
			case 'a':
			case 'A': $sResult .= 'a'; break;
			case 'b':
			case 'B': $sResult .= 'b'; break;
			case 'c':
			case 'C': $sResult .= 'c'; break;
			case 'd':
			case 'D': $sResult .= 'd'; break;
			case 'e':
			case 'E': $sResult .= 'e'; break;
			case 'f':
			case 'F': $sResult .= 'f'; break;
			case 'g':
			case 'G': $sResult .= 'g'; break;
			case 'h':
			case 'H': $sResult .= 'h'; break;
			case 'i':
			case 'I': $sResult .= 'i'; break;
			case 'j':
			case 'J': $sResult .= 'j'; break;
			case 'k':
			case 'K': $sResult .= 'k'; break;
			case 'l':
			case 'L': $sResult .= 'l'; break;
			case 'm':
			case 'M': $sResult .= 'm'; break;
			case 'n':
			case 'N': $sResult .= 'n'; break;
			case 'o':
			case 'O': $sResult .= 'o'; break;
			case 'p':
			case 'P': $sResult .= 'p'; break;
			case 'q':
			case 'Q': $sResult .= 'q'; break;
			case 'r':
			case 'R': $sResult .= 'r'; break;
			case 's':
			case 'S': $sResult .= 's'; break;
			case 't':
			case 'T': $sResult .= 't'; break;
			case 'u':
			case 'U': $sResult .= 'u'; break;
			case 'v':
			case 'V': $sResult .= 'v'; break;
			case 'w':
			case 'W': $sResult .= 'w'; break;
			case 'x':
			case 'X': $sResult .= 'x'; break;
			case 'y':
			case 'Y': $sResult .= 'y'; break;
			case 'z':
			case 'Z': $sResult .= 'z'; break;
			default:
			$sResult .= '_';
			break;
		}
	}
	return $sResult;
}


// tplList | sqlGetInsert | sqlGetSelect | sGetArray | array_serialize | array_fltr


function tplList($aIn, $sName, $type, $aSel=array())
{
	$tplContainer = new KTemplate();	
	switch ($type):
	case 'radio':
		$sTplContainer = '
		<!-- BEGIN block -->
		<input type="radio" name="'.$sName.'" value="{k}" <!-- BEGIN sel --> checked <!-- END sel --> />{v}<br />
		<!-- END block -->
		';
		break;
	case 'listbox':
		$sTplContainer = '
		<select name="'.$sName.'">
		<!-- BEGIN block -->
		<option value="{k}" <!-- BEGIN sel --> selected <!-- END sel --> >{v}</option>
		<!-- END block -->
		</select>
		<br />
		';
		break;
	case 'multiple':
		$sTplContainer = '
		<select multiple size="7" name="'.$sName.'[]">
		<!-- BEGIN block -->
		<option value="{k}" <!-- BEGIN sel --> selected <!-- END sel --> >{v}</option>
		<!-- END block -->
		</select>
		<br />
		Удерживайте клавишу CTRL чтобы указать несколько значений
		<br />
		';
		break;
	case 'checkbox':
		$sTplContainer = '
		<!-- BEGIN block -->
		<input type="checkbox" name="'.$sName.'[]" value="{k}" <!-- BEGIN sel --> checked <!-- END sel --> />{v}<br />
		<!-- END block -->
		';
		break;
	case 'text':
		$sTplContainer = '
		<!-- BEGIN block -->
		<input type="text" name="'.$sName.'" style="width: 90%" <!-- BEGIN sel --> value="{v}" <!-- END sel --> /><br />
		<!-- END block -->
		<br />
		';
		break;
	case 'textarea':
		$sTplContainer = '
		<!-- BEGIN block -->
		<textarea name="'.$sName.'" style="width: 90%" rows="10"><!-- BEGIN sel -->{v}<!-- END sel --></textarea><br />
		<!-- END block -->
		<br />
		';
		break;
	endswitch;
	$tplContainer->loadTemplateContent($sTplContainer);
	
	if ( ($type == 'text') || ($type == 'textarea') ) {
		$tpl = $tplContainer->fetchBlock('block');
		if (!is_array($aSel)) {
			$tplSel = $tpl->fetchBlock('sel');
			$tplSel->assign('v', $aSel);
			$tpl->assign('sel', $tplSel);
		} else {
			$tpl->assign('sel', '');
		}
		$tplContainer->assign('block', $tpl);
		$tpl->reset();
	} else {
		if ($aSel != array()) {
			if (!is_array($aSel)) {
				$aSel = array($aSel);
			}
			$aSel = array_flip($aSel);
			//dbg($aSel);
		}
		$bCheked = true;
		foreach ($aIn as $k=>$v) {
			$tpl = $tplContainer->fetchBlock('block');
			$tpl->assign('k', $k);
			$tpl->assign('v', $v);
			if (isset($aSel[$k])) {
				$tpl->assign('sel', $tpl->fetchBlock('sel'));
			} else {
				$tpl->assign('sel', '');
			}
			$tplContainer->assign('block', $tpl);
			$tpl->reset();
		}
	}
	return $tplContainer;	
}


function sqlGetInsert($sTable, $aIn)
{
	$sFields = $sValues = '';
	$zpt = '';
	$aIn = array_serialize($aIn);
	foreach ($aIn as $k=>$v) {
		$v = mysql_escape_string($v);
		$sFields .= "$zpt `$k`";
		if (strcasecmp($v, 'NOW()') == 0) {
			$sValues .= "$zpt $v";
		} else {
			$sValues .= "$zpt '$v'";
		}
		$zpt = ", \r\n";
	}
	$sql = "INSERT INTO `$sTable` ( $sFields ) VALUES ($sValues) ";
	return $sql;
}


function sqlGetSelect($sTable, $aIn)
{
	$sFields = '';
	$zpt = '';
	foreach ($aIn as $k=>$v) {
		$sFields .= "$zpt `$v`";
		$zpt = ", \r\n";
	}
	$sql = "SELECT $sFields FROM `$sTable` ";
	return $sql;
}

function sqlGetUpdate($sTable, $aIn, $sWhere)
{
	$sql = "UPDATE `$sTable` SET \r\n";
	foreach ($aIn as $k=>$v) {
		if (is_array($v)) {
			$v = serialize($v);
		}
		$v = mysql_escape_string($v);
		if (strcasecmp($v, 'NOW()') == 0) {
			$sql .= "`$k` = $v, \r\n";	
		} else {
			$sql .= "`$k` = '$v', \r\n";
		}
	}
	$sql = substr($sql, 0, strlen($sql)-4);
	$sql .= "\r\nWHERE ".$sWhere; 
	return $sql;
}


function sGetArray($aIn)
{
	$sFields = '';
	$zpt = '';
	//dbg(debug_backtrace());
	foreach ($aIn as $k=>$v) {
		if (is_array($v)) {
			$v = mysql_escape_string(serialize($v));
		}
		$sFields .= "$zpt '$v'";
		$zpt = ", \r\n";
	}
	$sRet = "array(\r\n".$sFields."\r\n );\r\n";
	return $sRet;
}


function array_serialize($aIn)
{
	$aR = array();
	foreach ($aIn as $k=>$v) {
		if (is_array($v)) {
			$v = serialize($v);
		}
		$aR[$k] = $v;
	}
	return $aR;
}


function array_fltr($aIn, $aFields)
{
	//dbg(debug_backtrace());
	$aFltr = array();
	foreach ($aIn as $k=>$v) {
		if (false !== array_search($k, $aFields)) {
			$aFltr[$k] = /*mysql_escape_string*/($v);
		}
	}
	return $aFltr;
}



function my_die($error = '')
{
	if (empty($error)) {
		$error = 'db_error';
	}
	$error .= ': ';
	$bsod = debug_backtrace();
	for($i=count($bsod)-1; $i>=0; $i--) {
		$file = $bsod[$i]['file'];
		$file = substr($file, strrpos($file, '\\')+1);
		$error .= substr($file, 0, count($file)-5);
		switch ($bsod[$i]['function']) {
			case 'include':
				$calltype = 'i';
			case 'io':
				$calltype = 'io';
			case 'r':
				$calltype = 'r';
			case 'ro':
				$calltype = 'ro';
				break;
			default:
				$calltype = $bsod[$i]['function'];
		}
		$error .= '[';
		if ($i != 0) {
			$error .= $calltype.':';
		}
		$error .= $bsod[$i]['line'].']';
		if ($i != 0) {
			$error .= '-';
		}
	}

	$error .= "\r\n".mysql_error();
	
	$subject = $_SERVER['HTTP_HOST'].' '.'error';
	
	$message = $error."\r\n\r\n".my_info();	
	my_mail($message, $subject);
	
	if ((defined('DEBUG')) || (defined('LOCALHOST')) ) {
		echo($error);
		include_once(FLGR_COMMON.'/exit.php');
	} else {
		$die = "Произошла ошибка.<br />";
		$die .= "Администратору сайта выслан e-mail с ее описанием - <br />";
		$die .= "он постарается все исправить в самое ближайшее время.";
		echo($die);
		include_once(FLGR_COMMON.'/exit.php');
	}
	
}

function my_info()
{
	if (!isset($_SERVER['HTTP_REFERER'])) {
		$_SERVER['HTTP_REFERER'] = "";
	}	
	$message = 'Request: "'.$_SERVER['REQUEST_URI'].'"'."\r\n";
	$message .= 'Agent: "'.$_SERVER['HTTP_USER_AGENT'].'"'."\r\n";
	$message .= 'Refferer: "'.$_SERVER['HTTP_REFERER'].'"'."\r\n";
	$message .= 'Host: '.$_SERVER['HTTP_HOST']."\r\n";
	$message .= 'Time: '.date('d.m.Y H:i:s')."\r\n";	
	$message .= 'IP: "'.$_SERVER['REMOTE_ADDR'].'"'."\r\n";
	$message .= 'SESSID: "'.session_id().'"'."\r\n";
	if (isset($_SESSION['user'])) {
		$message .= "\r\n"."User is logged:"."\r\n";
		foreach ($_SESSION['user'] as $k=>$v) {
			$message .= "$k: $v\r\n";
		}
	} else {
		$message .= 'User is not logged'."\r\n";
	}
	return $message;
}


function my_mail($message, $subject = HOST, $to = EMAIL_ADMIN, $from = EMAIL_ADMIN, $headers = "")
{
	$mail = new mime_mail();
	$mail->from = $from;
	$mail->to = $to;
	$mail->subject =  $subject;
	$mail->body = normalize($message);
	//$mail->add_attachment($aMail['file'], $aMail['filename'], $mimetype);
	$mail->send();
	
	/*
	//$headers .= "To: ".$to."\r\n";
	$headers .= "From: ".$from."\r\n";
	if (!@mail($to, $subject, $message, $headers)) {
		//safewrite(FLGR_MESSAGES.'/'.getmicrotime().'.txt', $message);
	}
	*/
}


function sGetQuery($aParam)
{
	$sResult = '';
	foreach ($aParam as $k=>$v) {
		$sResult .= '&'.$k.'='.$v;
	}
	$sResult[0] = '?';
	return $sResult;
}


function getSubVersionsRecursive($nId)
{
	global $aTree;
	
	if (!isset($aTree[$nId])) {
		$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_PAGES."` 
		WHERE (`id` = '$nId')";
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		while ($row = mysql_fetch_assoc($sql)) {
			$aTree[$row['id']] = $row;
		}
	}
	
	$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_PAGES."` 
			WHERE ((`parent` = '$nId') AND (`subversion` = 1)) 
			ORDER BY `order`";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	while ($row = mysql_fetch_assoc($sql)) {
		$aTree[$nId]['childs'][] = $row['id'];
		$aTree[$row['id']] = $row;
		getSubVersionsRecursive($row['id']);
	}
	
}


function isCorrectText($p)
{
	$f = '';
	$etalon_true = '	 \-=[]{}?:;.,"\'%$#@!^&*()_+<>/Р°Р±РІРіРґРµС‘Р¶Р·РёР№РєР»РјРЅРѕРїСЂСЃС‚СѓС„С…С†С‡С€С‰СЉС‹СЊСЌСЋСЏРђР‘Р’Р“Р”Р•РЃР–Р—Р?РљР›РњРќРћРџР РЎРўРЈР¤Р¦Р§РЁР©РЄР«Р¬Р­Р®РЇ1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$aEtalonTrue = array();
	for ($i=0; $i<strlen($etalon_true)-1; $i++) {
		if (!isset($aEtalonTrue[$etalon_true[$i]])) {
			$aEtalonTrue[$etalon_true[$i]] = '';
		}
	}
	for ($i=0; $i<strlen($p)-1; $i++) {
		//dbg($p[$i]);
		if (isset($aEtalonTrue[$p[$i]])) {
			$f .= $p[$i];
		}
	}	
	if ($f == '') {
		return true;
	} else {
		//dbg(array($p, $f));
		return false;
	}	
}


function utf_to_cp1251($p) {
	if (false == @iconv('UTF-8', 'CP1251', $p)) {
		return $p;
	}
	return iconv('UTF-8', 'CP1251', $p);
}


function getmicrotime() {list($usec,$sec) = explode(" ",microtime());return ((float)$usec + (float)$sec);};


function isMailCorrect($email)
{
	return preg_match('|([a-z0-9_\.\-]{1,20})@([a-z0-9\.\-]{1,20})\.([a-z]{2,4})|is', $email);
}


// Возвращает false если не получен ответ "200 OK"
function mDownloadRemoteFile($url)
{
	$snoopy = new Snoopy;
	$snoopy->fetch($url);
	if (false === strpos($snoopy->headers[0], '200 OK')) {
		return false;
	}
	return $snoopy->results; 
}


// Возвращает true в случае успеха
// или строку ошибки
function mGetRemoteFile($from, $to)
{
	$from = trim($from);
	$host = parse_url($from);
	$host = $host['host'];
	$ip = gethostbyname($host);
	if ($ip == $host) {
		return('error: gethostbyname');
	}
	
	$f = mDownloadRemoteFile($from);
	if (false === $f) {
		return('error: 404');
	}
	
	safewrite($to, $f);
	
	if (false === saferead($to)) {
		return 'error: file';
	}
	
	return true;
}

function sFilter($sRequest)
{
	/*** TODO ***/
	$sChars = '#$%&"\'\<>`';
	$nChars = strlen($sChars);
	$bFlagAttack = false;
	$nRequest = strlen($sRequest);
	$sResult = '';
	for ($r=0; $r<$nRequest; $r++) {
		for ($c=0; $c<$nChars; $c++) {
			if ($sRequest[$r] == $sChars[$c]) {
				$bFlagAttack = true;
				break;
			}
		}
		if ($bFlagAttack) {
			$bFlagAttack = false;
		} else {
			$sResult .= $sRequest[$r];
		}
	}
	return $sResult;
}


?>