<?php
/**
* Services Step Controller.
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
	* Flag if step needs to be installed
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array
	*/
    protected $runInstall = true;
    
	/**
	* List of services to be installed
	*
	* @author KnowledgeTree Team
	* @access private
	* @var array
	*/
    private $services = array('java'=>'Lucene', 'php'=>'Scheduler', 'soffice'=>'OpenOffice');

	/**
	* Flag if services are already Installed
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $alreadyInstalled = false;
    
	/**
	* Service Installed 
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
	* Reference to lucene validation object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return object
 	*/
	public $luceneValidation;
	
	/**
	* Reference to open office validation object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return object
 	*/
	public $openofficeValidation;
	
	/**
	* Reference to scheduler validation object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return object
 	*/
	public $schedulerValidation;

	/**
	* List of binaries needed to start service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return object
 	*/
	public $binaries = array();
	
	/**
	* List of binaries needed to start service
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return object
 	*/
	public $servicesValidation = false;
	
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
    	$this->temp_variables = array("step_name"=>"services", "silent"=>$this->silent);
    	foreach ($this->getServices() as $service) {
    		$class = strtolower($service)."Validation";
			$this->$class = new $class();
    	}
    	if(!$this->inStep("services")) {
    		$this->doRun();
    		return 'landing';
    	}
        if($this->next()) {
	        $passed = $this->doRun(); // Check dependencies
	        $serv = $this->getDataFromSession("services");
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
	* Run step
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function doRun() {
    	if($this->alreadyInstalled()) {
    		$this->alreadyInstalled = true;
    		$this->serviceCheck = 'tick';
    	} else {
	    	foreach ($this->getServices() as $bin=>$service) {
	    		$class = strtolower($service)."Validation";
				$this->$class->preset(); // Sets defaults
				$className = OS.$service;
				$srv = new $className();
				$srv->load();
				$status = $this->serviceInstalled($srv);
				if($status != 'STARTED' || $status != 'STOPPED') {
					if(!WINDOWS_OS) { $binary = $this->$class->getBinary(); } // Get binary, if it exists
					$passed = $this->$class->binaryChecks(); // Run Binary Pre Checks
					$this->binaries[$bin] = $passed;
	    			if ($passed) { // Install Service
	    				$this->installService($service, $passed);
	    			}
				} else {
					$this->$class->installed();
				}
	    	}
    	}
		if($this->checkServiceStatus()) {
			$this->alreadyInstalled = true;
			$this->serviceCheck = 'tick';
		}
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
    	$allInstalled = true;
		foreach ($serverDetails as $serviceName) {
			$className = OS.$serviceName;
			$service = new $className();
			$service->load();
			$status = $this->serviceInstalled($service);
			if($status != 'STARTED') {
				$msg = $service->getName()." Could not be added as a Service";
				$this->temp_variables['services'][] = array('class'=>'cross_orange', 'msg'=>$msg);
				$this->serviceCheck = 'cross_orange';
				$this->warnings[] = $msg;
				$allInstalled = false;
			} else {
				if(WINDOWS_OS) {
					$this->temp_variables['services'][] = array('class'=>'tick', 'msg'=>$service->getName()." has been added as a Service"); }
				else {
					$this->temp_variables['services'][] = array('class'=>'tick', 'msg'=>$service->getName()." has been added and Started as a Service");
				}
			}
		}
		
		return $allInstalled;
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
    public function alreadyInstalled() {
    	$allInstalled = true;
    	$serverDetails = $this->getServices();
		foreach ($serverDetails as $serviceName) {
			$className = OS.$serviceName;
			$service = new $className();
			$service->load();
			$status = $this->serviceInstalled($service);
			$flag = strtolower(substr($serviceName,0,1)).substr($serviceName,1)."Installed";
			if(!$status) {
				$allInstalled = false;
				$this->$flag = false;
			} else {
				$this->$flag = true;
			}
		}

		return $allInstalled;
    }

    /**
	* Installs services
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function installServices() {
		foreach ($this->getServices() as $serviceName) {
			$this->installService($serviceName);
		}
		
		return true;
    }

    /**
	* Installs services helper
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function installService($serviceName, $binary) {
		$className = OS.$serviceName;
		$service = new $className();
		$status = $this->serviceHelper($service, $binary);
		if (!$status) {
			$this->serviceCheck = 'cross_orange';
		}
    }
    
   	/**
	* Installs services
	*
	* @author KnowledgeTree Team
	* @param object
	* @access private
	* @return string
	*/
	private function serviceHelper($service, $binary) {
		$service->load(array('binary'=>$binary)); // Load Defaults
		$response = $service->install(); // Install service
		$statusCheck = OS."ServiceInstalled";
		return $this->$statusCheck($service);
	}
	
   	/**
	* Helper to check if service is installed
	*
	* @author KnowledgeTree Team
	* @param object
	* @access public
	* @return string
	*/
	public function serviceInstalled($service) {
		$statusCheck = OS."ServiceInstalled";
		return $this->$statusCheck($service);
	}
	
   	/**
	* Helper to check if service is started
	*
	* @author KnowledgeTree Team
	* @param object
	* @access public
	* @return string
	*/
	public function serviceStarted($service) {
		$statusCheck = OS."ServiceStarted";
		return $this->$statusCheck($service);
	}
	
   	/**
	* Check if windows service installed
	*
	* @author KnowledgeTree Team
	* @param object
	* @access public
	* @return boolean
	*/
	public function windowsServiceStarted($service) {
		$status = $service->status(); // Check if service has been installed
		if($status != 'RUNNING') { // Check service status
			return false;
		}
		return true;
	}
	
   	/**
	* Check if unix service installed
	*
	* @author KnowledgeTree Team
	* @param object
	* @access public
	* @return boolean
	*/
	public function unixServiceStarted($service) {
		$status = $service->status(); // Check if service has been installed
		if($status != 'STARTED') { // Check service status
			return false;
		}
		return true;
	}
	
   	/**
	* Check if windows service installed
	*
	* @author KnowledgeTree Team
	* @param object
	* @access public
	* @return boolean
	*/
	public function windowsServiceInstalled($service) {
		$status = $service->status(); // Check if service has been installed
		if($status == '') { // Check service status
			return false;
		}
		return true;
	}
	
   	/**
	* Check if unix service installed
	*
	* @author KnowledgeTree Team
	* @param object
	* @access public
	* @return boolean
	*/
	public function unixServiceInstalled($service) {
		$status = $service->status(); // Check if service has been installed
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
	public function installStep() {
		if (!$this->util->isMigration()) { // Check if it is a migration
			foreach ($this->getServices() as $serviceName) {
				$className = OS.$serviceName;
				$service = new $className();
				$status = $this->serviceStart($service);
			}
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
    * Set all silent mode varibles
    *
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
    */
    public function storeSilent() {
    	foreach ($this->getServices() as $service) {
    		$class = strtolower($service)."Validation";
			$serv = $this->$class->storeSilent();
			$this->temp_variables = array_merge($this->temp_variables, $serv);
    	}
    	$this->temp_variables['alreadyInstalled'] = $this->alreadyInstalled;
    	$this->temp_variables['serviceCheck'] = $this->serviceCheck;
    	$this->temp_variables['binaries'] = $this->binaries;
    	$this->temp_variables['servicesValidation'] = $this->servicesValidation;
    }
    

	
	/** Migrate Access **/
	public function migrateGetServices() {
		$services = array();
		foreach ($this->getServices() as $serviceName) {
			$className = OS.$serviceName;
			$service = new $className();
			$service->load();
			$services[] = $service;
		}
		
		return $services;
	}
}

?>
