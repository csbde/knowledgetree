<?php
class serviceValidation {
	/**
	* List of errors encountered
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array
	*/
    protected $errors = array();
    
	/**
	* List of warnings encountered
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array
	*/
    protected $warnings = array();
    
	/**
	* Reference to utility object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return object
 	*/
	public $util;

	public $outputDir;
	
	public $varDir;

	/**
	* Flag if services are already Installed
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    public $installed = false;
    
	/**
	* List of variables to be loaded to template
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $temp_variables = array();
    
	public function __construct() {
		$this->util = new InstallUtil();
		$this->setSystemDirs();
	}
	
	function setSystemDirs() {
		$conf = $this->util->getDataFromSession('configuration');
		$this->outputDir = $conf['paths']['logDirectory']['path'].DS;
		$this->varDir = $conf['paths']['varDirectory']['path'].DS;
	}
	
    public function installed() {
    	
    }
    
    public function getBinary() {
    	
    }
    
    public function binaryChecks() {
    	
    }
    
    public function preset() {
    	
    }
}
?>