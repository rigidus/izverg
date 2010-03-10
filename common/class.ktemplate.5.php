<?php

/**
 * KTemplateException - a (not very) customized exception handler
 * class for KTemplate.
 * This file is part of kuerbis.org web tool suite. 
 *
 * The complete kuerbis.org classes are distributed under the 
 * GNU Lesser General Public License.
 * See the lesser.txt file for details.
 * 
 * @package   KTemplate
 * @author    Ralf Geschke <ralf@kuerbis.org>
 * @copyright 2005 by Ralf Geschke
 * @access    public
 */
class KTemplateException extends Exception
{
}

/**
 * KTemplate - a simple, object-based Template class.
 * This file is part of kuerbis.org web tool suite. 
 *
 * The complete kuerbis.org classes are distributed under the 
 * GNU Lesser General Public License.
 * See the lesser.txt file for details.
 * 
 * @package   KTemplate
 * @author    Ralf Geschke <ralf@kuerbis.org>
 * @copyright 2002-2005 by Ralf Geschke
 * @access    public
 */
class KTemplate
{
    
    protected $_delimiterStart = "{";
    protected $_delimiterEnd = "}";
 
    const KTEMPLATE_ERR_FILE = 'Could not load template file';
   
    /**
     * Template text 
     *
     * @var      string
     */
    protected $_t;
    
    /**
     * This array contains the assigned strings or objects.
     * @var      array
     */
    protected $_pl;
        
    /**
     * Array of template objects and their contents.
     *
     * @var     array
     */
    protected $_bl;
    
    /**
     * Name of template file
     * @var     string
     */
    protected $_templatefile;
    
    /**
     * Name of class, taken from get_class() function.
     *
     * @var    string
     */
    protected $_className;
    
    /**
     * Placeholder for optional parameters.
     *
     * @var     array
     */
    public $_params;
    
