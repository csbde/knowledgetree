<?php
class openofficeValidation extends serviceValidation {
	/**
	* Path to open office executable
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
	public $soffice;

	/**
	* Flag if open office already provided
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    public $providedOpenOffice = false;
    
	/**
	* Flag, if open office is specified and an error has been encountered
	*
	* @author KnowledgeTree Team
	* @access public
	* @var boolean
	*/
    private $openOfficeExeError = false;
    
	/**
	* Holds path error, if open office is specified
	*
	* @author KnowledgeTree Team
	* @access public
	* @var string
	*/
    private $openOfficeExeMessage = '';

	/**
	* Open Office Installed 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $openOfficeCheck = 'cross';

	/**
	* Open Office windows locations 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $windowsLocations = array("C:\Program Files\OpenOffice.org 3\program", "C:\OpenOffice.org 3\program");
    
	/**
	* Open Office unix locations 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $unixLocations = array("");

    
    public function preset() {
    	$this->specifyOpenOffice();
    }
    
	/**
	* Set template view to specify open office
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function specifyOpenOffice() {
    	$this->openOfficeExeError = true;
    }

	private function openOfficeInstalled() {
    	$this->openOfficeExeError = false;
    }
    
    public function getBinary() {
    	$this->soffice = $this->util->getOpenOffice();
    }
    
    public function binaryChecks() {
    	if($this->util->openOfficeSpecified()) {
    		$this->soffice = $this->util->openOfficeSpecified();
			if(file_exists($this->soffice)) 
				return true;
			else 
				return false;
    	} else {
    		$auto = $this->detectOpenOffice();
    		if($auto) {
    			$this->soffice = $auto;
    			return true;
    		}
    		return false;
    	}
    }
    
	private function detectOpenOffice() {
		if(WINDOWS_OS) {
			$locations = $this->windowsLocations;
			$bin = "soffice.exe";
		} else {
			$locations = $this->unixLocations;
			$bin = "soffice";
		}
		foreach ($locations as $loc) {
			$pathToBinary = $loc.DS.$bin;
			if(file_exists($pathToBinary)) {
				return $pathToBinary;
			}
		}
		
		return false;
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
    	$this->temp_variables['openOfficeInstalled'] = $this->installed;
		$this->temp_variables['openOfficeExe'] = $this->soffice;
		$this->temp_variables['openOfficeExeError'] = $this->openOfficeExeError;
		$this->temp_variables['openOfficeExeMessage'] = $this->openOfficeExeMessage;
		return $this->temp_variables;
    }
}

?>