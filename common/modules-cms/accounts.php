<?php

// BREADCRUMBS

$BreadCrumbs->addBreadCrumbs($sKey, $sTitle);

if (!$bFlagLastModule) return;

$_t->assign('BreadCrumbs', $BreadCrumbs->getBreadCrumbs());


// TEMPLATE

$tpl = new KTemplate();
$_s = file_get_contents(__FILE__);
$_s = substr($_s, strpos($_s, '?'.'>')+2);
$tpl->loadTemplateContent($_s);

// Left

$tplSub = $tpl->fetchBlock('subfunctions');
$tplSub->assign('subst', $sRequest);
$tpl->assign('subfunctions', $tplSub);


// Right



$sql = "SELECT * FROM `".DB_PREFIX.DB_TBL_USERS."`";
$sql = mysql_query($sql);
if (false == $sql) my_die();
$aAccounts = array();
while ($row = mysql_fetch_assoc($sql)) {
	$aAccounts[] = $row;
}

if (empty($aAccounts)) {
	$tpl->assign('content', 'Нет аккаунтов');
} else {
	$tplContent = $tpl->fetchBlock('content');
	foreach ($aAccounts as $k => $v) {
		$tplAccount = $tplContent->fetchBlock('account');
		$tplAccount->assign($v);
		$tplAccount->assign('edit', $aCmsModules['account-edit']['key']);
		$tplAccount->assign('del', $aCmsModules['account-del']['key']);
		$tplContent->assign('account', $tplAccount);
		$tplAccount->reset();
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
		<!-- BEGIN subfunctions -->
		<div style="margin-bottom: 5px;">
			<a href="{subst}/account-add">Создать&nbsp;аккаунт</a>
		</div>
		<!-- END subfunctions -->
	</td>
	<td><img src="/img/spacer.gif" width="50" /></td>
	<td style="border-left: 1px solid red"><img src="/img/spacer.gif" width="20" /></td>
	<td>
		<!-- BEGIN content -->
		<table cellpadding="2">
		<thead>
			<th>&nbsp;</th>
			<th>Логин</th>
			<th>Имя</th>
			<th>Емайл</th>
			<th>Телефон</th>
		</thead>
		<!-- BEGIN account -->
		<tr>
			<td>
				<a href="{edit}/{id}" style="text-decoration: none;"><img src="/img/edit.gif" border="0" />
				<a href="{del}/{id}" style="text-decoration: none;"><img src="/img/del.gif" border="0" />
			</td>
			<td class="border">&nbsp;{login}</td>
			<td class="border">&nbsp;{name}</td>
			<td class="border">&nbsp;{email}</td>
			<td class="border">&nbsp;{telephone}</td>
		</tr>
		<!-- END account -->
		</table>
		<!-- END content -->
	</td>
</tr>
</table>

</form>