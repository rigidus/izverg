<?php

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
	}
		
	function validator_login($value)
	{
		// Не пустой
		$filled = $this->validator_filled($value);
		if (is_array($filled)) return $filled;
		
		// Только английские строчные буквы, цифры, тире и знак подчеркивания
		$s = 'qwertyuiopasdfghjklzxcvbnm1234567890_-';
		$a = array();
		for ($i=0; $i<strlen($s); $i++) {
			$a[$s[$i]] = $s[$i];
		}
		for ($i=0; $i<strlen($value); $i++) {
			if (!isset($a[$value[$i]])) {
				return array('Только английские строчные буквы, цифры, тире и знак подчеркивания!');
			}
		}
		// Незарегистрированный логин
		$sql = "SELECT `login` FROM `".DB_PREFIX.DB_TBL_USERS."`";
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		$aAccounts = array();
		while ($row = mysql_fetch_assoc($sql)) {
			$aAccounts[] = current($row);
		}
		//dbg($aAccounts);
		foreach ($aAccounts as $v) {
			if ($v == $value) {
				return array('Такой логин уже зарегистрирован!');
			}
		}
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
	
	function validator_password($value)
	{
		$name = $this->validator_name($value);
		if (is_array($name)) return $name;
		if ($_POST['password'] != $_POST['password_confirm']) {
			return array('Пароль и подтверждение не совпадают!');
		}
		return true;
	}
	
	function validator_age($value)
	{
		if (empty($value)) return true;
		if (!is_numeric($value)) return array('Число полных лет (в пределах от 4 до <a  target="_blank" href="http://ru.wikipedia.org/wiki/%D0%9A%D0%B0%D0%BB%D1%8C%D0%BC%D0%B0%D0%BD,_%D0%96%D0%B0%D0%BD%D0%BD%D0%B0">122</a>)');
		if (($value < 5) || ($value>122)) return array('Число полных лет (в пределах от 4 до <a  target="_blank" href="http://ru.wikipedia.org/wiki/%D0%9A%D0%B0%D0%BB%D1%8C%D0%BC%D0%B0%D0%BD,_%D0%96%D0%B0%D0%BD%D0%BD%D0%B0">122</a>)');
		return true;
	}
	
	function validator_email($value)
	{
		// Не пустой
		$filled = $this->validator_filled($value);
		if (is_array($filled)) return $filled;
		
		// TODO ***
		// ...
		
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

	case 'register':
			//dbg($metaForm->getFormMeta(), 'input array');
			$aTmp = $metaForm->getFormMeta();
			$aTmp = $aTmp['value'];
			unset($aTmp[$metaFormAction->process()]);	// unser($act)
			unset($aTmp['kcaptcha']);
			unset($aTmp['password_confirm']);
			
			$sql = sqlGetInsert(DB_PREFIX.DB_TBL_USERS, $aTmp);
			$sql = mysql_query($sql);
			if (false == $sql) my_die();
			
			$Permissions->login($aTmp['login'], $aTmp['password']);
			
			cStat::bSaveEvent(EVENT_NEWUSER, $aTmp['login']);
			
			$BreadCrumbs->addBreadCrumbs($sKey, 'Регистрация');
			$tpl = $_t->fetchBlock('ContentBlock');
			$tpl->assign('title', 'Регистрация');
			$tpl->assign('content', 'Спасибо за регистрацию. Вы вошли на сайт.');
			
			$_t->assign('ContentBlock', $tpl);
			$tpl->reset();
		break;
	
	case 'update':
			//dbg($metaForm->getFormMeta(), 'input array');
			$aTmp = $metaForm->getFormMeta();
			$aTmp = $aTmp['value'];
			unset($aTmp[$metaFormAction->process()]);	// unser($act)
			unset($aTmp['kcaptcha']);
			unset($aTmp['password_confirm']);
			
			$sql = sqlGetUpdate(DB_PREFIX.DB_TBL_USERS, $aTmp, "`id` = '".$Permissions->getLoggedUserId()."'");
			$sql = mysql_query($sql);
			if (false == $sql) my_die();
			cStat::bSaveEvent(EVENT_UPDATEUSER, '('.$Permissions->getLoggedUserId().')'.$aTmp['login']);
			
			$Permissions->login($aTmp['login'], $aTmp['password']);
			
			$BreadCrumbs->addBreadCrumbs($sKey, 'Профиль пользователя');
			$tpl = $_t->fetchBlock('ContentBlock');
			$tpl->assign('title', 'Профиль пользователя');
			
			$tpl->assign('content', 'Профиль сохранен.');
			
			$_t->assign('ContentBlock', $tpl);
			$tpl->reset();
			
			
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
			$tplForm->assign('validator_login', '');
			$tplForm->assign('validator_name', '');
			$tplForm->assign('validator_password', '');
			$tplForm->assign('validator_age', '');
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
		
			if ($Permissions->bIsLogged()) {
				
				$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_USERS."` WHERE id=".$Permissions->getLoggedUserId();
				$sql = mysql_query($sql);
				if (false == $sql) my_die();
				$aUser = array();
				while ($row = mysql_fetch_assoc($sql)) {
					$aUser[] = $row;
				}
				//dbg($aUser);
				$_POST = current($aUser);
				$_POST['password_confirm'] = $_POST['password'];
				if (empty($_POST['sex'])) {
					$_POST['sex'] = 'default';
				}
				if ($_POST['age'] == 0) {
					$_POST['age'] = '';
				}
				
				$BreadCrumbs->addBreadCrumbs($sKey, 'Профиль пользователя');
				$tpl->assign('title', 'Профиль пользователя');
				$tplForm->assign('content', '');
				$tplForm->assign('act', 'update');
				$tplForm->assign('submit', 'Сохранить профиль');
				$tplForm->assign('validation_login', '');
				
			} else {
				$_POST['sex'] = 'default';
				
				$BreadCrumbs->addBreadCrumbs($sKey, 'Регистрация аккаунта');
				$tpl->assign('title', 'Регистрация аккаунта');
				$tplForm->assign('content', 'Регистрация на этом сайте даст вам возможность оставлять комментарии в блоге.');
				$tplForm->assign('act', 'register');
				$tplForm->assign('submit', 'Зарегистрироваться');
				$tplForm->assign('validation_login', $tplForm->fetchBlock('validation_login'));
			}
			
			$tplForm->assign('validator_login', 'Только английские строчные буквы, цифры, тире и знак подчеркивания');
			$tplForm->assign('validator_name', 'Любые буквы, цифры, тире и знак подчеркивания');
			$tplForm->assign('validator_password', 'Любые буквы, цифры, тире и знак подчеркивания');
			$tplForm->assign('validator_age', 'Число полных лет (в пределах от 4 до <a  target="_blank" href="http://ru.wikipedia.org/wiki/%D0%9A%D0%B0%D0%BB%D1%8C%D0%BC%D0%B0%D0%BD,_%D0%96%D0%B0%D0%BD%D0%BD%D0%B0">122</a>)');
			$tplForm->assign('validator_email', 'Правильный email (используется для уведомлений, на сайте не отображается)');
			$tplForm->assign('validator_kcaptcha', 'Защита от автоматических регистраций - введите символы, изображенные на картинке');
			
			$sForm = $tplForm->get();
			$sForm = HTML_FormPersister::ob_formpersisterhandler($sForm);
			$sForm = $metaForm->process($sForm);
			
			$tpl->assign('content', $sForm);
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