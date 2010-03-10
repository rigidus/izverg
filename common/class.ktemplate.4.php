<?php

define("TEMPLATE_ERR_FILE","Could not load template file.");

//Modifications by CG (cg@cg.net.ru)

/**
 * KTemplate (former Apolda Simple Template) class - KTemplate.
 * This file is part of kuerbis.org web tool suite. 
 *
 * The complete kuerbis.org classes are distributed under the 
 * GNU Lesser General Public License.
 * See the lesser.txt file for details.
 * 
 * @package   KTemplate
 * @author    Ralf Geschke <ralf@kuerbis.org>
 * @copyright 2002-2003 by Ralf Geschke
 * @version   $Id: class_ktemplate.inc.php,v 1.8 2003/05/18 16:09:05 geschke Exp $
 * @access    public
 */
class KTemplate
{
    
    var $delimiterStart = "{";
    var $delimiterEnd = "}";
    
    /**
     * Template text 
     *
     * @var      string
     */
    var $t;

    /**
     * This array contains the assigned strings or objects.
     * @var      array
     */
    var $pl;

    /**
     * Array of template objects and their contents.
     *
     * @var     array
     */
    var $bl;

    /**
     * Name of template file
     * @var     string
     */
    var $templatefile;

    /**
     * Name of class, taken from get_class() function.
     *
     * @var    string
     */
    var $className;

    /**
     * Placeholder for optional parameters.
     *
     * @var     array
     */
    var $params;

    /**
     * Constructor function. 
     * If a template filename is submitted, this function will
     * initialize the template object tree.
     *
     * @param    string $filename  Name of template file.
     * @access   public
     * @return   void
     */
    function KTemplate($filename = "",$params=null)
	{
	    /* todo: 
	       - remove setting error messages from constructor 
	       ( to a base class ? )
	    */
	    $this->className = get_class($this);
	    $this->params = $params;
	    $this->loadTemplateFile($filename);
	}
    
    /**
     * Set start delimiter
     * Call this function if you wish to change the default start
     * delimiter '{' to another character.
     * 
     * @param    string $delim
     * @return   void
     */
    function setStartDelim($delim="{") 
	{
	    $this->delimiterStart = $delim;
        }
    
    /**
     * Set end delimiter
     * Call this function if you wish to change the default end
     * delimiter '}' to another character.
     *
     * @param    string $delim
     * @return   void
     */
    function setEndDelim($delim="}") 
	{
	    $this->delimiterEnd = $delim;
        }

    /**
     * Checks existence of a template variable. 
     * 
     * @param    string $varname
     * @return   boolean
     */
    function isAssigned($varname) 
	{
	    return isset( $this->pl[$varname] );
	}
    
    /**
     * Load and initialize template file.
     * This is only useful if it is not possible to 
     * set a template filename by creating an instance of
     * the template class.
     * 
     * @param    string $filename  Name of template file.
     * @access   public
     * @return   void
     */
    function loadTemplateFile($filename = "")
	{
	    if (!$filename)
		return false;
	    if ($filename)
		$this->templatefile = $filename;
	    if (!$fp = fopen($this->templatefile,'r'))
	    {
			echo(TEMPLATE_ERR_FILE);
			include(FLGR_COMMON.'/exit.php');
	    }
			if(filesize($this->templatefile)>0) {
				$this->t = fread($fp,filesize($this->templatefile));
			} else {
				$this->t="";
			}
	    fclose($fp);
	    $this->_initTemplate();
	}
    
    /**
     * Submit a string variable as template content.
     * This is useful if your template doesn't exist as file,
     * e.g. if it is saved in a database.
     * 
     * @param    string $templatestring
     * @access   public
     * @return   void
     */
    function loadTemplateContent($templatestring="")
	{
	    $this->t = $templatestring;
	    $this->_initTemplate();
	}
    
