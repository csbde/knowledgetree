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
class windowsScheduler extends Service {
	//private $name;
	private $schedulerScriptPath;
	private $schedulerSource;
	private $schedulerOut;
	private $schedulerError;
	private $schedulerDir;
	private $util = null;
	
	public function __construct() {
	}
	
	function load() {
		$this->name = "KTSchedulerTest";
		$this->util = new InstallUtil();
		$this->setSchedulerDIR(SYSTEM_DIR."bin".DS."win32");
		$this->setSchedulerScriptPath("taskrunner_test.bat");
		$this->setSchedulerSource("schedulerService.php");
	}

	private function setSchedulerScriptPath($schedulerScriptPath) {
		$this->schedulerScriptPath = "{$this->getSchedulerDir()}".DS."$schedulerScriptPath";
	}
	
	public function getSchedulerScriptPath() {
		return $this->schedulerScriptPath;
	}
	
	private function setSchedulerDIR($luceneDir) {
		$this->schedulerDir = $luceneDir;
	}
	
	public function getSchedulerDir() {
		return $this->schedulerDir;
	}

	private function setSchedulerSource($schedulerSource) {
		$this->schedulerSource = $this->getSchedulerDir().DS.$schedulerSource;
	}
	
	public function getSchedulerSource() {
		return $this->schedulerSource;
	}
	
	public function start() {
		$cmd = "sc start {$this->name}";
		$response = $this->util->pexec($cmd);
		
		return $response;
	}
	
	public function stop() {
		$cmd = "sc stop {$this->name}";
		$response = $this->util->pexec($cmd);
		
		return $response;
	}
	
	public function install() {
		$pathToSCode = $this->getSchedulerSource();
		$fp = fopen($this->schedulerScriptPath, "w+");
		$content = "@echo off\n";
		$content .= "\"".PHP_DIR."php.exe\" "."\"{$this->schedulerSource}\"";
		fwrite($fp, $content);
		fclose($fp);
		$response = win32_create_service(array(
            'service' => $this->name,
            'display' => $this->name,
            'path' => $this->schedulerScriptPath
            ));
		
		return $response;
	}
	
	public function uninstall() {
		$cmd = "sc delete {$this->name}";
		$response = $this->util->pexec($cmd);
		
		return $response;
	}
	
	public function status() {
		$cmd = "sc query {$this->name}";
		$response = $this->util->pexec($cmd);
		if($response['out']) {
//preg_match('/^STATE *\: *(\d) *(\w+)/', trim($response['out'][3]), $matches);
			$state = preg_replace('/^STATE *\: *\d */', '', trim($response['out'][3])); // Status store in third key
			return $state;
		}
	}
}
?>