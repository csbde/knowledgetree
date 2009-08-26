<?php
/**
* Windows Scheduler Service Controller.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
* Portions copyright The Jam Warehouse Software (Pty) Limited
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
class windowsScheduler extends windowsService {
	/**
	* Batch Script to execute
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string 
	*/
	private $schedulerScriptPath;
	
	/**
	* Php Script to execute
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string 
	*/
	private $schedulerSource;
	
	/**
	* Scheduler Directory
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string 
	*/
	private $schedulerDir;
	
	/**
	* Load defaults needed by service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string
	* @return void
 	*/
	function load() {
		$this->name = "KTSchedulerTest";
		$this->setSchedulerDIR(SYSTEM_DIR."bin".DS."win32");
//		$this->setSchedulerScriptPath("taskrunner_test.bat");
		$this->setSchedulerScriptPath("taskrunner.bat");
		$this->setSchedulerSource("schedulerService.php");
	}

	/**
	* Set Batch Script path
	*
	* @author KnowledgeTree Team
	* @access private
	* @param string
	* @return void
 	*/
	private function setSchedulerScriptPath($schedulerScriptPath) {
		$this->schedulerScriptPath = "{$this->getSchedulerDir()}".DS."$schedulerScriptPath";
	}
	
	/**
	* Retrieve Batch Script path
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/
	public function getSchedulerScriptPath() {
		if(file_exists($this->schedulerScriptPath))
			return $this->schedulerScriptPath;
		return false;
	}
	
	/**
	* Set Scheduler Directory path
	*
	* @author KnowledgeTree Team
	* @access private
	* @param none
	* @return string
 	*/
	private function setSchedulerDIR($schedulerDIR) {
		$this->schedulerDir = $schedulerDIR;
	}
	
	/**
	* Retrieve Scheduler Directory path
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/
	public function getSchedulerDir() {
		if(file_exists($this->schedulerDir))
			return $this->schedulerDir;
		return false;
	}

	/**
	* Set Php Script path
	*
	* @author KnowledgeTree Team
	* @access private
	* @param none
	* @return string
 	*/
	private function setSchedulerSource($schedulerSource) {
		$this->schedulerSource = $this->getSchedulerDir().DS.$schedulerSource;
	}
	
	/**
	* Retrieve Php Script path
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/
	public function getSchedulerSource() {
		if(file_exists($this->schedulerSource))
			return $this->schedulerSource;
		return false;
	}
	
	/**
	* Install Scheduler Service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return array
 	*/
	public function install() {
		$state = $this->status();
		if($state == '') {
			if(is_readable(SYS_BIN_DIR)) {
				if(!file_exists($this->getSchedulerScriptPath())) {
					$fp = fopen($this->getSchedulerScriptPath(), "w+");
					$content = "@echo off\n";
					$content .= "\"".PHP_DIR."php.exe\" "."\"{$this->getSchedulerSource()}\"";
					fwrite($fp, $content);
					fclose($fp);
				}
			}
			$response = win32_create_service(array(
	            'service' => $this->name,
	            'display' => $this->name,
	            'path' => $this->getSchedulerScriptPath()
	            ));
			return $response;
		}
		return $state;
	}
}
?>