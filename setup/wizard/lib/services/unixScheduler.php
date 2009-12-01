<?php
/**
* Unix Scheduler Service Controller. 
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

class unixScheduler extends unixService {
	private $schedulerDir;
	private $schedulerSource;
	private $schedulerSourceLoc;
	private $systemDir;
	private $scheduler;
	private $phpCli;
	public $name = "KTScheduler";
	public $hrname = "KnowledgeTree Scheduler Service";
	
	/**
	* Load defaults needed by service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string
	* @return void
 	*/
	public function load() {
		$this->setPhpCli();
		$this->scheduler = 'scheduler';
		$this->setSchedulerSource('schedulerTask.sh');
		$this->setSystemDir(SYSTEM_ROOT."bin".DS);
		$this->setSchedulerDir(VAR_BIN_DIR);
		$this->setSchedulerSourceLoc('schedulerTask.sh');
	}

	function setPhpCli() {
		$this->phpCli = $this->util->getPhp();
	}
	
	function setSystemDir($systemDir) {
		$this->systemDir = $systemDir;
	}
	
	function getSystemDir() {
		if(file_exists($this->systemDir))
			return $this->systemDir;
		return false;
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
		if(file_exists($this->schedulerSourceLoc))
			return $this->schedulerSourceLoc;
		return false;
	}
	
	function writeSchedulerTask() {
		$fLoc = $this->getSchedulerDir().$this->getSchedulerSource();
		$fp = fopen($fLoc, "w+");
		$content = "#!/bin/sh\n";
		$content .= "cd ".SYS_BIN_DIR."\n";
		$content .= "while true; do\n";
		// TODO : This will not work without CLI
		$content .= "{$this->phpCli} -Cq scheduler.php\n";
		$content .= "sleep 30\n";
		$content .= "done";
		fwrite($fp, $content);
		fclose($fp);
	}
	
	function install() {
    	$status = $this->status();
    	if($status == '') {
			return $this->start();
    	} else {
    		return $status;
    	}
	}
	
	function uninstall() {
		$this->stop();
	}
	
	/**
	* Stop Service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return array
 	*/
	function stop() {
    	$cmd = "pkill -f ".$this->scheduler;
    	$response = $this->util->pexec($cmd);
		return $response;
	}
	
	function status() {
    	$cmd = "ps ax | grep ".$this->getSchedulerSource();
    	$response = $this->util->pexec($cmd);
      	if(is_array($response['out'])) {
    		if(count($response['out']) > 1) {
    			foreach ($response['out'] as $r) {
    				$matches = false;
    				preg_match('/grep/', $r, $matches); // Ignore grep
    				if(!$matches) {
    					return 'STARTED';
    				}
    			}
    		} else {
    			return '';
    		}
    	}
    	
    	return '';
	}
	
	/**
	* Start Service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return array
 	*/
	function start() {
		// TODO : Write sh on the fly? Not sure the reasoning here
		$source = $this->getSchedulerSourceLoc();
//		$this->writeSchedulerTask();
		$logFile = "/dev/null";
//		@unlink($logFile);
		if($source) { // Source
			$cmd = "nohup ".$source." > ".$logFile." 2>&1 & echo $!";
		} else { // Could be Stack
			$source = SYS_BIN_DIR.$this->schedulerSource;
			$cmd = "nohup ".$source." > ".$logFile." 2>&1 & echo $!";
		}
//    	if(DEBUG) {
//    		echo "$cmd<br/>";
//    		return ;
//    	}
    	//$response = $this->util->pexec($cmd);
    	
//		return $response;
		return false;
	}

	public function getName() {
		return $this->name;
	}
	
	public function getHRName() {
		return $this->hrname;
	}
	
	public function getStopMsg($installDir) {
		return "Service Running";//"Execute from terminal : $installDir/dmsctl.sh stop";
	}
	
}
?>
