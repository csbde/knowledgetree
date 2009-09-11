<?php
/**
* Services Step Controller.
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
* @package Migrateer
* @version Version 0.1
*/

class services extends Step 
{
	/**
	* List of errors encountered
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array
	*/
    protected $error = array();
    
	/**
	* Flag if step needs to be migrateed
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array
	*/
    protected $runMigrate = true;
    
	/**
	* List of services to be migrateed
	*
	* @author KnowledgeTree Team
	* @access private
	* @var array
	*/
    private $services = array('Lucene', 'Scheduler', 'OpenOffice');
    
	/**
	* Path to java executable
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
    protected $java;
    
	/**
	* Path to php executable
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
    protected $php;
    
	/**
	* Path to open office executable
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
	protected $soffice;

	/**
	* Reference to utility object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
    protected $util;

	/**
	* Minumum Java Version
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
    private $javaVersion = '1.5';
//    private $javaVersion = '1.7';

	/**
	* Java Migrateed 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $javaCheck = 'cross';

    
    public $providedJava = false;
    
	/**
	* Flag if services are already Migrateed
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $alreadyMigrateed = false;
    
	/**
	* Flag if services are already Migrateed
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $luceneMigrateed = false;
    
	/**
	* Flag if services are already Migrateed
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $schedulerMigrateed = false;
    
	/**
	* PHP Migrateed 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $phpCheck = 'cross_orange';
    
	/**
	* Java Bridge Migrateed 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $javaExtCheck = 'cross_orange';
    
	/**
	* Service Migrateed 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var array
	*/
    private $serviceCheck = 'tick';
    
	/**
	* Flag to store class information in session
	*
	* @author KnowledgeTree Team
	* @access public
	* @var boolean
	*/
    protected $storeInSession = true;
    
	/**
	* List of variables to be loaded to template
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $temp_variables;
    
	/**
	* Flag if step needs to run silently
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $silent = true;
    
	/**
	* Flag if bridge extension needs to be disabled
	*
	* @author KnowledgeTree Team
	* @access public
	* @var boolean
	*/
    private $disableExtension = false;
    
	/**
	* Flag, if java is specified and an error has been encountered
	*
	* @author KnowledgeTree Team
	* @access public
	* @var mixed
	*/
    private $javaExeError = '';
    
	/**
	* Holds path error, if java is specified
	*
	* @author KnowledgeTree Team
	* @access public
	* @var mixed
	*/
    private $javaExeMessage = '';
    
	/**
	* Holds path error, if php is specified
	*
	* @author KnowledgeTree Team
	* @access public
	* @var mixed
	*/
    private $phpExeError = '';
	/**
	* Constructs services object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
 	*/
    public function __construct() {
    	$this->temp_variables = array("step_name"=>"services", "silent"=>$this->silent);
    	$this->util = new MigrateUtil();
    }
    
