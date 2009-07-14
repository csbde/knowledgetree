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
* @package Installer
* @version Version 0.1
*/
require_once(WIZARD_DIR.'step.php');
require_once(WIZARD_DIR.'database.inc');

class database extends Step 
{
	/**
	* Database type
	*
	* @author KnowledgeTree Team
	* @access private
	* @var array
	*/	
    private $dbhandler = null;
    	
	/**
	* Database type
	*
	* @author KnowledgeTree Team
	* @access private
	* @var array
	*/	
    private $dtype = '';
    
	/**
	* Database types
	*
	* @author KnowledgeTree Team
	* @access private
	* @var array
	*/	
    private $dtypes = array();
    
	/**
	* Database host
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dhost = '';
    
	/**
	* Database port
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dport = '';
    
	/**
	* Database name
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dname = '';
    
	/**
	* Database root username
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $duname = '';
    
	/**
	* Database root password
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dpassword = '';
    
	/**
	* Database dms username
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dmsname = '';
    
	/**
	* Database dms password
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dmspassword = '';

	/**
	* Default dms user username
	*
	* @author KnowledgeTree Team
	* @access private
	* @var boolean
	*/
    private $dmsusername = '';
    
	/**
	* Default dms user password
	*
	* @author KnowledgeTree Team
	* @access private
	* @var boolean
	*/
	private $dmsuserpassword = '';
	
	/**
	* Location of database binary.
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dbbinary = 'mysql'; // TODO:multiple databases
    
	/**
	* Database table prefix
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $tprefix = '';
    
	/**
	* Flag to drop database
	*
	* @author KnowledgeTree Team
	* @access private
	* @var boolean
	*/
    private $ddrop = false;
    
	/**
	* List of variables needed
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $temp_variables = array("step_name"=>"database");
    
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
	* Flag if step needs to be installed
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $runInstall = true;
    
	/**
	* Constructs database object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
 	*/
    public function __construct() {
    	$this->dbhandler = new DBUtil();
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
    	$this->setErrorsFromSession();
        if($this->inStep("database")) {
            $res = $this->doProcess();
        	if($res) { // If theres a response, return it
            	return $res;
        	}
        }
        if($this->setDataFromSession("database")) { // Attempt to set values from session
        	
            $this->setDetails(); // Set any posted variables
        } else {
        	
            $this->loadDefaults($this->readXml()); // Load default variables from file
        }
        
        return 'landing';
    }

	/**
	* Controls setup helper
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function doProcess() {
        if($this->next()) {        	
            $this->setDBConfig(); // Set any posted variables
            $this->setDetails();
            if($this->doTest()) { // Test
				return 'confirm';
            } else {
                return 'error';
            }
        } else if($this->previous()) {
            return 'previous';
        } else if($this->confirm()) {
            $this->setDataFromSession("database"); // Set Session Information
            $this->setDBConfig(); // Set any posted variables
			return 'next';
        } else if($this->edit()) {
            $this->setDataFromSession("database"); // Set Session Information, since its an edit
            return 'landing';
        }
    }

	/**
	* Test database connectivity
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function doTest() {
    	if($this->match($this->dmspassword, $this->getPassword1()) != 0) {
    		$this->error = array("19"=>"Passwords do not match: " . $this->dmspassword." ". $this->getPassword1());
    		return false;
    	}
    	if($this->match($this->dmsuserpassword, $this->getPassword2()) != 0) {
    		$this->error = array("17"=>"Passwords do not match: " . $this->dmsuserpassword." ". $this->getPassword2());
    		return false;
    	}
    	if($this->dport == '') 
    		$con = $this->dbhandler->DBUtil($this->dhost, $this->duname, $this->dpassword, $this->dname);
    	else 
    		$con = $this->dbhandler->DBUtil($this->dhost.":".$this->dport, $this->duname, $this->dpassword, $this->dname);
        if (!$con) {
            $this->error = array("1"=>"Could not connect: " . $this->dbhandler->getErrors());
            return false;
        } else {
            return true;
        }
    }
    
    public function match($str1, $str2) {
    	return strcmp($str1, $str2);
    }
    
    public function getPassword1() {
    	return $_POST['dmspassword2'];
    }
    
    public function getPassword2() {
    	return $_POST['dmsuserpassword2'];
    }
	/**
	* Check if theres a database type
	*
	* @author KnowledgeTree Team
	* @params none
	* @access private
	* @return boolean database type or false
	*/
    private function getType() {
        if(isset($_POST['dtype'])) {
            return $_POST['dtype'];
        }

        return false;
    }

