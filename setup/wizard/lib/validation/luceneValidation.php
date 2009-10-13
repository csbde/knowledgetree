<?php
class luceneValidation extends serviceValidation {
	/**
	* Path to java executable
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
    private $java = "";
    
	/**
	* Minumum Java Version
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
    private $javaVersion = '1.5';

	/**
	* Flag if java already provided
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $providedJava = false;
    
	/**
	* Flag, if java is specified and an error has been encountered
	*
	* @author KnowledgeTree Team
	* @access public
	* @var booelean
	*/
    private $javaExeError = false;
    
	/**
	* Holds path error, if java is specified
	*
	* @author KnowledgeTree Team
	* @access public
	* @var string
	*/
    private $javaExeMessage = '';
    
	/**
	* Java Installed 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $javaCheck = 'cross';
    
	/**
	* Flag if bridge extension needs to be disabled
	*
	* @author KnowledgeTree Team
	* @access public
	* @var boolean
	*/
    private $disableExtension = false;

	/**
	* Java Bridge Installed 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $javaExtCheck = 'cross_orange';
    
    public function preset() {
		$this->zendBridgeNotInstalled(); // Set bridge not installed
		$this->javaVersionInCorrect(); // Set version to incorrect
		$this->javaNotInstalled(); // Set java to not installed
		$this->setJava(); // Check if java has been auto detected
    }
    
    /**
	* Check if java executable was found
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return array
	*/
	private function setJava() {
		if($this->java != '') { // Java JRE Found
			$this->javaCheck = 'tick';
			$this->javaInstalled();
			$this->temp_variables['java']['location'] = $this->java;
			return ;
		}
		
		$this->temp_variables['java']['location'] = $this->java;
    }

	/**
	* Store Java state as installed
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function javaInstalled() {
		$this->temp_variables['java']['class'] = 'tick';
		$this->temp_variables['java']['found'] = "Java Runtime Installed";
    }
    
	/**
	* Store Java state as not installed
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function javaNotInstalled() {
		$this->temp_variables['java']['class'] = 'cross';
		$this->temp_variables['java']['found'] = "Java runtime environment required";
    }
    
	/**
	* Store Java version state as correct
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function javaVersionCorrect() {
		$this->temp_variables['version']['class'] = 'tick';
		$this->temp_variables['version']['found'] = "Java Version 1.5+ Installed";
    }
    
	/**
	* Store Java version state as warning
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function javaVersionWarning() {
		$this->temp_variables['version']['class'] = 'cross_orange';
		$this->temp_variables['version']['found'] = "Java Runtime Version Cannot be detected";
    }
    
	/**
	* Store Java version as state incorrect
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function javaVersionInCorrect() {
		$this->temp_variables['version']['class'] = 'cross';
		$this->temp_variables['version']['found'] = "Requires Java 1.5+ to be installed";
    }
    
	/**
    * Store Zend Bridge state as installed
    *
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
    */
    private function zendBridgeInstalled() {
		$this->temp_variables['extensions']['class'] = 'tick';
		$this->temp_variables['extensions']['found'] = "Java Bridge Installed";
    }
    
	/**
    * Store Zend Bridge state as not installed
    * 
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
    */
    private function zendBridgeNotInstalled() {
		$this->temp_variables['extensions']['class'] = 'cross_orange';
		$this->temp_variables['extensions']['found'] = "Zend Java Bridge Not Installed";
    }
    
   	/**
    * Store Zend Bridge state as warning
    *
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
    */
    private function zendBridgeWarning() {
		$this->temp_variables['extensions']['class'] = 'cross_orange';
		$this->temp_variables['extensions']['found'] = "Zend Java Bridge Not Functional";
    }
    
    public function installed() {
		$this->disableExtension = true; // Disable the use of the php bridge extension
		$this->javaVersionCorrect();
		$this->javaInstalled();
		$this->javaCheck = 'tick';
    }
    
    public function getBinary() {
    	$this->java = $this->util->getJava();
    }
    