    /**
     * Parse the template.
     * This function creates the template object tree and replaces contents
     * of blocks with simple placeholders. 
     * 
     * @access   private
     * @return   void
     */
    function _initTemplate()
	{
	    preg_match_all("/<!--\s+BEGIN\s+(.*)?\s+-->(.*)<!--\s+END\s+(\\1)\s+-->/ms",$this->t,$ma);
	    for ($i = 0; $i < count($ma[0]); $i++)
	    {
		$search = "/\s*\n*<!--\s+BEGIN\s+(" . $ma[1][$i] . ")?\s+-->(.*)<!--\s+END\s+(" . $ma[1][$i]. ")\s+-->\s*\n*/ms";
		$replace = $this->delimiterStart . $ma[1][$i] . $this->delimiterEnd;
		
		$this->bl[$ma[1][$i]] =& new $this->className('',$this->params);
		$this->bl[$ma[1][$i]]->loadTemplateContent($ma[2][$i]);
		$this->t = preg_replace($search,$replace,$this->t);
		
		//CG
		//$this->pl[$ma[1][$i]]="";
	    }
	}
    
    /**
     * Fetch a block out of the template. 
     * If the block exists, this function returns a Template object,
     * otherwise nothing (false).
     * When parsing the template, the blocks will removed
     * into Template objects and replaced with placeholders. 
     * The name of the placeholder is identical to the name 
     * of the removed block.
     * 
     * @param    string $blockName
     * @access   public
     * @return   object Template or boolean false
     */
    function fetchBlock($blockName)
	{
	    if (isset($this->bl[$blockName]))
		return $this->bl[$blockName];
	    else
		return false;
	}
    
    /**
     * Assign value to an existing placeholder. 
     * If this function is called multiple, the contents
     * will be added. 
     * 
     * The parameter $varName can be a string, an associative 
     * array or a Template object. 
     * 
     * @param    mixed $varName
     *           Allowed types:    Requirements:
     *           string            $varValue            
     *           array             Array format: 
     *                             array ("name_of_placeholder" => Value,
     *                                    ... )
     *           object            Template object or any object which
     *                             returns HTML code via get() method.
     *
     * @param    string $varValue (optional)
     * @access   public
     */
    function assign($varName,$varValue=false)
	{
	    if (is_array($varName))
	    {
		foreach ($varName as $key => $value)
		    {
			$this->pl[$key][] = $value;
		    }
	    }
	    else
	    {
		$this->pl[$varName][] = $varValue;
	    }
	}
    
    /**
     * Delete the contents of submitted variables.
     * 
     * @param    none
     * @access   public
     */
    function reset()
	{
	    unset($this->pl);
	}
    
    /**
     * Print a template with all replacements done.
     * 
     * @param    none
     * @access   public
     */
    function out()
	{
	    print $this->get();
	}
    
    /**
     * Returns a template with all replacements done. 
     *
     * This new function works without destruction of the
     * template string. 
     * It needs some testing, especially due to performance reasons.
     * 
     * @param    none
     * @access   public
     * @return   string parsed template content
     */
function get($clean=false) {
	$t = $this->t;
	if (isset($this->pl) && is_array($this->pl)) {
		foreach ($this->pl as $key => $value) {
			$search = $this->delimiterStart . $key . $this->delimiterEnd;
			$replaceText = "";
			for ($i = 0; $i < count($this->pl[$key]); $i++) {
				//dbg(debug_backtrace());
				//dbg($key);
				//dbg($i);
				if (is_object($this->pl[$key][$i])) {
					$replaceText .= $this->pl[$key][$i]->get();
				} else {
					$replaceText .= $this->pl[$key][$i];
				}
			}//for
			$t = str_replace($search,$replaceText,$t);
		}//foreach
	}//if
	return $t;
}
	
	function showlabel($id,$warn=false) {
		$tmp = $this->fetchBlock($id);
		if($tmp!==false) {
			$this->assign($id,$tmp);
			return true;
		} else {
			if($warn) {
				echo("Label /".$id."/ not found!");
			}
			return false;
		}
	}
	
}//class

?>