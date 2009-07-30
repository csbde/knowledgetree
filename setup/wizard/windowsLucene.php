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
class windowsLucene extends Service {
	private $javaBin;
	private $javaJVM;
	private $javaSystem;
//	private $name;
	private $luceneExe;
	private $luceneSource;
	private $luceneServer;
	private $luceneOut;
	private $luceneError;
	private $luceneDir;
	private $util = null;
	
	public function __construct() {
	}
	
	function load() {
		$this->name = "KTLuceneTest";
		$this->util = new InstallUtil();
		$this->javaSystem = new Java('java.lang.System');
		$this->setJavaBin($this->javaSystem->getProperty('java.home').DS."bin");
		$this->setLuceneDIR(SYSTEM_DIR."bin".DS."luceneserver");
		$this->setLuceneExe("KTLuceneService.exe");
		$this->setJavaJVM();
		$this->setLuceneSource("ktlucene.jar");
		$this->setLuceneServer("com.knowledgetree.lucene.KTLuceneServer");
		$this->setLuceneOut("lucene-out.txt");
		$this->setLuceneError("lucene-err.txt");
		
	}

	private function setJavaBin($javaBin) {
		$this->javaBin = $javaBin;
	}
	
	public function getJavaBin() {
		return $this->javaBin;
	}
	
	private function setLuceneDIR($luceneDir) {
		$this->luceneDir = $luceneDir;
	}
	
	public function getluceneDir() {
		return $this->luceneDir;
	}

	private function setLuceneExe($luceneExe) {
		$this->luceneExe = $this->getluceneDir().DS.$luceneExe;
	}
	
	public function getLuceneExe() {
		return $this->luceneExe;
	}
	
	private function setLuceneSource($luceneSource) {
		$this->luceneSource = $this->getluceneDir().DS.$luceneSource;
	}
	
	public function getLuceneSource() {
		return $this->luceneSource;
	}
	
	private function setLuceneServer($luceneServer) {
		$this->luceneServer = $luceneServer;
	}
	
	public function getLuceneServer() {
		return $this->luceneServer;
	}
	
	private function setLuceneOut($luceneOut) {
		$this->luceneOut = SYS_LOG_DIR.$luceneOut;
	}
	
	public function getLuceneOut() {
		return $this->luceneOut;
	}
	
	private function setLuceneError($luceneError) {
		$this->luceneError = SYS_LOG_DIR.$luceneError;
	}
	
	public function getLuceneError() {
		return $this->luceneError;
	}
	
	private function setJavaJVM() {
		if(file_exists($this->getJavaBin().DS."client".DS."jvm.dll")) {
			$this->javaJVM = $this->getJavaBin().DS."client".DS."jvm.dll";
		} elseif (file_exists($this->getJavaBin().DS."server".DS."jvm.dll")) {
			$this->javaJVM = $this->getJavaBin().DS."server".DS."jvm.dll";
		}
	}
	
	public function getJavaJVM() {
		return $this->javaJVM;
	}
	
	function start() {
		$cmd = "sc start {$this->name}";
		$response = $this->util->pexec($cmd);
		
		return $response;
	}
	
	function stop() {
		$cmd = "sc stop {$this->name}";
		$response = $this->util->pexec($cmd);
		
		return $response;
	}
	
	function install() {
		$cmd = "\"$this->luceneExe\""." -install \"".$this->name."\" \"".$this->javaJVM. "\" -Djava.class.path=\"". $this->luceneSource."\"". " -start ".$this->luceneServer. " -out \"".$this->luceneOut."\" -err \"".$this->luceneError."\" -current \"".$this->luceneDir."\" -auto";
		$response = $this->util->pexec($cmd);
		
		return $response;
	}
	
	function uninstall() {
		$cmd = "sc delete {$this->name}";
		$response = $this->util->pexec($cmd);
		
		return $response;
	}
	
	function status() {
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