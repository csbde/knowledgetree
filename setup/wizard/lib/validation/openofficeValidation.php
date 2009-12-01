<?php
/**
* Open Office Service Validation.
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
class openofficeValidation extends serviceValidation {
	/**
	* Path to open office executable
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
	public $soffice;

	/**
	* Flag if open office already provided
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    public $providedOpenOffice = false;
    
	/**
	* Flag, if open office is specified and an error has been encountered
	*
	* @author KnowledgeTree Team
	* @access public
	* @var boolean
	*/
    private $openOfficeExeError = false;
    
	/**
	* Holds path error, if open office is specified
	*
	* @author KnowledgeTree Team
	* @access public
	* @var string
	*/
    private $openOfficeExeMessage = '';

	/**
	* Open Office Installed 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $openOfficeCheck = 'cross';

	/**
	* Open Office windows locations 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $windowsLocations = array("C:\Program Files\OpenOffice.org 3\program", "C:\OpenOffice.org 3\program");
    
	/**
	* Open Office unix locations 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $unixLocations = array("/usr/bin");

    public function preset($options = null) {
    	$this->specifyOpenOffice();
    }
    
	/**
	* Set template view to specify open office
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function specifyOpenOffice() {
    	$this->openOfficeExeError = true;
    }

	private function openOfficeInstalled() {
    	$this->openOfficeExeError = false;
    }
    
    public function getBinary() {
    	$this->soffice = $this->binaryChecks();
    }
    
    public function binaryChecks() {
    	if($this->util->openOfficeSpecified()) {
    		$this->soffice = $this->util->openOfficeSpecified();
			if(file_exists($this->soffice))
				return $this->soffice;
			else 
				return false;
    	} else {
    		$auto = $this->detectOpenOffice();
    		if($auto) {
    			$this->soffice = $auto;
    			$this->openOfficeExeError = false;
    			return $this->soffice;
    		}
    		return false;
    	}
    }
    
	private function detectOpenOffice() {
		if(WINDOWS_OS) {
			$locations = $this->windowsLocations;
			$bin = "soffice.exe";
		} else {
			$locations = $this->unixLocations;
			$bin = "soffice";
		}
		foreach ($locations as $loc) {
			$pathToBinary = $loc.DS.$bin;
			if(file_exists($pathToBinary)) {
				return $pathToBinary;
			}
		}
		$pathToBinary = $this->useZendOffice(); // Check for openoffice in zend
		if(file_exists($pathToBinary)) {
			return $pathToBinary;
		}
			
		return false;
	}
	
	public function useZendOffice() {
	    if($this->util->installEnvironment() == 'Zend') {
	    	if(WINDOWS_OS) { // For Zend Installation only
				$sysdir = explode(DS, SYSTEM_DIR);
				array_pop($sysdir);
				array_pop($sysdir);
				$zendsys = '';
				foreach ($sysdir as $k=>$v) {
					$zendsys .= $v.DS;
				}
				$soffice = $zendsys."openoffice".DS."program".DS."soffice.exe";
				if(file_exists($soffice))
					return $soffice;
	    	}
	    }
	    
	    return false;
	}
	
   	/**
    * Set all silent mode varibles
    *
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
    */
    public function storeSilent() {
    	$this->temp_variables['openOfficeInstalled'] = $this->installed;
		$this->temp_variables['openOfficeExe'] = $this->soffice;
		$this->temp_variables['openOfficeExeError'] = $this->openOfficeExeError;
		$this->temp_variables['openOfficeExeMessage'] = $this->openOfficeExeMessage;
		return $this->temp_variables;
    }
}

?>