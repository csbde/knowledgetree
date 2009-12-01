<?php
/**
* Service Validation.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright(C) 2008,2009 KnowledgeTree Inc.
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
class serviceValidation {
	/**
	* List of errors encountered
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array
	*/
    protected $errors = array();
    
	/**
	* List of warnings encountered
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array
	*/
    protected $warnings = array();
    
	/**
	* Reference to utility object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return object
 	*/
	public $util;

	public $outputDir;
	
	public $varDir;

	/**
	* Flag if services are already Installed
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    public $installed = false;
    
	/**
	* List of variables to be loaded to template
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $temp_variables = array();
    
	public function __construct() {
		$this->util = new InstallUtil();
		$this->setSystemDirs();
	}
	
	function setSystemDirs() {
		$conf = $this->util->getDataFromSession('configuration');
		$this->outputDir = $conf['paths']['logDirectory']['path'].DS;
		$this->varDir = $conf['paths']['varDirectory']['path'].DS;
	}
	
    public function installed() {
    	
    }
    
    public function getBinary() {
    	
    }
    
    public function binaryChecks() {
    	
    }
    
    public function preset() {
    	
    }
}
?>