	/**
	* Set Errors if any were encountered
	*
	* @author KnowledgeTree Team
	* @params none
	* @access private
	* @return boolean
	*/
    private function setErrorsFromSession() {
        if(isset($_SESSION['database']['errors'])) {
            $this->error[] = $_SESSION['database']['errors'];
            
            return true;
        }

        return false;
    }

	/**
	* Set POST information
	*
	* @author KnowledgeTree Team
	* @params none
	* @access public
	* @return void
	*/
    public function setDBConfig() {
    	$this->dtype = $this->getPostSafe("dtype");
    	$this->dtypes = array("0"=>"mysql"); // TODO:multiple databases
        $this->dhost = $this->getPostSafe("dhost");
        $this->dport = $this->getPostSafe("dport");
        $this->dname = $this->getPostSafe("dname");
        $this->duname = $this->getPostSafe("duname");
        $this->dpassword = $this->getPostSafe("dpassword");
        $this->dmsname = $this->getPostSafe("dmsname");
        $this->dmsusername = $this->getPostSafe("dmsusername");
        $this->dmspassword = $this->getPostSafe("dmspassword");
        $this->dmsuserpassword = $this->getPostSafe("dmsuserpassword");
        $this->dbbinary = $this->getPostSafe("dbbinary");
        $this->tprefix = $this->getPostSafe("tprefix");
        $this->ddrop = $this->getPostBoolean("ddrop");
    }

	/**
	* Load default options on template from xml file
	*
	* @author KnowledgeTree Team
	* @params simple xml object $simplexml
	* @access public
	* @return void
	*/
    public function loadDefaults($simplexml) {
        if($simplexml) {
        	$this->temp_variables['dtype'] = "";
            $this->temp_variables['dtypes'] = array("0"=>"mysql"); // TODO:multiple databases
            $this->temp_variables['dname'] = (string) $simplexml->dname;
            $this->temp_variables['duname'] = (string) $simplexml->duname;
            $this->temp_variables['dhost'] = (string) $simplexml->dhost;
            $this->temp_variables['dport'] = (string) $simplexml->dport;
            $this->temp_variables['dpassword'] = '';
            $this->temp_variables['dmsname'] = '';
            $this->temp_variables['dmsusername'] = '';
            $this->temp_variables['dmspassword'] = '';
            $this->temp_variables['dmsuserpassword'] = '';
            $this->temp_variables['dbbinary'] = 'mysql';
            $this->temp_variables['tprefix'] = '';
            $this->temp_variables['ddrop'] = false;
        }
    }

