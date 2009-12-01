<?php
/**
* Database Step Controller. 
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

class database extends Step 
{
	/**
	* Reference to Database object
	*
	* @author KnowledgeTree Team
	* @access public
	* @var object
	*/	
    public $util;
    
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
	* Location of database binaries.
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $mysqlDir; // TODO:multiple databases
    
	/**
	* Name of database binary.
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dbbinary = ''; // TODO:multiple databases
    
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
	* List of errors encountered
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $error = array();
    
	/**
	* List of errors used in template
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $templateErrors = array('dmspassword', 'dmsuserpassword', 'con', 'dname', 'dtype', 'duname', 'dpassword');
    
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
	* Flag if step needs to run silently
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $silent = true;
    
    private $salt = 'installers';
    
	/**
	* Main control of database setup
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function doStep() {
    	$this->temp_variables = array("step_name"=>"database", "silent"=>$this->silent);
    	$this->setErrorsFromSession();
    	$this->initErrors(); // Load template errors
        if($this->inStep("database")) {
            $res = $this->doProcess();
        	if($res) { // If theres a response, return it
            	return $res;
        	}
        }
        if($this->setDataFromSession("database")) { // Attempt to set values from session
            $this->setDetails(); // Set any posted variables
        } else {
        	$this->temp_variables['state'] = '';
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
            $this->setPostConfig(); // Set any posted variables
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
            $this->setPostConfig(); // Set any posted variables
			return 'next';
        } else if($this->edit()) {
            $this->setDataFromSession("database"); // Set Session Information, since its an edit
            $this->temp_variables['state'] = 'edit';
            
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
    		$this->error['dmspassword'] = "Passwords do not match: " . $this->dmspassword." ". $this->getPassword1();
    		return false;
    	}
    	if($this->match($this->dmsuserpassword, $this->getPassword2()) != 0) {
    		$this->error['dmsuserpassword'] = "Passwords do not match: " . $this->dmsuserpassword." ". $this->getPassword2();
    		return false;
    	}
    	$this->util->dbUtilities->load($this->dhost, $this->dport, $this->duname, $this->dpassword, $this->dname);
        if (!$this->util->dbUtilities->getDatabaseLink()) {
            $this->error['con'] = "Could not connect to the database, check username and password";
            return false;
        } else {
        	if ($this->dbExists()) { // Check if database Exists
        		$this->error['dname'] = 'Database Already Exists, specify a different name'; // Reset usage errors
            	return false;
        	} else {
        		$this->error = array(); // Reset usage errors
            	return true;
        	}
        }
    }
    
    public function dbExists() {
    	return $this->util->dbUtilities->useDb();
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
        if(isset($_SESSION[$this->salt]['database']['errors'])) {
            $this->error[] = $_SESSION[$this->salt]['database']['errors'];
            
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
    public function setPostConfig() {
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
	* @params object SimpleXmlObject
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
            $this->temp_variables['dmsname'] = (string) $simplexml->dmsadminuser;
            $this->temp_variables['dmsusername'] = (string) $simplexml->dmsuser;
            $this->temp_variables['dmspassword'] = (string) $simplexml->dmsaupass;
            $this->temp_variables['dmsuserpassword'] = (string) $simplexml->dmsupass;
            if(WINDOWS_OS) {
            	$this->temp_variables['dbbinary'] = 'mysql.exe';
            } else {
            	$this->temp_variables['dbbinary'] = 'mysql';
            }
            $this->temp_variables['tprefix'] = '';
            $this->temp_variables['ddrop'] = false;
        }
        
        return $this->temp_variables;
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
   		if($this->edit()) {
   			$this->temp_variables['state'] = 'edit';
   		} else {
   			$this->temp_variables['state'] = '';
   		}
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
	* @params object SimpleXmlObject
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
	* @access public
	* @params none
	* @return object SimpleXmlObject
	*/
    public function readXml() {
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
    		$this->error['dtype'] = 'No database type selected';
    		return 'error';
    	}
        if(!$this->{$this->dtype}()) {
        	return 'error';
        }
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
        // check for migrate lock file which indicates this is a migration and not a clean install
        if ($this->util->isMigration()) {
            if(!$this->migrateDB($con)) {
                $this->error['con'] = "Could not Create Database: ";
                return false;
            }
        }
        else {
            if(!$this->createDB()) {
            	$this->error['con'] = "Could not Create Database: ";
            	return false;
            }
        }
        $this->closeMysql();
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
		$this->util->dbUtilities->load($this->dhost, $this->dport, $this->duname, $this->dpassword, $this->dname);
    }
    
    /**
    * Helper
    *
    * @author KnowledgeTree Team
    * @params object mysql connection object $con
    * @access private
    * @return object mysql connection
    */
    private function migrateDB($con) {
        if($this->usedb()) { // attempt to use the db
            if($this->dropdb()) { // attempt to drop the db
                if(!$this->create()) { // attempt to create the db
                    $this->error['con'] = "Could not create database: ";
                }
            } else {
                $this->error['con'] = "Could not drop database: ";
            }
        } else {
            if(!$this->create()) { // attempt to create the db
                $this->error['con'] = "Could not create database: ";
            }
        }
        
        if(!$this->createDmsUser()) { // Create dms users
            
        }

		if(!$this->importExportedDB()) {
			$this->error['con'] = "Could not Import ";
		}
		
        return true;
    }

	/**
	* Helper
	*
	* @author KnowledgeTree Team
	* @params object mysql connection object $con
	* @access private
	* @return object mysql connection
	*/
    private function createDB() {
		if($this->usedb()) { // attempt to use the db
		    if($this->dropdb()) { // attempt to drop the db
		        if(!$this->create()) { // attempt to create the db
					$this->error['con'] = "Could not create database: ";
		        }
		    } else {
		    	$this->error['con'] = "Could not drop database: ";
		    }
		} else {
		    if(!$this->create()) { // attempt to create the db
				$this->error['con'] = "Could not create database: ";
		    }
		}
		$this->util->dbUtilities->clearErrors();
		if(!$this->createDmsUser()) { // Create dms users
			$this->error['con'] = "Could not create database users ";
		}
		if(!$this->createSchema()) {
			$this->error['con'] = "Could not create schema ";
		}
		if(!$this->populateSchema()) {
			$this->error['con'] = "Could not populate schema ";
		}
		$this->writeBinaries();
		$this->addServerPort();
		$this->util->getSystemIdentifier(); // ensure a guid was generated and is stored
		$this->reBuildPaths();
		
		return true;
    }

    private function addServerPort() {
		$conf = $this->util->getDataFromSession('configuration');
    	$port = $conf['server']['port']['value'];
		$iserverPorts = 'UPDATE config_settings SET value = "'.$port.'" where group_name = "server" and item IN("internal_server_port", "server_port");'; // Update internal server port    	
		$this->util->dbUtilities->query($iserverPorts);
    }
    
	/**
	* Create database
	*
	* @author KnowledgeTree Team
	* @params object mysql connection object $con
	* @access private
	* @return boolean
	*/
    private function create() {
        $sql = "CREATE DATABASE {$this->dname}";
        if ($this->util->dbUtilities->query($sql)) {	
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
    private function usedb() {
		if($this->util->dbUtilities->useDb()) {
            return true;
        } else {
            $this->error['con'] = "Error using database: {$this->dname}";
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
    private function dropdb() {
        if($this->ddrop) {
            $sql = "DROP DATABASE {$this->dname};";
			if(!$this->util->dbUtilities->query($sql)) {
                $this->error['con'] = "Cannot drop database: {$this->dname}";
                return false;
            }
        } else {
            $this->error['con'] = "Cannot drop database: {$this->dname}";
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
    private function createDmsUser() {
		$user1 = "GRANT SELECT, INSERT, UPDATE, DELETE ON {$this->dname}.* TO {$this->dmsusername}@{$this->dhost} IDENTIFIED BY \"{$this->dmsuserpassword}\";";
      	$user2 = "GRANT ALL PRIVILEGES ON {$this->dname}.* TO {$this->dmsname}@{$this->dhost} IDENTIFIED BY \"{$this->dmspassword}\";";
      if ($this->util->dbUtilities->query($user1) && $this->util->dbUtilities->query($user2)) {
              return true;
          } else {
            $this->error['con'] = "Could not create users for database: {$this->dname}";
            return false;
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
    private function createSchema() {
    	return $this->parse_mysql_dump($this->util->sqlInstallDir()."structure.sql");
    }

	private function parse_mysql_dump($url) {
	    $handle = fopen($url, "r");
	    $query = "";
		if ($handle) {
			while (!feof($handle)) {
    			$query.= fgets($handle, 4096);
    				if (substr(rtrim($query), -1) == ';') {
     					$this->util->dbUtilities->query($query);
     					$query = '';
    				}
			}
			fclose($handle);
		}
	    
		return true;
	}

	/**
	* Populate database
	*
	* @author KnowledgeTree Team
	* @access private
	* @params none
	* @return boolean
	*/
    private function populateSchema() {
    	return $this->parse_mysql_dump($this->util->sqlInstallDir()."data.sql");
    }

    private function importExportedDB() {
        $dbMigrate = $this->util->getDataFromPackage('migrate', 'database');
        $sqlFile = $dbMigrate['dumpLocation'];
    	$this->parse_mysql_dump($sqlFile);
    	$dropPluginHelper = "TRUNCATE plugin_helper;"; // Remove plugin helper table
    	$this->util->dbUtilities->query($dropPluginHelper);
		$this->addServerPort();
    	$updateExternalBinaries = 'UPDATE config_settings c SET c.value = "default" where c.group_name = "externalBinary";'; // Remove references to old paths
    	$this->util->dbUtilities->query($updateExternalBinaries);
    	$this->reBuildPaths();
		$this->writeBinaries(); // Rebuild some of the binaries
		$this->util->getSystemIdentifier(); // ensure a guid was generated and is stored

    	return true;
    }
    
    private function reBuildPaths() {
    	$conf = $this->util->getDataFromSession('configuration');
    	$paths = $conf['paths'];
    	foreach ($paths as $k=>$path) {
    		$sql = 'UPDATE config_settings SET value = "'.$path['path'].'" where item = "'.$k.'";';
    		$this->util->dbUtilities->query($sql);
    	}
	}
    
    private function writeBinaries() {
    	// if Windows, attempt to insert full paths to binaries
    	if (WINDOWS_OS) {
    	    $winBinaries = array('php' => array(0 => 'externalBinary', 1 => $this->util->useZendPhp() . 'php.exe'), 
    	    				  	 'python' => array(0 => 'externalBinary', 1 => SYSTEM_ROOT . 'openoffice\program\python.exe'), 
    	                      	 'java' => array(0 => 'externalBinary', 1 => SYSTEM_ROOT . 'java\jre\bin\java.exe'), 
    	                      	 'convert' => array(0 => 'externalBinary', 1 => SYSTEM_ROOT . 'bin\imagemagick\convert.exe'), 
    	                      	 'df' => array(0 => 'externalBinary', 1 => SYSTEM_ROOT . 'bin\gnuwin32\df.exe'), 
    	                      	 'zip' => array(0 => 'export', 1 => SYSTEM_ROOT . 'bin\zip\zip.exe'), 
    	                      	 'unzip' => array(0 => 'import', 1 => SYSTEM_ROOT . 'bin\unzip\unzip.exe'));
	    	
    	    if (INSTALL_TYPE == 'commercial' || true) {
    	    	$winBinaries['pdf2swf'] = array(0 => 'externalBinary', 1 => SYSTEM_ROOT . 'bin\swftools\pdf2swf.exe');
    	    }
    	    
    	    foreach ($winBinaries as $displayName => $bin)
    	    {
    	        // continue without attempting to set the path if we can't find the file in the specified location
    	        if (!file_exists($bin[1])) continue;
    	        
    	        // escape paths for insert/update query
    	        $bin[1] = str_replace('\\', '\\\\', $bin[1]);
    	        
    	        // instaView won't exist, must be inserted instead of updated
    	        // TODO this may need to be modified to first check for existing setting as with the convert step below; not necessary for 3.7.0.x
    	    	if ($displayName == 'pdf2swf') {
    	            $updateBin = 'INSERT INTO `config_settings` (group_name, display_name, description, item, value, default_value, type, options, can_edit) '
	    					   . 'VALUES ("' . $bin[0] . '", "' . $displayName . '", "The path to the SWFTools \"pdf2swf\" binary", "pdf2swfPath", '
	    					   . '"' . $bin[1] . '", "pdf2swf", "string", NULL, 1);';
    	        }
    	        // on a migration, the convert setting will not exist, so do something similar to the above, but first check whether it exists
    	    	else if ($displayName == 'convert') {
            		// check for existing config settings entry and only add if not already present
			        $sql = 'SELECT id FROM `config_settings` WHERE group_name = "externalBinary" AND item = "convertPath"';
			        $result = $this->util->dbUtilities->query($sql);
			        $output = $this->util->dbUtilities->fetchAssoc($result);
				    if(is_null($output)) {
    					$updateBin = 'INSERT INTO `config_settings` (group_name, display_name, description, item, value, default_value, type, options, can_edit) '
	    	            		   . 'VALUES ("' . $bin[0] . '", "' . $displayName . '", "The path to the ImageMagick \"convert\" binary", "convertPath", '
	    	            		   . '"' . $bin[1] . '", "convert", "string", NULL, 1)';
    	    		}
    	    		else {
	    	            $updateBin = 'UPDATE config_settings c SET c.value = "'. $bin[1] . '" '
	                               . 'where c.group_name = "' . $bin[0] . '" and c.display_name = "'.$displayName.'";';
    	    		}
    	        }
    	        else {
                    $updateBin = 'UPDATE config_settings c SET c.value = "'. $bin[1] . '" '
                               . 'where c.group_name = "' . $bin[0] . '" and c.display_name = "'.$displayName.'";';
    	        }
    	        
                $this->util->dbUtilities->query($updateBin);
            }
    	}
    	// if Linux?
    	else {
	    	$services = $this->util->getDataFromSession('services');
	    	$binaries = $services['binaries'];
    	    $python = "/usr/bin/python"; // Python default location
    	    if(file_exists($python)) {
    	    	$binaries['python'] = $python;
    	    }
	    	if($binaries) {
		    	foreach ($binaries as $k=>$bin) {
		    		if($k != 1) {
		    			$updateBin = 'UPDATE config_settings c SET c.value = "'.$bin.'" where c.group_name = "externalBinary" and c.display_name = "'.$k.'";';
						$this->util->dbUtilities->query($updateBin);
		    		}
		    	}
	    	}
    	}
    }
    
	/**
	* Close connection if it exists
	*
	* @author KnowledgeTree Team
	* @access private
	* @params mysql connection object $con
	* @return void
	*/
    private function closeMysql() {
        try {
            $this->util->dbUtilities->close();
        } catch (Exeption $e) {
            $this->error['con'] = "Could not close: " . $e;
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
    
}

?>