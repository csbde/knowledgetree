<?php
/**
* Lucene Service Controller. 
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright(C) 2008,2009 KnowledgeTree Inc.
* Portions copyright The Jam Warehouse Software(Pty) Limited
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

class unixScheduler extends Service {
	public $name;
	public $phpDir;
	protected $schedulerPidFile;
	protected $schedulerDir;
	protected $schedulerSource;
	protected $schedulerSourceLoc;
	private $util = null;
	
	public function __construct() {
	}
	
	function load() {
		$this->name = "KTSchedulerTest";
		$this->util = new InstallUtil();
		$this->setSchedulerDir(SYSTEM_DIR."bin".DS);
		$this->setSchedulerSource('schedulerTask.sh');
		$this->setSchedulerSourceLoc('schedulerTask.sh');
		$this->setSchedulerPidFile("scheduler_test.pid");
	}
	
	private function setSchedulerPidFile($schedulerPidFile) {
		$this->schedulerPidFile = $schedulerPidFile;
	}
	
	private function getSchedulerPidFile() {
		return $this->schedulerPidFile;
	}
	
	function setSchedulerDir($schedulerDir) {
		$this->schedulerDir = $schedulerDir;
	}
	
	function getSchedulerDir() {
		return $this->schedulerDir;
	}
	
	function setSchedulerSource($schedulerSource) {
		$this->schedulerSource = $schedulerSource;
	}
	
	function getSchedulerSource() {
		return $this->schedulerSource;
	}
	
	function setSchedulerSourceLoc($schedulerSourceLoc) {
		$this->schedulerSourceLoc = $this->getSchedulerDir().$schedulerSourceLoc;
	}
	
	function getSchedulerSourceLoc() {
		if(file_exists($this->schedulerSourceLoc)) {
			return $this->schedulerSourceLoc;
		}
//		die('File Expected Error');
		return false;
	}
	
	function install() {
		$source = $this->getSchedulerSourceLoc();
		if($source) {
			$cmd = "nohup ".$source." &> ".SYS_LOG_DIR."dmsctl.log";
	    	$response = $this->util->pexec($cmd);
    		return $response;
		}
		
		return false;
	}
	
	function uninstall() {
		$this->stop();
	}
	
	function stop() {
    	$cmd = "pkill -f ".$this->name;
    	$response = $this->util->pexec($cmd);
		return $response;
	}
	
	function status() {
    	$cmd = "ps ax | grep ".$this->getSchedulerSource()." | awk {'print $1'}";
    	$response = $this->util->pexec($cmd);
    	if(is_array($response['out'])) {
    		if(count($response['out']) > 1) {
    			return 'STARTED';
    		} else {
    			return 'STOPPED';
    		}
    	}
    	
    	return 'STOPPED';
	}
}
?>