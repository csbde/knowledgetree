<?php
/**
* Scheduler Service Validation.
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
class schedulerValidation extends serviceValidation {
	/**
	* Path to php executable
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
    protected $php;
    
	/**
	* Flag if php already provided
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    public $providedPhp = false;
    
	/**
	* PHP Installed 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $phpCheck = 'tick';

	/**
	* Flag, if php is specified and an error has been encountered
	*
	* @author KnowledgeTree Team
	* @access public
	* @var boolean
	*/
    private $phpExeError = false;
    
	/**
	* Holds path error, if php is specified
	*
	* @author KnowledgeTree Team
	* @access public
	* @var string
	*/
    private $phpExeMessage = '';
    
    public function getBinary() {
		$this->php = $this->util->getPhp();
    }
    
    public function binaryChecks() {
    	// TODO: Better detection
    	$phpDir = $this->util->useZendPhp();
    	if(WINDOWS_OS) {
    		$phpPath = "$phpDir"."php.exe";
    	} else {
    		$phpPath = "$phpDir"."php";
    	}
    	if(file_exists($phpPath)) {
    		return $phpPath;
    	}
    }
    
    function detPhpSettings() {
    	// TODO: Better php handling
    	return true;
    	$phpExecutable = $this->util->phpSpecified();// Retrieve java bin
    	$cmd = "$phpExecutable -version > ".$this->outputDir."/outPHP 2>&1 echo $!";
    	$response = $this->util->pexec($cmd);
    	if(file_exists($this->outputDir.'outPHP')) {
    		$tmp = file_get_contents($this->outputDir.'outPHP');
    		preg_match('/PHP/',$tmp, $matches);
    		if($matches) {
				$this->phpCheck = 'tick';
				
				return true;
    		} else {
    			$this->phpCheck = 'cross_orange';
    			$this->phpExeError = "PHP : Incorrect path specified";
				$this->error[] = "PHP executable required";
				
				return false;
    		}
    	}
    }
    
    /**
	* Set template view to specify php
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function specifyPhp() {
    	$this->phpExeError = true;
    }
    
    private function setPhp() {
		if($this->php != '') { // PHP Found
			$this->phpCheck = 'tick';
		} elseif ($phpDir = $this->util->useZendPhp()) { // Use System Defined Settings
			$this->php = $phpDir;
		}
		
		$this->temp_variables['php']['location'] = $this->php;
    }
    
	public function getPhpDir() {
		return $this->php;
	}
	
    public function storeSilent() { // TODO : PHP detection
    	$this->temp_variables['schedulerInstalled'] = $this->installed;
		$this->temp_variables['phpCheck'] = $this->phpCheck;
		$this->temp_variables['phpExeError'] = $this->phpExeError;
		
		return $this->temp_variables;
    }
}

?>