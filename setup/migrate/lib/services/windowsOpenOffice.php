<?php
/**
* Windows Agent Service Controller.
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
* @package Migrater
* @version Version 0.1
*/

class windowsOpenOffice extends windowsService {

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
    private $winservice;
	
	public function __construct() {
		$this->name = "openoffice";
		$this->util = new MigrateUtil();
	}
	
	public function load() {
        // hack for testing
		$this->setPort("8100");
		$this->setHost("127.0.0.1");
		$this->setLog("openoffice.log");
		$this->setBin("C:\Program Files (x86)\OpenOffice.org 3\program\soffice.bin");
		$this->setBin("C:\Program Files (x86)\ktdms\openoffice\program\soffice.bin");
		$this->setBin("C:\Program Files (x86)\ktdms\openoffice.2.4\program\soffice.bin");
		$this->setWinservice("winserv.exe");
		$this->setOption();
	}
	
	private function setPort($port = "8100") {
		$this->port = $port;
	}
	
	public function getPort() {
		return $this->port;
	}
	
	private function setHost($host = "127.0.0.1") {
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
    
	private function setWinservice($winservice = "winserv.exe") {
		$this->winservice = SYS_BIN_DIR . $winservice;
	}
	
	public function getWinservice() {
		return $this->winservice;
	}
	
	private function setOption() {
		$this->options = "-displayname {$this->name} -start auto \"{$this->bin}\" -headless -invisible "
                       . "-accept=socket,host={$this->host},port={$this->port};urp;";
	}
	
	public function getOption() {
		return $this->options;
	}
	
    public function migrate() {
    	$status = $this->status();
        
    	if($status == '') {
            $cmd = "\"{$this->winservice}\" migrate $this->name $this->options";
            $response = $this->util->pexec($cmd);
            return $response;
    	}
        else {
    		return $status;
    	}
    }
    
//    public function start() {
//    	$state = $this->status();
//    	if($state != 'STARTED') {
//			$cmd = 'sc start ' . $this->name;
//	    	$response = $this->util->pexec($cmd);
//	    	
//	    	return $response;
//    	} elseif ($state == '') {
//    		// Start Service
//    		return true;
//    	} else {
//    		// Service Running Already
//    		return true;
//    	}
//    	
//    	return false;
//    }
    
}
?>