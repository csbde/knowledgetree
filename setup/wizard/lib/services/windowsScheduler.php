<?php
/**
* Windows Scheduler Service Controller.
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
	* Service name
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/	
	public $name = "KTScheduler";

	public $hrname = "KnowledgeTree Scheduler Service. (KTScheduler)";
	
	public $description = "KnowledgeTree Scheduler Service.";
	
	/**
	* Load defaults needed by service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string
	* @return void
 	*/
	function load() {
		$this->setSchedulerDIR(SYS_VAR_DIR."bin");
		$this->setSchedulerScriptPath("taskrunner.bat");
		$this->setSchedulerSource("schedulerService.php");
		
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
		if(!file_exists($schedulerDIR)) {
			mkdir($schedulerDIR);
		}
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
	* Set Php Script path
	*
	* @author KnowledgeTree Team
	* @access private
	* @param none
	* @return string
 	*/
	private function setSchedulerSource($schedulerSource) {
		$this->schedulerSource = SYS_BIN_DIR."win32".DS.$schedulerSource;
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
	
	private function setWinservice($winservice = "winserv.exe") {
		$this->winservice = SYS_BIN_DIR .  "win32" . DS . $winservice;
	}
	
	private function setOptions() {
		$this->options = "-displayname \"{$this->name}\" -description \"{$this->description}\" -start auto -binary \"{$this->getSchedulerScriptPath()}\" -headless -invisible ";
	}
	
	private function writeTaskRunner() {
		if(DEBUG) { // Check if bin is readable and writable
			echo "Attempt to Create {$this->getSchedulerDir()}\\taskrunner.bat<br>";
		}
		$taskrunner = SYS_VAR_DIR."bin".DS."taskrunner.bat";
		$fp = fopen($taskrunner, "w+");
		$content = "@echo off \n";
		$content .= "\"".$this->util->useZendPhp()."php.exe\" "."\"{$this->getSchedulerSource()}\"";
		fwrite($fp, $content);
		fclose($fp);
	}
	
	private function writeSchedulerInstall($cmd) {
		$schedulerInstallFile = SYS_VAR_DIR."bin".DS."schedulerinstall.bat";
		$fp = fopen($schedulerInstallFile, "w+");
		fwrite($fp, $cmd);
		fclose($fp);
	}
	
	/**
	* Retrieve Status Service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/
	public function status() {
		$cmd = "sc query {$this->name}";
		$response = $this->util->pexec($cmd);
		if($response['out']) {
			$state = preg_replace('/^STATE *\: *\d */', '', trim($response['out'][3])); // Status store in third key
			return $state;
		}
		
		return '';
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
			$this->writeTaskRunner();
        	$this->setWinservice();
        	$this->setOptions();
        	$cmd = "\"{$this->winservice}\" install $this->name $this->options";
            $this->writeSchedulerInstall($cmd);
		}
		return $state;
	}

	/**
	* Start Service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return mixed
 	*/
	public function start() { // User has to manually start the services
		return false;
	}
	
	public function getHRName() {
		return $this->hrname;
	}
	
	public function getStopMsg($installDir) {
		return "";
	}
}
?>