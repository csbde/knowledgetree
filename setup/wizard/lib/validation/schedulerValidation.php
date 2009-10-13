<?php
class schedulerValidation extends serviceValidation {
	/**
	* Path to php executable
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
    protected $php;
    
	/**
	* Flag if php already provided
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    public $providedPhp = false;
    
	/**
	* PHP Installed 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $phpCheck = 'tick';

	/**
	* Flag, if php is specified and an error has been encountered
	*
	* @author KnowledgeTree Team
	* @access public
	* @var boolean
	*/
    private $phpExeError = false;
    
	/**
	* Holds path error, if php is specified
	*
	* @author KnowledgeTree Team
	* @access public
	* @var string
	*/
    private $phpExeMessage = '';
    
    public function getBinary() {
		$this->php = $this->util->getPhp();
    }
    
    public function binaryChecks() {
    	// TODO: Better detection
    	return true;
    	$this->setPhp();
    	if($this->util->phpSpecified()) {
			return $this->detPhpSettings();
    	} else {
    		$this->specifyPhp();// Ask for settings
			return false;
    	}
    }
    
    function detPhpSettings() {
    	// TODO: Better php handling
    	return true;
    	$phpExecutable = $this->util->phpSpecified();// Retrieve java bin
    	$cmd = "$phpExecutable -version > ".$this->outputDir."/outPHP 2>&1 echo $!";
    	$response = $this->util->pexec($cmd);
    	if(file_exists($this->outputDir.'outPHP')) {
    		$tmp = file_get_contents($this->outputDir.'outPHP');
    		preg_match('/PHP/',$tmp, $matches);
    		if($matches) {
				$this->phpCheck = 'tick';
				
				return true;
    		} else {
    			$this->phpCheck = 'cross_orange';
    			$this->phpExeError = "PHP : Incorrect path specified";
				$this->error[] = "PHP executable required";
				
				return false;
    		}
    	}
    }
    
    /**
	* Set template view to specify php
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function specifyPhp() {
    	$this->phpExeError = true;
    }
    
    private function setPhp() {
		if($this->php != '') { // PHP Found
			$this->phpCheck = 'tick';
		} elseif (PHP_DIR != '') { // Use System Defined Settings
			$this->php = PHP_DIR;
		} else {

		}
		$this->temp_variables['php']['location'] = $this->php;
    }
    
	public function getPhpDir() {
		return $this->php;
	}
	
    public function storeSilent() { // TODO : PHP detection
    	$this->temp_variables['schedulerInstalled'] = $this->installed;
		$this->temp_variables['phpCheck'] = $this->phpCheck;
		$this->temp_variables['phpExeError'] = $this->phpExeError;
		
		return $this->temp_variables;
    }
}

?>