	/**
	* Main control of services setup
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function doStep()
    {
    	if(!$this->inStep("services")) {
    		$this->doRun();
    		return 'landing';
    	}
        if($this->next()) {
	        // Check dependencies
	        $passed = $this->doRun();
	        $serv = $this->getDataFromSession("services");
//	        var_dump($conf);
//	        die;
            if($passed || $serv['providedJava'])
                return 'next';
            else
                return 'error';
        } else if($this->previous()) {
            return 'previous';
        }
        
        $passed = $this->doRun();
        return 'landing';
    }
    
	/**
	* Get service names
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return array
	*/
    public function getServices() {
    	return $this->services;
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
			$this->javaMigrateed();
			$this->temp_variables['java']['location'] = $this->java;
		}
    }
    
    /**
	* Run step
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function doRun() {
    	if($this->alreadyMigrateed()) {
    		$this->alreadyMigrateed = true;
    		$this->serviceCheck = 'tick';
    	} else {
	    	$this->php = $this->util->getPhp(); // Get java, if it exists
	    	$this->java = $this->util->getJava(); // Get java, if it exists
	    	$this->soffice = $this->util->getOpenOffice(); // Get java, if it exists
	    	$passedPhp = $this->phpChecks(); // Run Java Pre Checks
	    	$passedJava = $this->javaChecks(); // Run Java Pre Checks
	    	$passedOpenOffice = $this->openOfficeChecks(); // Run Java Pre Checks
	    	$errors = $this->getErrors(); // Get errors
			if(empty($errors) && $passedJava && $passedPhp && $passedOpenOffice) { // Migrate Service if there is no errors
				$this->migrateServices();
			} elseif ($passedPhp) { // Migrate Scheduler
				$this->migrateService('Scheduler');
			} elseif ($passedJava) { // Migrate Lucene
				$this->migrateService('Lucene');
			} elseif ($passedOpenOffice) { //Migrate OpenOffice
				$this->migrateService('OpenOffice');
			} else { // All Services not migrateed
				// TODO: What todo now?
			}
    	}
		$this->checkServiceStatus();
		$this->storeSilent(); // Store info needed for silent mode
		if(!empty($errors))
			return false;
		return true;
    }
    
	/**
	* A final check to see if services are still running,
	* incase they switched on and turned off.
	* 
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function checkServiceStatus() {
    	$serverDetails = $this->getServices();
		foreach ($serverDetails as $serviceName) {
			$className = OS.$serviceName;
			$service = new $className();
			$status = $this->serviceStatus($service);
			if($status != 'STARTED') {
				$msg = $service->getName()." Could not be added as a Service";
				$this->temp_variables['services'][] = array('class'=>'cross_orange', 'msg'=>$msg);
				$this->serviceCheck = 'cross_orange';
				$this->warnings[] = $msg;
			} else {
				if(WINDOWS_OS) {
					$this->temp_variables['services'][] = array('class'=>'tick', 'msg'=>$service->getName()." has been added as a Service"); }
				else {
					$this->temp_variables['services'][] = array('class'=>'tick', 'msg'=>$service->getName()." has been added and Started as a Service");
				}
			}
		}
    }
    
	/**
	* Checks if all services have been started already, 
	* incase the user lands on service page multiple times
	* 
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function alreadyMigrateed() {
    	$migrateed = true;
    	$serverDetails = $this->getServices();
		foreach ($serverDetails as $serviceName) {
			$className = OS.$serviceName;
			$service = new $className();
			$status = $this->serviceStatus($service);
			if(!$status) {
				return false;
			}
		}
		return true;
    }
    
    
    /**
	* Do some basic checks to help the user overcome java problems
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function javaChecks() {
		$this->zendBridgeNotMigrateed(); // Set bridge not migrateed
		$this->javaVersionInCorrect(); // Set version to incorrect
		$this->javaNotMigrateed(); // Set java to not migrateed
		$this->setJava(); // Check if java has been auto detected
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
    
    private function specifyJava() {
    	$this->javaExeError = true;
    }
    
    private function specifyPhp() {
    	$this->phpExeError = true;
    }
    
    private function phpChecks() {
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
    
    private function openOfficeChecks() {
    	return true;
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
    	$cmd = "$javaExecutable -version > output/outJV 2>&1 echo $!";
    	$response = $this->util->pexec($cmd);
    	if(file_exists(OUTPUT_DIR.'outJV')) {
    		$tmp = file_get_contents(OUTPUT_DIR.'outJV');
    		preg_match('/"(.*)"/',$tmp, $matches);
    		if($matches) {
	    		if($matches[1] < $this->javaVersion) { // Check Version of java
					$this->javaVersionInCorrect();
					$this->javaCheck = 'cross';
					$this->error[] = "Requires Java 1.5+ to be migrateed";
					
					return false;
	    		} else {
					$this->javaVersionCorrect();
					$this->javaMigrateed();
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
	    			$this->error[] = "Requires Java 1.5+ to be migrateed";
    			}
				
				
				return false;
    		}
    	}
    	
		$this->javaVersionInCorrect();
		$this->javaCheck = 'cross';
		$this->error[] = "Requires Java 1.5+ to be migrateed";
    	return false;
    }
    
    function detPhpSettings() {
    	// TODO: Better php handling
    	return true;
    	$phpExecutable = $this->util->phpSpecified();// Retrieve java bin
    	$cmd = "$phpExecutable -version > output/outPHP 2>&1 echo $!";
    	$response = $this->util->pexec($cmd);
    	if(file_exists(OUTPUT_DIR.'outPHP')) {
    		$tmp = file_get_contents(OUTPUT_DIR.'outPHP');
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
	* Attempts to use bridge and configure java settings
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function useBridge() {
		$zendBridge = $this->zendBridge(); // Find Zend Bridge
		if($zendBridge) { // Bridge migrateed implies java exists
			$this->zendBridgeMigrateed();
			if($this->checkZendBridge()) { // Make sure the Zend Bridge is functional
				$this->javaExtCheck = 'tick'; // Set bridge to functional
		    	$this->javaMigrateed(); // Set java to migrateed
	    		$javaSystem = new Java('java.lang.System');
		    	$version = $javaSystem->getProperty('java.version');
		    	$ver = substr($version, 0, 3);
		    	if($ver < $this->javaVersion) {
					$this->javaVersionInCorrect();
					$this->error[] = "Requires Java 1.5+ to be migrateed";
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
	* Check if Zend Bridge is enabled
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function zendBridge() {
		$mods = get_loaded_extensions();
		if(in_array('Zend Java Bridge', $mods)) 
			return true;
		else 
			return false;
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
	* Migrates services
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function migrateServices() {
		foreach ($this->getServices() as $serviceName) {
			$this->migrateService($serviceName);
		}
		
		return true;
    }

    /**
	* Migrates services helper
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function migrateService($serviceName) {
		$className = OS.$serviceName;
		$service = new $className();
		$status = $this->serviceHelper($service);
		if (!$status) {
			$this->serviceCheck = 'cross_orange';
		}
    }
    
   	/**
	* Migrates services
	*
	* @author KnowledgeTree Team
	* @param object
	* @access private
	* @return string
	*/
	private function serviceHelper($service) {
		$service->load(); // Load Defaults
		$response = $service->migrate(); // Migrate service
		$statusCheck = OS."ServiceMigrateed";
		return $this->$statusCheck($service);
	}
	
   	/**
	* Returns service status
	*
	* @author KnowledgeTree Team
	* @param object
	* @access private
	* @return string
	*/
	private function serviceStatus($service) {
		$statusCheck = OS."ServiceMigrateed";
		return $this->$statusCheck($service);
	}
	
   	/**
	* Check if windows service migrateed
	*
	* @author KnowledgeTree Team
	* @param object
	* @access public
	* @return boolean
	*/
	public function windowsServiceMigrateed($service) {
		$status = $service->status(); // Check if service has been migrateed
		if($status == '') { // Check service status
			return false;
		}
		return true;
	}
	
   	/**
	* Check if unix service migrateed
	*
	* @author KnowledgeTree Team
	* @param object
	* @access public
	* @return boolean
	*/
	public function unixServiceMigrateed($service) {
		$status = $service->status(); // Check if service has been migrateed
		if($status != 'STARTED') { // Check service status
			return false;
		}
		return true;
	}
	
   	/**
	* Starts all services
	*
	* @author KnowledgeTree Team
	* @param object
	* @access public
	* @return mixed
	*/
	public function migrateStep() {
		foreach ($this->getServices() as $serviceName) {
			$className = OS.$serviceName;
			$service = new $className();
			$status = $this->serviceStart($service);
		}
		return true;
	}
	
   	/**
	* Starts service
	*
	* @author KnowledgeTree Team
	* @param object
	* @access private
	* @return string
	*/
	private function serviceStart($service) {
		if(OS == 'windows') {
			$service->load(); // Load Defaults
			$service->start(); // Start Service
			return $service->status(); // Get service status
		}
	}
	
	/**
	* Returns services errors
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
	* Returns services warnings
	*
	* @author KnowledgeTree Team
	* @access public
	* @params none
	* @return array
	*/
    public function getWarnings() {
        return $this->warnings;
    }
    
	/**
	* Get the variables to be passed to the template
	*
	* @author KnowledgeTree Team
	* @access public
	* @return array
	*/
    public function getStepVars()
    {
        return $this->temp_variables;
    }
    
	/**
	* Store Java state as migrateed
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function javaMigrateed() {
		$this->temp_variables['java']['class'] = 'tick';
		$this->temp_variables['java']['found'] = "Java Runtime Migrateed";
    }
    
	/**
	* Store Java state as not migrateed
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function javaNotMigrateed() {
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
		$this->temp_variables['version']['found'] = "Java Version 1.5+ Migrateed";
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
		$this->temp_variables['version']['found'] = "Requires Java 1.5+ to be migrateed";
    }
    
	/**
    * Store Zend Bridge state as migrateed
    *
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
    */
    private function zendBridgeMigrateed() {
		$this->temp_variables['extensions']['class'] = 'tick';
		$this->temp_variables['extensions']['found'] = "Java Bridge Migrateed";
    }
    
	/**
    * Store Zend Bridge state as not migrateed
    * 
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
    */
    private function zendBridgeNotMigrateed() {
		$this->temp_variables['extensions']['class'] = 'cross_orange';
		$this->temp_variables['extensions']['found'] = "Zend Java Bridge Not Migrateed";
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
    
   	/**
    * Set all silent mode varibles
    *
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
    */
    private function storeSilent() {
    	$this->temp_variables['alreadyMigrateed'] = $this->alreadyMigrateed;
    	$this->temp_variables['luceneMigrateed'] = $this->luceneMigrateed;
    	$this->temp_variables['schedulerMigrateed'] = $this->schedulerMigrateed;
		$this->temp_variables['javaExeError'] = $this->javaExeError;
		$this->temp_variables['javaExeMessage'] = $this->javaExeMessage;
		$this->temp_variables['javaCheck'] = $this->javaCheck;
		$this->temp_variables['javaExtCheck'] = $this->javaExtCheck;
		// TODO : PHP detection
		$this->temp_variables['phpCheck'] = 'tick';//$this->phpCheck;
		$this->temp_variables['phpExeError'] = '';//$this->phpExeError;
		$this->temp_variables['serviceCheck'] = $this->serviceCheck;
		$this->temp_variables['disableExtension'] = $this->disableExtension;
		// TODO: Java checks are gettign intense
		$this->temp_variables['providedJava'] = $this->providedJava;
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
}
?>