	/**
	* Store options
	*
	* @author KnowledgeTree Team
	* @params SimpleXmlObject $simplexml
	* @access private
	* @return void
	*/
   private function setDetails() {
   		$this->temp_variables['dtype'] = $this->getPostSafe('dtype');
        $this->temp_variables['dtypes'] = array("0"=>"mysql"); // TODO:multiple databases;
        $this->temp_variables['dhost'] = $this->getPostSafe('dhost');
        $this->temp_variables['dport'] = $this->getPostSafe('dport');
        $this->temp_variables['dname'] = $this->getPostSafe('dname');
        $this->temp_variables['duname'] = $this->getPostSafe('duname');
        $this->temp_variables['dpassword'] = $this->getPostSafe('dpassword');
		$this->temp_variables['dmsname'] = $this->getPostSafe('dmsname');
		$this->temp_variables['dmsusername'] = $this->getPostSafe('dmsusername');
		$this->temp_variables['dmspassword'] = $this->getPostSafe('dmspassword');
		$this->temp_variables['dmsuserpassword'] = $this->getPostSafe('dmsuserpassword');;
		$this->temp_variables['dbbinary'] = $this->getPostSafe('dbbinary');
        $this->temp_variables['tprefix'] = $this->getPostSafe('tprefix');
        $this->temp_variables['ddrop'] = $this->getPostBoolean('ddrop');
    }
    
	/**
	* Extract database types
	*
	* @author KnowledgeTree Team
	* @access private
	* @params simple xml object $simplexml
	* @return array
	*/
    private function getTypes($xmlTypes) {
        $t = array();
        foreach ($xmlTypes->dtype as $key=>$val) {
            $t[] = (string) $val;
        }
        return $t;
    }