    /**
     * Constructor function. 
     * If a template filename is submitted, this function will
     * initialize the template object tree.
     * This function only does exist for backward compatibility.
     *
     * @param    string $filename  Name of template file.
     * @access   public
     * @return   void
     */
    public function __construct($filename='', $params=null)
    {
        /* todo: 
         - remove setting error messages from constructor 
         ( to a base class ? )
        */
        $this->_className = get_class($this);
        $this->_params = $params;
        $this->loadTemplateFile($filename);
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
    public function __set($varName,$varValue=false)
    {
        if (is_array($varName))
        {
            foreach ($varName as $key => $value)
            {
                if (is_object($value))
                    $this->_pl[$key][] = clone $value;
                else
                    $this->_pl[$key][] = $value;
            }
        }
        else
        {
            if (is_object($varValue))
                $this->_pl[$varName][] = clone $varValue;
            else
                $this->_pl[$varName][] = $varValue;
        }
    }
    
    
	public function assign($varName,$varValue=false)
    {
    	if (is_array($varName))
        {
            foreach ($varName as $key => $value)
            {
                if (is_object($value))
                    $this->_pl[$key][] = clone $value;
                else
                    $this->_pl[$key][] = $value;
            }
        }
        else
        {
            if (is_object($varValue))
                $this->_pl[$varName][] = clone $varValue;
            else
                $this->_pl[$varName][] = $varValue;
        }
    }
    
    public function fetchBlock($value)
    {
    	if (isset($this->_bl[$value]))
            return $this->_bl[$value];
        else
         	return false;
    }
    
    /**
     * Fetch a block out of the template. 
     * This method was formerly known as fetchBlock().
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
    public function __get($value)
    {
        if (isset($this->_bl[$value]))
            return $this->_bl[$value];
        else
            return false;
    }
    
    /**
     * Set start delimiter
     * Call this function if you wish to change the default start
     * delimiter '{' to another character.
     * 
     * @param    string $delim
     * @return   void
     */
    public function setStartDelim($delim="{") 
    {
        $this->_delimiterStart = $delim;
    }
    
    /**
     * Set end delimiter
     * Call this function if you wish to change the default end
     * delimiter '}' to another character.
     *
     * @param    string $delim
     * @return   void
     */
    public function setEndDelim($delim="}") 
    {
        $this->_delimiterEnd = $delim;
    }
    
    /**
     * Checks existence of a template variable. 
     * 
     * @param    string $varname
     * @return   boolean
     */
    public function isAssigned($varname) 
    {
        return isset( $this->_pl[$varname] );
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
    public function loadTemplateFile($filename = "")
    {
        if (!$filename)
            return false;
        if ($filename)
            $this->_templatefile = $filename;
        if (!$fp = @fopen($this->_templatefile,'r'))
        {
            throw new KTemplateException(self::KTEMPLATE_ERR_FILE);
        }
        $this->_t = fread($fp,filesize($this->_templatefile));
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
    public function loadTemplateContent($templatestring="")
    {
        $this->_t = $templatestring;
        $this->_initTemplate();
    }
    
    /**
     * Parse the template.
     * This function creates the template object tree and replaces contents
     * of blocks with simple placeholders. 
     * 
     * @return   void
     */
    protected function _initTemplate()
    {
        preg_match_all("/<!--\s+BEGIN\s+(.*)?\s+-->(.*)<!--\s+END\s+(\\1)\s+-->/ms",$this->_t,$ma);
        for ($i = 0; $i < count($ma[0]); $i++)
        {
            $search = "/\s*\n*<!--\s+BEGIN\s+(" . $ma[1][$i] . ")?\s+-->(.*)<!--\s+END\s+(" . $ma[1][$i]. ")\s+-->\s*\n*/ms";
            $replace = $this->_delimiterStart . $ma[1][$i] . $this->_delimiterEnd;
            
            $this->_bl[$ma[1][$i]] = new $this->_className('',$this->_params);
            $this->_bl[$ma[1][$i]]->loadTemplateContent($ma[2][$i]);
            $this->_t = preg_replace($search,$replace,$this->_t);
        }
    }
    
    /**
     * Delete the contents of submitted variables.
     * 
     * @param    none
     * @access   public
     */
    public function reset()
    {
        //unset($this->_pl);
        $this->_pl = array();
    }
    
    /**
     * Print a template with all replacements done.
     * 
     * @param    none
     * @access   public
     */
    public function out()
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
    public function get()
    {
        $t = $this->_t;
        if (isset($this->_pl) && is_array($this->_pl))
        {
            foreach ($this->_pl as $key => $value)
            {
                $search = $this->_delimiterStart . $key . $this->_delimiterEnd;
                $replaceText = "";
                for ($i = 0; $i < count($this->_pl[$key]); $i++)
                {
                    if (is_object($this->_pl[$key][$i]))
                        $replaceText .= $this->_pl[$key][$i]->get();
                    else
                        $replaceText .= $this->_pl[$key][$i];
                }
                $t = str_replace($search,$replaceText,$t);
            }
        }
        return $t;
    }
        
}



/**
 * KTemplateExt - an extension for KTemplate
 * This file is part of kuerbis.org web tool suite. 
 *
 * The complete kuerbis.org classes are distributed under the 
 * GNU Lesser General Public License.
 * See the lesser.txt file for details.
 * 
 * @package   KTemplate
 * @author    Ralf Geschke <ralf@kuerbis.org>
 * @copyright 2003-2005 by Ralf Geschke
 * @access    public
 */
class KTemplateExt extends KTemplate
{
    /**
     * Controls the handling of empty blocks, default is to preserve the 
     * placeholders.
     * 
     * @var    boolean
     * @access protected
     */
    protected $_removeEmptyBlocks;
    
    protected $_blc;
    
    public function __construct($filename='',$params=null)
    {
        parent::__construct($filename,$params);
        $this->_removeEmptyBlocks = false;
        $this->_parseParams();
    }
    
    /**
     * Parse the template.
     * This function creates the template object tree and replaces contents
     * of blocks with simple placeholders. 
     * 
     * @see     KTemplate::_initTemplate()
     */
    protected function _initTemplate()
    {
        parent::_initTemplate();
        if (isset($this->_bl) && is_array($this->_bl))
        {
            foreach ($this->_bl as $key => $value)
            {
                $this->_blc[$key] = true;
            }
        }
    }
    
    
    /**
     * Parse the optional params array.
     * 
     * @param    none
     * @return   nothing
     * @access   private
     */
    protected function _parseParams()
    {
        $classVars = get_object_vars($this);
        if (isset($this->_params) && is_array($this->_params))
        {
            foreach ($this->_params as $name => $value)
            {
                if (array_key_exists($name,$classVars))
                    $this->$name = $value;
            }
            
        }
    }
    
    /**
     * Delete the contents of submitted variables.
     * 
     * @param    none
     * @access   public
     */
    public function reset()
    {
        parent::reset();
        if (isset($this->_blc) && is_array($this->_blc))
        {
            reset($this->_blc);
            while (list($key,$value) = each($this->_blc))
            {
                $this->_blc[$key] = true;
            }
        }
    }
    
    
    /**
     * Assign value to an existing placeholder. 
     * If this function is called multiple, the contents
     * will be added. 
     * 
     * @see     KTemplate::__set()
     */
    public function __set($varName,$varValue=false)
    {
        parent::__set($varName,$varValue);
        if ($this->_removeEmptyBlocks and !is_array($varName))
            $this->_blc[$varName] = false;
        elseif ($this->_removeEmptyBlocks and is_array($varName))
        {
            foreach ($varName as $key => $value)
            {
                $this->_blc[$key] = false;
            }
        }
    }
    
    /**
     * Returns a template string with all replacements done. 
     * 
     * This new function works without destruction of the
     * template string. 
     * It needs some testing, especially due to performance reasons.
     *
     * @see     KTemplate::get()
     */
    public function get()
    {
        $t = parent::get();
        if ($this->_removeEmptyBlocks && isset($this->_blc) && is_array($this->_blc))
        {
            foreach ($this->_blc as $key => $value)
            {
                if ($this->_blc[$key] == true)
                {
                    $t = str_replace($this->_delimiterStart . $key . $this->_delimiterEnd,'',$t);
                }
		
            }
        }
        return $t;
    }
    
}

?>