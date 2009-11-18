<?php
/**
* Template Engine.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
* 
*
* This program is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License version 3 as published by the
* Free Software Foundation.
*
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
* details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
* California 94120-7775, or email info@knowledgetree.com.
*
* The interactive user interfaces in modified source and object code versions
* of this program must display Appropriate Legal Notices, as required under
* Section 5 of the GNU General Public License version 3.
*
* In accordance with Section 7(b) of the GNU General Public License version 3,
* these Appropriate Legal Notices must retain the display of the "Powered by
* KnowledgeTree" logo and retain the original copyright notice. If the display of the
* logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
* must display the words "Powered by KnowledgeTree" and retain the original
* copyright notice.
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Installer
* @version Version 0.1
*/

class Template
{
	/**
	* Hold all the variables that are going to be imported into the template file
	* @var array
	*/
    var $template_vars = Array();


    /**
	* Constructor
	*
	* @author KnowledgeTree Team
	* @param string $file the file name you want to load
	* @access public
	* @return void
	*/
    public function Template($file = null)
	{
        $this->file = $file;
    }


	/**
	* Set a variable into the template
	* If the variable is a template object, go and call its template::fetch() method
	*
	* @author KnowledgeTree Team
	* @param string $name The name for this value in the template file
	* @param string $value The value to show in the template file
	* @access public
	* @return void
	*/
    public function set($name, $value)
	{
		//if(is_a($value, 'Template')) {
		$class = 'Template';
		$isA = $value instanceof $class;
		if($isA) {
			$value = $value->fetch();
		}
		$this->template_vars[$name] = $value;
    }


	/**
	* Create the template and import its variables
	*
	* @author KnowledgeTree Team
	* @param string $file The file to use as the template
	* @access public
	* @return string The parsed template
	*/
    public function fetch($file = null)
	{
        if (is_null($file)) $file = $this->file;

        $file = WIZARD_DIR . $file;
		if (!file_exists($file)) {
			trigger_error('Template file '.$file.' does not exist ', E_USER_ERROR);
		}
		$this->template_vars['html'] = new htmlHelper();
        extract($this->template_vars); // Extract the vars to local namespace
        ob_start();
		include($file);
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

}
?>