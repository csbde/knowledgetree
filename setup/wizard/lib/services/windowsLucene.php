<?php
/**
* Windows Lucene Service Controller.
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
class windowsLucene extends windowsService {
	/**
	* Java Directory path
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string 
	*/
	private $javaBin;
	
	/**
	* Java JVM path
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string 
	*/
	private $javaJVM;
	
	/**
	* Java System object
	*
	* @author KnowledgeTree Team
	* @access private
	* @var object
	*/
	private $javaSystem;
	
	/**
	* Lucene executable path
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string 
	*/
	private $luceneExe;
	
	/**
	* Lucene jar path
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string 
	*/
	private $luceneSource;
	
	/**
	* Lucene package name
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string 
	*/
	private $luceneServer;
	
	/**
	* Lucene output log path
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string 
	*/
	private $luceneOut;
	
	/**
	* Lucene error log path
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string 
	*/
	private $luceneError;
	
	/**
	* Lucene directory path
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string 
	*/
	private $luceneDir;
	

	/**
	* Service name
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/	
	public $name = "KTLucene";
	
	/**
	* Load defaults needed by service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string
	* @return void
 	*/
	public function load() {
		$this->setJavaBin();
		$this->setLuceneDIR(SYSTEM_DIR."bin".DS."luceneserver");
		$this->setLuceneExe("KTLuceneService.exe");
		$this->setJavaJVM();
		$this->setLuceneSource("ktlucene.jar");
		$this->setLuceneServer("com.knowledgetree.lucene.KTLuceneServer");
		$this->setLuceneOut("lucene-out.txt");
		$this->setLuceneError("lucene-err.txt");
	}
	
	/**
	* Set Java Directory path
	*
	* @author KnowledgeTree Team
	* @access private
	* @param string
	* @return void
 	*/
	private function setJavaBin() {
		if($this->util->zendBridge()) {
			if($this->util->javaBridge()) {
				$this->javaSystem = new Java('java.lang.System');
				$this->javaBin = $this->javaSystem->getProperty('java.home').DS."bin";
				
				return true;
			}
		}
		// TODO: Will not detect, but a java pre-check is done in services, before this
		if(file_exists($this->varDir.'outJVHome')) {
			$this->javaBin = file_get_contents($this->varDir.'outJVHome');
			if($this->javaBin != '') return true;
		}
		
		return false;
	}

	/**
	* Get Java Directory path
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/
	public function getJavaBin() {
		return $this->javaBin;
	}
	
	/**
	* Set Lucene directory path
	*
	* @author KnowledgeTree Team
	* @access private
	* @param string
	* @return void
 	*/
	private function setLuceneDIR($luceneDir) {
		$this->luceneDir = $luceneDir;
	}
	
	/**
	* Get Lucene directory path
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/
	public function getluceneDir() {
		if(file_exists($this->luceneDir))
			return $this->luceneDir;
		return false;
	}

	/**
	* Set Lucene executable path
	*
	* @author KnowledgeTree Team
	* @access private
	* @param string
	* @return void
 	*/
	private function setLuceneExe($luceneExe) {
		$this->luceneExe = $this->getluceneDir().DS.$luceneExe;
	}
	
	/**
	* Get Lucene executable path
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string
	* @return void
 	*/
	public function getLuceneExe() {
		if(file_exists($this->luceneExe))
			return $this->luceneExe;
		return false;
	}
	
	/**
	* Set Lucene source path
	*
	* @author KnowledgeTree Team
	* @access private
	* @param string
	* @return void
 	*/
	private function setLuceneSource($luceneSource) {
		$this->luceneSource = $this->getluceneDir().DS.$luceneSource;
	}
	
	/**
	* Get Lucene source path
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/
	public function getLuceneSource() {
		if(file_exists($this->luceneSource))
			return $this->luceneSource;
		return false;
	}
	
	/**
	* Set Lucene package name
	*
	* @author KnowledgeTree Team
	* @access private
	* @param string
	* @return void
 	*/
	private function setLuceneServer($luceneServer) {
		$this->luceneServer = $luceneServer;
	}
	
	/**
	* Get Lucene package name
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/
	public function getLuceneServer() {
		return $this->luceneServer;
	}
	
	/**
	* Set Lucene output file path
	*
	* @author KnowledgeTree Team
	* @access private
	* @param string
	* @return void
 	*/
	private function setLuceneOut($luceneOut) {
		$this->luceneOut = $this->outputDir.$luceneOut;
	}
	
	/**
	* Get Lucene output file path
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/
	public function getLuceneOut() {
		return $this->luceneOut;
	}
	
	/**
	* Set Lucene error file path
	*
	* @author KnowledgeTree Team
	* @access private
	* @param string
	* @return void
 	*/
	private function setLuceneError($luceneError) {
		$this->luceneError = $this->outputDir.$luceneError;
	}
	
	/**
	* Get Lucene error file path
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/
	public function getLuceneError() {
		return $this->luceneError;
	}
	
	/**
	* Set Java JVM path
	*
	* @author KnowledgeTree Team
	* @access private
	* @param string
	* @return void
 	*/
	private function setJavaJVM() {
		if(file_exists($this->getJavaBin().DS."client".DS."jvm.dll")) {
			$this->javaJVM = $this->getJavaBin().DS."client".DS."jvm.dll";
		} elseif (file_exists($this->getJavaBin().DS."server".DS."jvm.dll")) {
			$this->javaJVM = $this->getJavaBin().DS."server".DS."jvm.dll";
		} elseif (file_exists($this->getJavaBin().DS."bin".DS."client".DS."jvm.dll")) {
			$this->javaJVM = $this->getJavaBin().DS."bin".DS."client".DS."jvm.dll";
		} elseif (file_exists($this->getJavaBin().DS."bin".DS."server".DS."jvm.dll")) {
			$this->javaJVM = $this->getJavaBin().DS."bin".DS."server".DS."jvm.dll";
		} else {
			$javaJVM = $this->useZendJVM();
			if(file_exists($javaJVM)) {
				$this->javaJVM = $javaJVM;
			}
		}
	}
	
    public function useZendJVM() {
	    if($this->util->installEnvironment() == 'Zend') {
	    	if(WINDOWS_OS) { // For Zend Installation only
				$sysdir = explode(DS, SYSTEM_DIR);
				array_pop($sysdir);
				array_pop($sysdir);
				$zendsys = '';
				foreach ($sysdir as $v) {
					$zendsys .= $v.DS;
				}
				$jvm = $zendsys."jre".DS."bin".DS."client".DS."jvm.dll";
				if(file_exists($jvm))
					return $jvm;
	    	}
	    }
	    
	    return false;
    }
	
	/**
	* Get Java JVM path
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/
	public function getJavaJVM() {
		return $this->javaJVM;
	}
	
	/**
	* Install Lucene Service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
 	*/
	public function install() {
		$state = $this->status();
		if($state == '') {
			$luceneExe = $this->getLuceneExe();
			$luceneSource = $this->getLuceneSource();
			$luceneDir = $this->getluceneDir();
			if($luceneExe && $luceneSource && $luceneDir) {
				$cmd = "\"{$luceneExe}\""." -install \"".$this->getName()."\" \"".$this->getJavaJVM(). "\" -Djava.class.path=\"".$luceneSource."\"". " -start ".$this->getLuceneServer(). " -out \"".$this->getLuceneOut()."\" -err \"".$this->getLuceneError()."\" -current \"".$luceneDir."\" -auto";
            	if(DEBUG) {
            		echo "$cmd<br/>";
            		return false;
            	}
				$response = $this->util->pexec($cmd);
				return $response;
			}
			return $state;
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