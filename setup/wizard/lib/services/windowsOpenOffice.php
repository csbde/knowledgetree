<?php
/**
* Windows Agent Service Controller.
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

class windowsOpenOffice extends windowsService {
	/**
	* Path to office executable
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
	private $path;

	/**
	* Web server
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
	private $host;

	/**
	* Path to temp pid file
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
	private $pidFile;

	/**
	* Web server Port
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
	private $port;

	/**
	* Web server
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
	private $bin;

	/**
	* Office executable name
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
	private $soffice;

	/**
	* Log file
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
	private $log;

	/**
	* Open office options
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
	private $options;

	/**
	* Path to win service
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
    private $winservice;

	/**
	* Service name
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/
    public $name = "KTOpenoffice";

    public $hrname = "KnowledgeTree OpenOffice.org Service. (KTOpenOffice)";
    
    public $description = "KnowledgeTree OpenOffice.org Service.";
    
	/**
	* Load defaults needed by service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string
	* @return void
 	*/
	public function load($options = null) {
    	if(isset($options['binary'])) {
    		$this->setBin($options['binary']);
    	}
		$this->setPort("8100");
		$this->setHost("127.0.0.1");
		$this->setLog("openoffice.log");
		$this->setWinservice("winserv.exe");
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

	private function setBin($bin) {
		$this->bin = $bin;
	}

	public function getBin() {
		return $this->bin;
	}

	private function setWinservice($winservice = "winserv.exe") {
		if(file_exists(SYS_BIN_DIR . $winservice)) {
			$this->winservice = SYS_BIN_DIR . $winservice;
		} else if(file_exists(SYS_BIN_DIR . "win32" . DS. $winservice)) {
			$this->winservice = SYS_BIN_DIR . "win32" . DS. $winservice;
		}
	}

	public function getWinservice() {
		return $this->winservice;
	}

	public function getOption() {
		return $this->options;
	}

	private function writeOfficeInstall($cmd) {
		$officeInstallFile = SYS_VAR_DIR."bin".DS."officeinstall.bat";
		$fp = fopen($officeInstallFile, "w+");
		fwrite($fp, $cmd);
		fclose($fp);
	}

    public function install() {
    	$status = $this->status();
    	if($status == '') {
    		$binary = $this->getBin();
    		if($binary != '') {
            	$cmd = "\"{$this->winservice}\" install \"{$this->name}\" -description \"{$this->description}\" -displayname \"{$this->name}\" -start auto \"".$binary."\" -headless -invisible -nofirststartwizard -\"accept=socket,host={$this->host},port={$this->port};urp;\"";;
	        	if(DEBUG) {
	        		echo "$cmd<br/>";
	        		return false;
	        	}
	        	$this->writeOfficeInstall($cmd);
	            //$response = $this->util->pexec($cmd);
//	            return $response;
    		}
    		return $status;
    	}
        else {
    		return $status;
    	}
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
		return "";//"Execute from command prompt : $installDir/dmsctl.bat stop";
	}
}
?>