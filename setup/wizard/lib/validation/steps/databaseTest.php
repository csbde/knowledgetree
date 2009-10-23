<?php

class databaseTest extends Test { 
	private $stepName = "database";
	private $params = array();
	
	public function __construct() {
		require_once(WIZARD_DIR."step.php");
		require_once(WIZARD_DIR."installUtil.php");
		require_once(WIZARD_DIR."path.php");
		require_once(WIZARD_DIR."dbUtilities.php");
		require_once(STEP_DIR."$this->stepName.php");
	}
	
	/**
	* Test database connectivity
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function doAjaxTest($host, $uname, $dname) {
		echo "$host, $uname, $dname";
		die;
    }

    public function doCreateSchema() {
    	$this->dhost = '127.0.0.1';
    	$this->duname = 'root';
    	$this->dpassword = 'root';
    	$this->dname = 'dms_install';
    	$this->dbbinary = 'mysql';
    	$this->dbhandler->load($this->dhost, $this->duname, $this->dpassword, $this->dname);
    	$this->createSchema();
    	echo 'Schema loaded<br>';
    }
}


?>