	/**
	* Read xml config file
	*
	* @author KnowledgeTree Team
	* @access private
	* @params none
	* @return object SimpleXmlObject
	*/
    private function readXml() {
        $simplexml = simplexml_load_file(CONF_DIR."databases.xml");

        return $simplexml;
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
	* Runs step install if required
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function installStep() {
		return $this->installDatabase();
    }
    
	/**
	* Helper
	*
	* @author KnowledgeTree Team
	* @params none
	* @access private
	* @return void
	*/
    private function installDatabase() {
    	if($this->dtype == '') {
    		$this->error[] = 'No database type selected';
    		return 'error';
    	}
        $this->{$this->dtype}();
    }

	/**
	* Helper
	*
	* @author KnowledgeTree Team
	* @params none
	* @access private
	* @return void
	*/
    private function mysql() {
        $con = $this->connectMysql();
        if($con) {
            if(!$this->createDB($con)) {
            	$this->error = array("20"=>"Could Create Database: " . $this->dbhandler->getErrors());
            	return false;
            }
            $this->closeMysql($con);
        }
    }

	/**
	* Connect to mysql
	*
	* @author KnowledgeTree Team
	* @params none
	* @access private
	* @return object mysql connection
	*/
    private function connectMysql() {
		$con = $this->dbhandler->DBUtil($this->dhost, $this->duname, $this->dpassword, $this->dname);
        if (!$con) {
            $this->error = array("2"=>"Could not connect: " . $this->dbhandler->getErrors());

            return false;
        }

        return $con;
    }

	/**
	* Helper
	*
	* @author KnowledgeTree Team
	* @params object mysql connection object $con
	* @access private
	* @return object mysql connection
	*/
    private function createDB($con) {
		if($this->usedb($con)) { // attempt to use the db
		    if($this->dropdb($con)) { // attempt to drop the db
		        if(!$this->create($con)) { // attempt to create the db
					$this->error = array("15"=>"Could create database: " . $this->dbhandler->getErrors());
					return false;// cannot overwrite database
		        }
		    } else {
		    	$this->error = array("14"=>"Could not drop database: " . $this->dbhandler->getErrors());
		    	return false;// cannot overwrite database
		    }
		} else {
		    if(!$this->create($con)) { // attempt to create the db
				$this->error = array("16"=>"Could create database: " . $this->dbhandler->getErrors());
				return false;// cannot overwrite database
		    }
		}
		if(!$this->createDmsUser($con)) {
			// TODO:Way to catch errors
		}
		if(!$this->createSchema($con)) {
			// TODO:Way to catch errors
		}
		if(!$this->populateSchema($con)) {
			// TODO:Way to catch errors
		}
		if(!$this->applyUpgrades($con)) {
			// TODO:Way to catch errors
		}
		return true;
    }

	/**
	* Create database
	*
	* @author KnowledgeTree Team
	* @params object mysql connection object $con
	* @access private
	* @return boolean
	*/
    private function create($con) {
        $sql = "CREATE DATABASE {$this->dname}";
        if ($this->dbhandler->query($sql, $con)) {
			
            return true;
        }

		return false;
    }

	/**
	* Attempts to use a db
	*
	* @author KnowledgeTree Team
	* @params mysql connection object $con
	* @access private
	* @return boolean
	*/
    private function usedb($con) {
		if($this->dbhandler->useBD($this->dname)) {
            return true;
        } else {
            $this->error = array("4"=>"Error using database: ".$this->dbhandler->getErrors()."");
            return false;
        }
    }

	/**
	* Attempts to drop table
	*
	* @author KnowledgeTree Team
	* @access private
	* @params mysql connection object $con
	* @return boolean
	*/
    private function dropdb($con) {
        if($this->ddrop) {
            $sql = "DROP DATABASE {$this->dname};";
			if(!$this->dbhandler->query($sql)) {
                $this->error = array("5"=>"Cannot drop database: ".$this->dbhandler->getErrors()."");
                return false;
            }
        } else {
            $this->error = array("6"=>"Cannot drop database: ".$this->dbhandler->getErrors()."");
            return false;
        }
        return true;
    }
        
	/**
	* Create dms user
	*
	* @author KnowledgeTree Team
	* @access private
	* @params none
	* @return boolean
	*/
    private function createDmsUser($con) {
    	if($this->dmsname == '' || $this->dmspassword == '') {
        	$command = "{$this->dbbinary} -u{$this->duname} -p{$this->dpassword} {$this->dname} < sql/user.sql";
        	return exec($command, $output);
    	} else {
			$user1 = "GRANT SELECT, INSERT, UPDATE, DELETE ON {$this->dname}.* TO {$this->dmsusername}@{$this->dhost} IDENTIFIED BY \"{$this->dmsuserpassword}\";";
			$user2 = "GRANT ALL PRIVILEGES ON {$this->dname}.* TO {$this->dmsname}@{$this->dhost} IDENTIFIED BY \"{$this->dmspassword}\";";
			if ($this->dbhandler->execute($user1) && $this->dbhandler->execute($user2)) {
            	return true;
        	} else {
        		$this->error = array("18"=>"Could not create users in database: ".$this->dbhandler->getErrors()."");
        		return false;
        	}
		}
        
    }
    
	/**
	* Create schema
	*
	* @author KnowledgeTree Team
	* @access private
	* @params none
	* @return boolean
	*/
    private function createSchema($con) {
        $command = "{$this->dbbinary} -u{$this->duname} -p{$this->dpassword} {$this->dname} < sql/structure.sql";
        return exec($command, $output);
    }

	/**
	* Populate database
	*
	* @author KnowledgeTree Team
	* @access private
	* @params none
	* @return boolean
	*/
    private function populateSchema($con) {
        $command = "{$this->dbbinary} -u{$this->duname} -p{$this->dpassword} {$this->dname} < sql/data.sql";
        return exec($command, $output);
    }

    private function applyUpgrades($con) {
    	// Database upgrade to version 3.6.1: Search ranking
//        $command = "{$this->dbbinary} -u{$this->duname} -p{$this->dpassword} {$this->dname} < sql/upgrades/search_ranking.sql";
//        exec($command, $output);
//        $command = "{$this->dbbinary} -u{$this->duname} -p{$this->dpassword} {$this->dname} < sql/upgrades/folders.sql";
//        exec($command, $output);
    }
	/**
	* Close connection if it exists
	*
	* @author KnowledgeTree Team
	* @access private
	* @params mysql connection object $con
	* @return void
	*/
    private function closeMysql($con) {
        try {
            $this->dbhandler->close();
        } catch (Exeption $e) {
            $this->error = array("13"=>"Could not close: " . $e);
        }
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
}
?>