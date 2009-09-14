<?php
/**
* Unix Agent Service Controller. 
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

class unixOpenOffice extends unixService {

	// utility
	public $util;
	// path to office
	private $path;
	// host
	private $host;
	// pid running
	private $pidFile;
	// port to bind to
	private $port;
	// bin folder
	private $bin;
	// office executable
	private $soffice;
	// office log file
	private $log;
	private $options;
	private $office;
	
	public function __construct() {
		$this->name = "KTOpenOffice";
		$this->util = new InstallUtil();
		$this->office = 'openoffice';
	}
	
	public function load() {
		$this->setPort("8100");
		$this->setHost("localhost");
		$this->setLog("openoffice.log");
		$this->setBin("soffice");
		$this->setOption();
	}
	
	private function setPort($port = "8100") {
		$this->port = $port;
	}
	
	public function getPort() {
		return $this->port;
	}
	
	private function setHost($host = "localhost") {
		$this->host = $host;
	}
	
	public function getHost() {
		return $this->host;
	}
	
	private function setLog($log = "openoffice.log") {
		$this->log = $log;
	}
	
	public function getLog() {
		return $this->log;
	}
	
	private function setBin($bin = "soffice") {
		$this->bin = $bin;
	}
	
	public function getBin() {
		return $this->bin;
	}
	
	private function setOption() {
		$this->options = "-nofirststartwizard -nologo -headless -accept=\"socket,host={$this->getHost()},port={$this->getPort()};urp;StarOffice.ServiceManager\"";
	}
	
	public function getOption() {
		return $this->options;
	}
	
    public function install() {
    	$status = $this->status();
    	if($status == '') {
			return $this->start();
    	} else {
    		return $status;
    	}
    }
    
    private function setOfficeName($office) {
    	$this->office = $office;
    }
    
    public function getOfficeName() {
    	return $this->office;
    }
    
    public function status() {
    	$cmd = "ps ax | grep ".$this->getOfficeName();
    	$response = $this->util->pexec($cmd);
    	if(is_array($response['out'])) {
    		if(count($response['out']) > 1) {
    			foreach ($response['out'] as $r) {
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
    
    public function start() {
    	$state = $this->status();
    	if($state != 'STARTED') {
			$cmd = "nohup {$this->getBin()} ".$this->getOption()." > ".SYS_LOG_DIR."{$this->getLog()} 2>&1 & echo $!";
	    	$response = $this->util->pexec($cmd);
	    	
	    	return $response;
    	} elseif ($state == '') {
    		// Start Service
    		return true;
    	} else {
    		// Service Running Already
    		return true;
    	}
    	
    	return false;
    }
    
    function stop() {
    	$cmd = "pkill -f ".$this->office;
    	$response = $this->util->pexec($cmd);
		return $response;
	}
	
	function uninstall() {
		$this->stop();
	}
}
?>