    /**
	* Do some basic checks to help the user overcome java problems
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    public function binaryChecks() {
    	if($this->util->javaSpecified()) {
    		$this->disableExtension = true; // Disable the use of the php bridge extension
    		if($this->detSettings(true)) { // AutoDetect java settings
    			return true;
    		} else {
    			$this->specifyJava(); // Ask for settings
    		}
    	} else {
    		$auto = $this->useBridge(); // Use Bridge to get java settings
    		if($auto) {
				return $auto;
    		} else {
    			$auto = $this->useDetected(); // Check if auto detected java works
    			if($auto) {
    				$this->disableExtension = true; // Disable the use of the php bridge extension
    				return $auto;
    			} else {
					$this->specifyJava(); // Ask for settings
    			}
    		}
			return $auto;
    	}
    }
    
	/**
	* Set template view to specify java
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function specifyJava() {
    	$this->javaExeError = true;
    }
    
    /**
	* Attempts to use bridge and configure java settings
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function useBridge() {
		$zendBridge = $this->util->zendBridge(); // Find Zend Bridge
		if($zendBridge) { // Bridge installed implies java exists
			$this->zendBridgeInstalled();
			if($this->checkZendBridge()) { // Make sure the Zend Bridge is functional
				$this->javaExtCheck = 'tick'; // Set bridge to functional
		    	$this->javaInstalled(); // Set java to installed
	    		$javaSystem = new Java('java.lang.System');
		    	$version = $javaSystem->getProperty('java.version');
		    	$ver = substr($version, 0, 3);
		    	if($ver < $this->javaVersion) {
					$this->javaVersionInCorrect();
					$this->errors[] = "Requires Java 1.5+ to be installed";
					return false;
		    	} else {
					$this->javaVersionCorrect(); // Set version to correct
					$this->javaCheck = 'tick';
					return true;
		    	}
			} else {
				$this->javaCheck = 'cross_orange';
				$this->javaVersionWarning();
				$this->zendBridgeWarning();
				$this->warnings[] = "Zend Java Bridge Not Functional";
				$this->javaExtCheck = 'cross_orange';
				return false;
			}
		} else {
			$this->warnings[] = "Zend Java Bridge Not Found";
			return false;
		}
    }
    
    /**
	* Check if Zend Bridge is functional
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function checkZendBridge() {
    	if($this->util->javaBridge()) { // Check if java bridge is functional
			return true;
    	} else {
			return false;
    	}    	
    }
    
   	/**
	* Attempts to use user input and configure java settings
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function detSettings($attempt = false) {
    	$javaExecutable = $this->util->javaSpecified();// Retrieve java bin
    	if($javaExecutable == '') {
    		if($this->java == '') {
    			return false;
    		}
    		$javaExecutable = $this->java;
    	}
    	if(WINDOWS_OS) { 
    		$cmd .= "\"$javaExecutable\" -cp \"".SYS_DIR.";\" javaVersion \"".$this->outputDir."outJV\""." \"".$this->outputDir."outJVHome\"";
    		$func = OS."ReadJVFromFile";
    		if($this->$func($cmd)) return true;
    	} else {
    		$cmd = "\"$javaExecutable\" -version > ".$this->outputDir."outJV 2>&1 echo $!";
    		$func = OS."ReadJVFromFile";
    		if($this->$func($cmd)) return true;
    	}

		$this->javaVersionInCorrect();
		$this->javaCheck = 'cross';
		$this->error[] = "Requires Java 1.5+ to be installed";
    	return false;
    }
    
    function windowsReadJVFromFile($cmd) {
    	$response = $this->util->pexec($cmd);
		if(file_exists($this->outputDir.'outJV')) {
			$version = file_get_contents($this->outputDir.'outJV');
			if($version != '') {
				if($version < $this->javaVersion) { // Check Version of java
					$this->javaVersionInCorrect();
					$this->javaCheck = 'cross';
					$this->error[] = "Requires Java 1.5+ to be installed";
					
					return false;
				} else {
					$this->javaVersionCorrect();
					$this->javaInstalled();
					$this->javaCheck = 'tick';
					$this->providedJava = true;
					
					return true;
				}
			} else {
				$this->javaVersionWarning();
				$this->javaCheck = 'cross_orange';
				if($attempt) {
					$this->javaExeMessage = "Incorrect java path specified";
					$this->javaExeError = true;
					$this->error[] = "Requires Java 1.5+ to be installed";
				}
				
				return false;
			}
		}
    }
    
    function unixReadJVFromFile($cmd) {
    	$response = $this->util->pexec($cmd);
		if(file_exists($this->outputDir.'outJV')) {
			$tmp = file_get_contents($this->outputDir.'outJV');
			preg_match('/"(.*)"/',$tmp, $matches);
			if($matches) {
				if($matches[1] < $this->javaVersion) { // Check Version of java
					$this->javaVersionInCorrect();
					$this->javaCheck = 'cross';
					$this->error[] = "Requires Java 1.5+ to be installed";
					
					return false;
				} else {
					$this->javaVersionCorrect();
					$this->javaInstalled();
					$this->javaCheck = 'tick';
					$this->providedJava = true;
					
					return true;
				}
			} else {
				$this->javaVersionWarning();
				$this->javaCheck = 'cross_orange';
				if($attempt) {
					$this->javaExeMessage = "Incorrect java path specified";
					$this->javaExeError = true;
					$this->error[] = "Requires Java 1.5+ to be installed";
				}
				
				return false;
			}
		}
    }
    
	/**
	* Attempt detection without logging errors
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function useDetected() {
    	return $this->detSettings();
    }
    
   	/**
    * Set all silent mode varibles
    *
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
    */
    public function storeSilent() {
    	$this->temp_variables['luceneInstalled'] = $this->installed;
		$this->temp_variables['javaExeError'] = $this->javaExeError;
		$this->temp_variables['javaExeMessage'] = $this->javaExeMessage;
		$this->temp_variables['javaCheck'] = $this->javaCheck;
		$this->temp_variables['javaExtCheck'] = $this->javaExtCheck;
		$this->temp_variables['providedJava'] = $this->providedJava;
		$this->temp_variables['disableExtension'] = $this->disableExtension;
		return $this->temp_variables;
    }
}
?>