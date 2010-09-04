<?php

// ADD_BREADCRUMBS
$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

if (!$bFlagLastModule) return;

include_once(FLGR_COMMON.'/HTML_MetaForm/lib/config.php');
include_once(FLGR_COMMON.'/HTML_MetaForm/HTML_FormPersister/lib/config.php');
include_once('HTML/MetaForm.php');
include_once('HTML/MetaFormAction.php');
include_once('HTML/FormPersister.php'); 


class HTML_MetaFormActionExt extends HTML_MetaFormAction
{
	function validator_filled($value)
	{
		if (empty($value)) return array('Это поле не может быть пустым!');
		return true;
	}
		
	function validator_name($value)
	{
		// Не пустой
		$filled = $this->validator_filled($value);
		if (is_array($filled)) return $filled;
		
		// Только английские строчные буквы, цифры, тире и знак подчеркивания
		$s = 'qwertyuiopasdfghjklzxcvbnm1234567890_-';
		$s .= 'QWERTYUIOPASDFGHJKLZXCVBNM';
		$s .= 'ЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮЁ';
		$s .= 'йцукенгшщзхъфывапролджэячмитьбюё';
		$a = array();
		for ($i=0; $i<strlen($s); $i++) {
			$a[$s[$i]] = $s[$i];
		}
		for ($i=0; $i<strlen($value); $i++) {
			if (!isset($a[$value[$i]])) {
				return array('Любые буквы, цифры, тире и знак подчеркивания!');
			}
		}
		return true;
	}
	
	function validator_email($value)
	{
		// Не пустой
		$filled = $this->validator_filled($value);
		if (is_array($filled)) return $filled;
		
		if(!isMailCorrect($value)) {
			return array('Ошибочный email!');
		}
		
		return true;
	}
	
	function validator_captcha($value)
	{
		// Не пустой
		$filled = $this->validator_filled($value);
		if (is_array($filled)) return $filled;
		
		if ($_SESSION['captcha_keystring'] != $value) {
			return array('Символы не верны!');
		}		
		
		return true;
	}
	
}


// Create new MetaForm object (processor).
$metaForm =& new HTML_MetaForm('secret_digital_signature_YS0lTgit');

// Now process the form (if posted).
$metaFormAction =& new HTML_MetaFormActionExt($metaForm);

//dbg($metaFormAction->process(), 'CASE SELECTOR');

$process = $metaFormAction->process();

// Если передана чужая форма
if (isset($_POST['act'])) {
	$process = 'INIT';
}


switch ($process) :

	case 'send':
			//dbg($metaForm->getFormMeta(), 'input array');
			$aTmp = $metaForm->getFormMeta();
			$aTmp = $aTmp['value'];
			unset($aTmp[$metaFormAction->process()]);	// unser($act)
			unset($aTmp['kcaptcha']);
			
			$to  = EMAIL_CONTACTS;
			$subject = $_SERVER['HTTP_HOST'].' '.'contacts';
			$message = 'Здравствуйте, '.$_POST['name']."\r\n\r\n".$_POST['message'];
			$message .= "\r\n\r\n".my_info();
			$from = $_POST['email'];			
			my_mail(crbr($message), $subject, $to, $from);

			$tpl = $_t->fetchBlock('ContentBlock');
			$tpl->assign('title', $sTitle);
			$tpl->assign('content', 'Ваше сообщение успешно отправлено.<br />');
			$tpl->assign('content', 'Мы ответим вам на указанный e-mail.<br />');
			
			$_t->assign('ContentBlock', $tpl);
			$tpl->reset();
			
			cStat::bSaveEvent(EVENT_SENDMAIL, $_POST['name'].' < '.$_POST['email'].' > '.$_POST['message']);
		break;
	
	default:
			//dbg($metaFormAction->getErrors(), 'Validator error:');
			//dbg($metaForm->getFormMeta(), 'input array');
			
			$tpl = $_t->fetchBlock('ContentBlock');
			$tpl->assign('title', 'Ошибка!');
			$tpl->assign('content', '<span style="color: #FF0000;">Не все поля заполнены правильно. Проверьте правильность заполнения полей!</span><br /><br />');
			
			$tplForm = new KTemplate(FLGR_TEMPLATES.'/'.$sModuleTpl.'.htm');
			
			// Act = имя последнего поля в форме
			$aValues = $metaForm->getFormMeta();
			$aValues = $aValues['value'];
			end($aValues);
			$tplForm->assign('act', key($aValues));
			$tplForm->assign('submit', current($aValues));
			
			// Очищаем places под сообщения валидаторов
			$tplForm->assign('validator_name', '');
			$tplForm->assign('validator_message', '');
			$tplForm->assign('validator_email', '');
			$tplForm->assign('validator_kcaptcha', '');
			
			foreach ($metaFormAction->getErrors() as $v) {
				$tplForm->assign('validator_'.$v['name'], '<span style="color: #FF0000;">'.$v['message'][0].'</span>');
			}
			
			$sForm = $tplForm->get();
			$sForm = HTML_FormPersister::ob_formpersisterhandler($sForm);
			$sForm = $metaForm->process($sForm);
			
			$tpl->assign('content', $sForm);
			$_t->assign('ContentBlock', $tpl);
			$tpl->reset();			
		break;
		
	case 'INIT':
			$tpl = $_t->fetchBlock('ContentBlock');
			$tplForm = new KTemplate(FLGR_TEMPLATES.'/'.$sModuleTpl.'.htm');
			
			$tpl->assign('title', $sTitle);
			$tpl->assign('content', crbr($sText));
			
			$tplForm->assign('validator_name', 'Любые буквы, цифры, тире и знак подчеркивания');
			$tplForm->assign('validator_message', 'Это поле не должно быть пустым');
			$tplForm->assign('validator_email', 'Правильный email (используется для уведомлений, на сайте не отображается)');
			$tplForm->assign('validator_kcaptcha', 'Защита от автоматических регистраций - введите символы, изображенные на картинке');
			
			$sForm = $tplForm->get();
			$sForm = HTML_FormPersister::ob_formpersisterhandler($sForm);
			$sForm = $metaForm->process($sForm);
			
			$tpl->assign('content', $sForm);
			
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
			
			
			$_t->assign('ContentBlock', $tpl);
			$tpl->reset();
		break;
		
endswitch;

// BREADCRUMBS
$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());

// HEAD_TITLE
$_t->assign('head_title', $sTitle);

// SEO
$_t->assign('seo_title', $sSeoTitle);
$_t->assign('seo_keywords', $sSeoKeywords);
$_t->assign('seo_description', $sSeoDescription);

?>