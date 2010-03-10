<?php

class cPermissions
{
  var $aAccounts = array();


  function cPermissions()
  {

  }


  function _getAccounts()
  {
	$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_USERS."`";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	$this->aAccounts = array();
	while ($row = mysql_fetch_assoc($sql)) {
	  $this->aAccounts[] = $row;
	}
  }


  function login($sLogin, $sPassword)
  {
	if (empty($this->aAccounts)) {
	  $this->_getAccounts();
	}
	$bFlagLogin = false;
	foreach ($this->aAccounts as $v) {
	  if ( ($v['login'] == $sLogin) && ($v['password'] == $sPassword) ) {
		$_SESSION['user'] = $v;
		$bFlagLogin = true;
	  }
	}
	return $bFlagLogin;
  }


  function bIsAdmin()
  {
	if (isset($_SESSION['user'])) {
	  if ($_SESSION['user']['id'] == 1) {
		return true;
	  }
	}
	return false;
  }


  function bIsLogged()
  {
	if (isset($_SESSION['user'])) {
	  return true;
	}
  }


  function logout()
  {
	if (isset($_SESSION['user'])) {
	  unset($_SESSION['user']);
	}
  }


  function getLoggedUserId()
  {
	if (!$this->bIsLogged()) {
	  return false;
	} else {
	  return $_SESSION['user']['id'];
	}
  }


  function getUserNameFromId($id)
  {
	if (empty($this->aAccounts)) {
	  $this->_getAccounts();
	}
	foreach ($this->aAccounts as $v) {
	  if ($v['id'] == $id) {
		return $v['name'];
	  }
	}
	return false;
  }

}

?>