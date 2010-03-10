<?php

// breadcrumbs
$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

if (!$bFlagLastModule) return;

// Интересует путь, который прошли регистрирующиеся пользователи
// Сначала находим все EVENT_NEWUSER (***TODO*** за последнюю неделю)
$sql = "SELECT `agent`, `ip`, `sessid`, `userid`, `t` FROM `".DB_PREFIX.DB_TBL_EVENTS."` WHERE (`type` = '".EVENT_NEWUSER."') ORDER BY `t` DESC";
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aEventsNewUser = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aEventsNewUser[] = $row;
}

// Перебираем
foreach ($aEventsNewUser as $k=>$v) {
	// Выбираем регистрационные данные
	$sql = "SELECT `login`, `password`, `name`, `email`, `text`, `icq`, `site` FROM `".DB_PREFIX.DB_TBL_USERS."` WHERE (`id` = ".($v['userid']).")";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	$aUser = array();
	$aUser = mysql_fetch_assoc($sql);
	$aEventsNewUser[$k]['user'] = $aUser;
	// Выбираем для каждого из них элементы с их SESSID
	$sql = "SELECT `from`, `t`, `to` FROM `".DB_PREFIX.DB_TBL_STAT."` WHERE (`sessid` = '".$v['sessid']."') ORDER BY `t`";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	$aStat = array();
	while ($row = mysql_fetch_assoc($sql)) {
		$aStat[] = $row;
	}
	$aEventsNewUser[$k]['stat'] = $aStat;
}

if (empty($aEventsNewUser)) 
{
	$_t->assign('content', 'Новых пользователей нет');
}


//dbg($aEventsNewUser);
foreach ($aEventsNewUser as $k=>$v) {
	$sLog = '';
	$sLog .= "<br /><b>agent:</b> ".$v['agent'];
	$sLog .= "<br /><b>ip:</b> ".int2ip($v['ip']);
	if (!empty($v['user'])){		
		foreach ($v['user'] as $kk=>$vv) {
			$sLog .= "<br /><b>$kk:</b> ".$vv;
		}
	}	
	$from = '';
	if (!empty($v['stat'])){	
		foreach ($v['stat'] as $kk=>$vv) {
			foreach ($vv as $kkk=>$vvv) {
				if ($kkk == 'from') {
					if ($from == '') {
						$sLog .= "<br /><b>$kkk:</b> ".$vvv;
						$sLog .= '<br />';
						$from = $vvv;
					}
				} else {
					$sLog .= "<br /><b>$kkk:</b> ".$vvv;
				}
			}
		}
	}
	$_t->assign('content', $sLog);	
}

$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());

?>