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
* @package Installer
* @version Version 0.1
*/

class windowsOpenOffice extends windowsService {

	/**
	* Reference to utility object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
	public $util;
	
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
    public $name = "KTOpenOfficeTest";
    
	public function load() {
        // hack for testing
		$this->setPort("8100");
		$this->setHost("127.0.0.1");
		$this->setLog("openoffice.log");
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
	
	private function setBin($bin) {
		$this->bin = "\"".$bin."\"";
	}
	
	public function getBin() {
		return $this->bin;
	}
    
	private function setWinservice($winservice = "winserv.exe") {
		if(file_exists(SYS_BIN_DIR . $winservice))
			$this->winservice = SYS_BIN_DIR . $winservice;
		else if(file_exists(SYS_BIN_DIR . "win32" . DS. $winservice))
			$this->winservice = SYS_BIN_DIR . "win32" . DS. $winservice;
	}
	
	public function getWinservice() {
		return $this->winservice;
	}
	
	private function setOption() {
		$this->options = "-displayname {$this->name} -start auto {$this->getBin()} -headless -nofirststartwizard "
                       . "-accept=\"socket,host={$this->host},port={$this->port};urp;StarOffice.ServiceManager\"";
	}
	
	public function getOption() {
		return $this->options;
	}
	
    public function install() {
    	$status = $this->status();
    	if($status == '') {
    		$services = $this->util->getDataFromSession('services');
    		$this->setBin("{$services['openOfficeExe']}");
    		$this->setOption();
            $cmd = "\"{$this->winservice}\" install $this->name {$this->getOption()}";
        	if(DEBUG) {
        		echo "$cmd<br/>";
        		return ;
        	}
            $response = $this->util->pexec($cmd);
            return $response;
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
}
?>