<?php
/**
* Unix Lucene Service Controller.
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

class unixLucene extends unixService {
	private $shutdownScript;
	private $indexerDir;
	private $lucenePidFile;
	private $luceneDir;
	private $luceneSource;
	private $luceneSourceLoc;
	private $javaXms;
	private $javaXmx;
	public $name = "KTLucene";
	public $hrname = "KnowledgeTree Indexer Service";

	/**
	* Load defaults needed by service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string
	* @return void
 	*/
	public function load() {
		$this->setLuceneSource("ktlucene.jar");
		$this->setLuceneDir(SYSTEM_DIR."bin".DS."luceneserver".DS);
		$this->setIndexerDir(SYSTEM_DIR."search2".DS."indexing".DS."bin".DS);
		$this->setLucenePidFile("lucene_test.pid");
		$this->setJavaXms(512);
		$this->setJavaXmx(512);
		$this->setLuceneSourceLoc("ktlucene.jar");
		$this->setShutdownScript("shutdown.php");
	}


	public function setIndexerDir($indexerDir) {
		$this->indexerDir = $indexerDir;
	}

	private function getIndexerDir() {
		return $this->indexerDir;
	}

	private function setShutdownScript($shutdownScript) {
		$this->shutdownScript = $shutdownScript;
	}

	public function getShutdownScript() {
		return $this->shutdownScript;
	}

	private function setLucenePidFile($lucenePidFile) {
		$this->lucenePidFile = $lucenePidFile;
	}

	private function getLucenePidFile() {
		return $this->lucenePidFile;
	}

	private function setLuceneDir($luceneDir) {
		$this->luceneDir = $luceneDir;
	}

	public function getLuceneDir() {
		return $this->luceneDir;
	}

	private function setJavaXms($javaXms) {
		$this->javaXms = "-Xms$javaXms";
	}

	public function getJavaXms() {
		return $this->javaXms;
	}

	private function setJavaXmx($javaXmx) {
		$this->javaXmx = "-Xmx$javaXmx";
	}

	public function getJavaXmx() {
		return $this->javaXmx;
	}

	private function setLuceneSource($luceneSource) {
		$this->luceneSource = $luceneSource;
	}

	public function getLuceneSource() {
		return $this->luceneSource;
	}

	private function setLuceneSourceLoc($luceneSourceLoc) {
		$this->luceneSourceLoc = $this->getLuceneDir().$luceneSourceLoc;
	}

	public function getLuceneSourceLoc() {
		return $this->luceneSourceLoc;
	}

	public function getJavaOptions() {
		return " {$this->getJavaXmx()} {$this->getJavaXmx()} -jar ";
	}

	/**
	* Stop Service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return array
 	*/
  	public function stop() {
		$state = $this->status();
		if($state != '') {
			$cmd = "pkill -f ".$this->getLuceneSource();
    		$response = $this->util->pexec($cmd);
			return $response;
		}
		return $state;
    }

    public function install() {
    	$status = $this->status();
    	if($status == '') {
			return $this->start();
    	} else {
    		return $status;
    	}
    }

    public function status() {
    	$cmd = "ps ax | grep ".$this->getLuceneSource();
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

    public function uninstall() {
    	$this->stop();
    }

	/**
	* Start Service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return boolean
 	*/
    public function start() {
    	$state = $this->status();
    	$this->writeLuceneProperties();
    	if($state != 'STARTED') {
    		//$logFile = $this->outputDir."lucene.log";
    		$logFile = "/dev/null";//$this->outputDir."lucene.log";
    		//unlink($logFile);
	    	$cmd = "cd ".$this->getLuceneDir()."; ";
	    	$cmd .= "nohup java -jar ".$this->getLuceneSource()." {$this->getJavaXmx()} {$this->getJavaXms()} > ".$logFile." 2>&1 & echo $!";
//	    	if(DEBUG) {
//	    		echo "$cmd<br/>";
//	    		return false;
//	    	}
	    	//$response = $this->util->pexec($cmd);

//	    	return $response;
			return false;
    	}

    	return true;
    }

	public function getName() {
		return $this->name;
	}

	public function getHRName() {
		return $this->hrname;
	}

	public function getStopMsg($installDir) {
		return "";//"Execute from terminal : $installDir/dmsctl.sh stop";
	}

	/**
	* Write Lucene Service property file
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return string
	*/
	private function writeLuceneProperties() {
		// Check if bin is readable and writable
		$fileLoc = $this->getluceneDir(). "KnowledgeTreeIndexer.properties";
		$fp = fopen($fileLoc, "w+");
		$content = "server.port=8875\n";
		$content .= "server.paranoid=false\n";
		$content .= "server.accept=127.0.0.1\n";
		$content .= "server.deny=\n";
		$conf = $this->util->getDataFromSession('configuration');
		$varDirectory = $conf['paths']['varDirectory']['path'];
		$content .= "indexer.directory=$varDirectory" . DS . "indexes\n";
		$content .= "indexer.analyzer=org.apache.lucene.analysis.standard.StandardAnalyzer\n";
		fwrite($fp, $content);
		fclose($fp);
		chmod($fileLoc, 0644);
	}
}
?>