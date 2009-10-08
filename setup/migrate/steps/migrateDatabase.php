<?php
/**
* Database Step Controller. 
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
* @package Migrater
* @version Version 0.1
*/

class migrateDatabase extends Step 
{
	/**
	* Reference to Database object
	*
	* @author KnowledgeTree Team
	* @access public
	* @var object
	*/	
    public $_dbhandler = null;
    	
	/**
	* Reference to Utility object
	*
	* @author KnowledgeTree Team
	* @access public
	* @var object
	*/	
    public $util = null;
   
	/**
	* List of errors encountered
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $error = array();
    
	/**
	* Flag to store class information in session
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $storeInSession = true;
    
	/**
	* Flag if step needs to be migrated
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $runMigrate = true;
    
	/**
	* Flag if step needs to run silently
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $silent = false;

	/**
	* List of errors used in template
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $templateErrors = array('dmspassword', 'dmsuserpassword', 'con', 'dname', 'dtype', 'duname', 'dpassword');
    private $sqlDumpFile = '';
	/**
	* Constructs database object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
 	*/
    public function __construct() {
    	$this->temp_variables = array("step_name"=>"database", "silent"=>$this->silent);
    	$this->util = new MigrateUtil();
    	if(WINDOWS_OS)
			$this->mysqlDir = MYSQL_BIN;
        $this->wizardLocation = '../wizard';
    }

	/**
	* Main control of database setup
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function doStep() {
    	$this->initErrors(); // Load template errors
    	$this->setDetails(); // Set any posted variables
    	if(!$this->inStep("database")) {
    		return 'landing';
    	}
		if($this->next()) {
			if($this->exportDatabase()) {
				$this->storeSilent();
				return 'next';
			}
		} else if($this->previous()) {
			return 'previous';
		}
        
        return 'landing';
    }

    public function exportDatabase() {
    	if($this->doTest()) {
	    	$installation = $this->getDataFromSession("installation"); // Get installation directory
	    	$dbSettings = $installation['dbSettings'];
	    	$location = $installation['location'];
			$uname = $this->temp_variables['duname'];
			$pwrd = $this->temp_variables['dpassword'];
			$tmpFolder = $this->resolveTempDir();
	    	if(WINDOWS_OS) {
//	    		$tmpFolder = "tmp/";
	    		$exe = "\"$location\mysql\bin\mysqldump.exe\""; // Location of dump
	    	} else {
//	    		$tmpFolder = "/tmp/";
	    		$exe = "'$location/mysql/bin/mysqldump'"; // Location of dump
	    	}
			$sqlFile = $tmpFolder."/dms_migrate.sql";
			$dbAdminUser = $dbSettings['dbAdminUser'];
			$dbAdminPass = $dbSettings['dbAdminPass'];
			$dbName = $dbSettings['dbName'];
			$cmd = "$exe -u{$dbAdminUser} -p{$dbAdminPass} $dbName > ".$sqlFile;
//			echo $cmd;
//			die;
			$response = $this->util->pexec($cmd);
			if(file_exists($sqlFile)) {
				$fileContents = file_get_contents($sqlFile);
				if(!empty($fileContents)) {
					$this->sqlDumpFile = realpath($sqlFile); // Store location of dump
					return true;
				}
			}
    	}
    	
		return false;
    }
    
    // TODO
function resolveTempDir()
{

    if (!WINDOWS_OS)
    {
        $dir='/tmp/kt-db-backup';
    }
    else
    {
        $dir='c:/kt-db-backup';
    }
//    $oKTConfig =& KTConfig::getSingleton();
//    $dir = $oKTConfig->get('backup/backupDirectory',$dir);

    if (!is_dir($dir))
    {
            mkdir($dir);
    }
    return $dir;
}

    public function doTest() {
    	return true;
    	$installation = $this->getDataFromSession("installation"); // Get installation directory
    	$dbSettings = $installation['dbSettings'];
    	$location = $installation['location'];
		$uname = $this->temp_variables['duname'];
		$pwrd = $this->temp_variables['dpassword'];
		//dmsadmin	//clean1 // 3316 // TODO
		$dbhandler = $this->util->loadInstallDBUtil();
		$dbhandler->load("localhost:3316",$uname, $pwrd, "dms");
    	if (!$dbhandler->getDatabaseLink()) {
        	$this->error['con'] = "Could not connect to the database, please check username and password";
        	return false;
    	}
    	
    	return true;
    }
    
	/**
	* Store options
	*
	* @author KnowledgeTree Team
	* @params object SimpleXmlObject
	* @access private
	* @return void
	*/
   private function setDetails() {
        $this->temp_variables['duname'] = $this->getPostSafe('duname');
        $this->temp_variables['dpassword'] = $this->getPostSafe('dpassword');
        $this->temp_variables['dumpLocation'] = $this->getPostSafe('dumpLocation');
        // create lock file to indicate migration mode
        $this->createMigrateFile();
    }
    
    /**
     * Creates migration lock file so that system knows it is supposed to run an upgrade installation
     * 
     * @author KnowledgeTree Team
     * @access private
     * @return void
     */
    private function createMigrateFile() {
        @touch($this->wizardLocation . DIRECTORY_SEPARATOR . "migrate.lock");
    }
    
	/**
	* Safer way to return post data
	*
	* @author KnowledgeTree Team
	* @params SimpleXmlObject $simplexml
	* @access public
	* @return void
	*/
    public function getPostSafe($key) {
    	return isset($_POST[$key]) ? $_POST[$key] : "";
    }
    
	/**
	* Stores varibles used by template
	*
	* @author KnowledgeTree Team
	* @params none
	* @access public
	* @return array
	*/
    public function getStepVars() {
        return $this->temp_variables;
    }

	/**
	* Returns database errors
	*
	* @author KnowledgeTree Team
	* @access public
	* @params none
	* @return array
	*/
    public function getErrors() {

        return $this->error;
    }
    
	/**
	* Initialize errors to false
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function initErrors() {
    	foreach ($this->templateErrors as $e) {
    		$this->error[$e] = false;
    	}
    }
    
    private function storeSilent() {
    	// TODO
    	$_SESSION['database']['dumpLocation'] = $this->sqlDumpFile;
    	$this->temp_variables['dumpLocation'] = $this->sqlDumpFile;
    }
    
}
?>