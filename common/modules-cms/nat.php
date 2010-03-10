<?php

// BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

//if (!$bFlagLastModule) return;

$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());


// удаление записи
if (isset($_POST['del_id'])):
	if (is_numeric($_POST['del_id'])) :
	$del_id = $_POST['del_id'];
	// verify
	$sql = 'SELECT * FROM `'.DB_PREFIX.DB_TBL_NAT.'` WHERE id = '.$del_id;
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
	$aDel = mysql_fetch_assoc($sql);
	
	if (empty($aDel)) {
		$_t->assign('content', '<span style="color: red">Ошибка:</span> Удаляемая запись не существует!');
		return;
	}
				$sql = "DELETE FROM `".DB_PREFIX.DB_TBL_NAT."` WHERE `id` = ".$del_id;
				$sql = mysql_query($sql);
				if (false == $sql) my_die();
	endif;
endif;

// правка записи
if (isset($_POST['edit_id'])) :	
	if ($_POST['edit_id'] == 'edit_id') :	
		$sql = "SELECT `id` FROM `".DB_PREFIX.DB_TBL_NAT."`";
		$sql = mysql_query($sql);
		if (false == $sql) my_die();
		while ($aEdit_id = mysql_fetch_assoc($sql))
		{
			$edit_id = $aEdit_id['id'];
			$from = 'from-'.$edit_id;
			$to = 'to-'.$edit_id;
			$from = $_POST[$from];
			$to = $_POST[$to];
			
			$sql_up = "UPDATE `".DB_PREFIX.DB_TBL_NAT."` 
						 SET 
						`id` = '".$edit_id."', 
						`from` = '".$from."', 
						`to` = '".$to."'
						WHERE `id` ='".$edit_id."' LIMIT 1;";
						
			$sql_up = mysql_query($sql_up);
			if (false == $sql_up) my_die();		
		}
	endif;
endif;


// новая запись

if (isset($_POST['nat-add'])) :

	$from = $_POST['lastParent'];
	$to = $_POST['newParent'];

	$sql = "SELECT `id` FROM `".DB_PREFIX.DB_TBL_NAT."`";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();

	$sql = "INSERT INTO `".DB_PREFIX.DB_TBL_NAT."` 
			( `from` , `to`)
			VALUES ('".$from."', '".$to."');";
	$sql = mysql_query($sql);
	if (false == $sql) my_die();
endif;


// TEMPLATE

$tpl = new KTemplate();
$_s = file_get_contents(__FILE__);
$_s = substr($_s, strpos($_s, '?'.'>')+2);
$tpl->loadTemplateContent($_s);


// Left

$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_NAT."`";
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aNat = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aNat[] = $row;
}


if (empty($aNat)) {
	$tpl->assign('content', 'Нет записей');
} else {
	$tplContent = $tpl->fetchBlock('content');
	foreach ($aNat as $k=>$v) {
		$tplNat = $tplContent->fetchBlock('nat');
		$tplNat->assign($v);
		$tplContent->assign('nat', $tplNat);
		$tplNat->reset();
	}
	$tpl->assign('content', $tplContent);
	
}

// OUT

$_t->assign('content', $tpl);


return;
?>

<style type="text/css">
table.border {
	border-top: solid #474234;
	border-right: solid #B69C99;
	border-bottom: solid #B69C99;
	border-left: solid #B69C99;
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
}
td.border {
	border: 1px solid #CCCCCC;
	background-color: #FFFFCC;
}
</style>

<form method="post">

<table cellpadding="0" cellspacing="0">
	<tr>
		<td valign="top">
			<div style="margin-bottom: 5px;">
				<a href="" onclick="javascript: ShowHide('newlink'); return false;">Добавить&nbsp;новую&nbsp;ссылку</a>
			</div>
			<table border="0" cellpadding="2" cellspacing="0" id="newlink" style="display: none; margin-top: 15px;">			
				<tr>
					<td valign="top" width="1"><nobr>Откуда:&nbsp;</nobr></td>
					<td valign="top"><input type="text" name="lastParent" value="" style="width: 150px; border: 1px solid #AAAAAA;" /></td>
				</tr>
				<tr>
					<td valign="top" width="1"><nobr>Куда:&nbsp;</nobr></td>
					<td valign="top"><input type="text" name="newParent" value="" style="width: 150px; border: 1px solid #AAAAAA;" /></td>
				</tr>
				<tr>	
					<td valign="top"><br /><button type="submit" name="nat-add" value="nat-add" style="border: 1px solid #AAAAAA;" />Сохранить</td>
				</tr>
			</table>	
		</td>
		
	</tr>		
	<tr>
		<td>
			<br />
			<div style="border-bottom: 1px solid red"></div>
			<br />
		</td>
	</tr>		
		<td>
			<!-- BEGIN content -->
			<table cellpadding="2">
			<thead>					
				<th>&nbsp;</th>
				<th>Откуда</th>
				<th>Куда</th>
			</thead>
			<!-- BEGIN nat -->
			<tr>			
				<td valign="top"><button type="submit" style="border: 1px solid #FFFFFF; background: #FFFFFF url('/img/del.gif'); width: 20px;" name="del_id" value="{id}">&nbsp;</button></td>					
				<td valign="top">
					<input type="text" name="from-{id}" value="{from}" style="width: 150px; border: 1px solid #AAAAAA;" />
				</td>
				<td valign="top">
					<input type="text" name="to-{id}" value="{to}" style="width: 150px; border: 1px solid #AAAAAA" />
				</td>
			</tr>
			<!-- END nat -->
			</table>
			<!-- END content -->
		</td>
	</tr>
	<tr>
		<td valign="top" align="center"><br /><button type="submit" style="border: 1px solid #AAAAAA;" name="edit_id" value="edit_id" />Обновить&nbsp;все </td>
	</tr>
</table>

</form>