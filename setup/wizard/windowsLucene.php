<?php
require_once("service.php");

class Lucene {
	private $javaBin;
	private $javaJVM;
	private $javaSystem;
	private $name;
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
		$this->javaSystem = new Java('java.lang.System');
		$this->setJavaBin($this->javaSystem->getProperty('java.home').DS."bin");
		$this->setLuceneDIR(SYS_DIR.DS."bin".DS."luceneserver");
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
		$this->luceneOut = SYS_LOG_DIR.DS.$luceneOut;
	}
	
	public function getLuceneOut() {
		return $this->luceneOut;
	}
	
	private function setLuceneError($luceneError) {
		$this->luceneError = SYS_LOG_DIR.DS.$luceneError;
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
		// windows
		$cmd = "sc start {$this->name}";
//		echo "start\n$cmd<br/>";
		$response = exec($cmd);
		// linux
		// zend
	}
	
	function stop() {
		$cmd = "sc stop {$this->name}";
		echo "stop\n$cmd<br/>";
		$response = exec($cmd);
	}
	
	function install() {
		$cmd = $this->luceneExe." -install \"".$this->name."\" \"".$this->javaJVM. "\" -Djava.class.path=\"". $this->luceneSource."\"". " -start ".$this->luceneServer. " -out \"".$this->luceneOut."\" -err \"".$this->luceneError."\" -current \"".$this->luceneDir."\" -auto";
//		echo "install\n$cmd<br/>";
		$response = exec($cmd);
	}
	
	function uninstall() {
		$cmd = "sc delete {$this->name}";
//		echo "uninstall\n$cmd<br/>";
		$response = exec($cmd);
	}
}
$lucene = new Lucene();
$lucene->load();
$lucene->install();
$lucene->start();
$lucene->stop();
$lucene->uninstall();
?>