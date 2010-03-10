<?php

class cStat {
	
	function bSaveStat()
	{
		$ip = ip2int($_SERVER['REMOTE_ADDR']);
		$to = unslashify($_SERVER['REQUEST_URI']);
		$from = @unslashify($_SERVER['HTTP_REFERER']);
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$sessid = session_id();
		$sql = "INSERT INTO `".DB_PREFIX.DB_TBL_STAT."` ( `id` , `t` , `ip` , `to` , `from` , `agent` , `sessid` ) VALUES ( '', NOW( ) , '$ip', '$to', '$from', '$agent', '$sessid');";
		$sql = mysql_query($sql);
		if ($sql === false)	return false;
		return true;
	}
	
	function bSaveEvent($type, $data='')
	{
		$type = $type;
		$data = mysql_escape_string($data);
		$request = $_SERVER['REQUEST_URI'];
		$refferer = @$_SERVER['HTTP_REFERER'];
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$ip = ip2int($_SERVER['REMOTE_ADDR']);
		$sessid = session_id();
		$userid = @$_SESSION['user']['id'];
		$sql = "INSERT INTO `".DB_PREFIX.DB_TBL_EVENTS."` ( `id` , `type` , `request` , `refferer` , `agent` , `ip` , `sessid`, `userid`, `t`, `data` ) VALUES ( '', '$type' , '$request', '$refferer', '$agent', '$ip', '$sessid', '$userid', NOW( ), '$data');";
		$sql = mysql_query($sql);
		if ($sql === false)	return false;
		return true;
	}
	
}


?>