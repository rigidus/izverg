<?php

class cBreadCrumbs 
{
	var $aBreadCrumbs = array();
	var $tplBreadCrumbs;
	
	function cBreadCrumbs($tpl = false)
	{
		if ($tpl == false) {
			$this->tplBreadCrumbs = new KTemplate();
			$this->tplBreadCrumbs->loadTemplateContent('
			<!-- BEGIN BreadCrumbs -->
			<a href="{bc_link}">{bc_title}</a> /
			<!-- END BreadCrumbs -->
			');
		} else {
			$this->tplBreadCrumbs = $tpl;
		}
	}
	
	function addBreadCrumbs($key, $title) 
	{
		$this->aBreadCrumbs[] = array('key'=>$key, 'title'=>$title);
	}
	
	function getBreadCrumbs()
	{
		$lnk = '';
		foreach ($this->aBreadCrumbs as $v) {
			$tplBC = $this->tplBreadCrumbs->fetchBlock('BreadCrumbs');
			$tplBC->assign('bc_title', $v['title']);
			if ($lnk == '/') {
				$lnk .= $v['key'];
			} else {
				$lnk .= '/'.$v['key'];
			}
			$tplBC->assign('bc_link', $lnk);
			$this->tplBreadCrumbs->assign('BreadCrumbs', $tplBC);
			$tplBC->reset();
		}
		return $this->tplBreadCrumbs;
	}
}

?>