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
* @package Installer
* @version Version 0.1
*/

class services extends Step 
{
	/**
	* List of errors encountered
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $error = array();
    
	/**
	* Flag if step needs to be installed
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $runInstall = true;
    
    private $services = array('Lucene', 'Scheduler');
    
    protected $java;
    
    protected $util;
    
    //private $response;
    
    private $javaVersion = '1.5';
    
	/**
	* Java Installed 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $java_check = 'cross';
    
	/**
	* Java Bridge Installed 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $java_ext_check = 'cross_orange';
    
	/**
	* Service Installed 
	*
	* @author KnowledgeTree Team
	* @access private
	* @var array
	*/
    private $service_check = 'tick';
    
	/**
	* Flag to store class information in session
	*
	* @author KnowledgeTree Team
	* @access public
	* @var boolean
	*/
    protected $storeInSession = false;
    
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
	* Constructs services object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
 	*/
    public function __construct() {
    	$this->temp_variables = array("step_name"=>"services", "silent"=>$this->silent);
    	$this->util = new InstallUtil();
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
        // Check dependencies
        $passed = $this->doRun();
        if($this->next()) {
            if($passed)
                return 'next';
            else
                return 'error';
        } else if($this->previous()) {
            return 'previous';
        }
        return 'landing';
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
    	$this->java = $this->util->getJava();
		if($this->javaChecks()) {
			$this->installService();
		} else { // Services not installed
			foreach ($this->services as $serviceName) {
				$this->temp_variables['services'][] = array('class'=>'cross', 'msg'=>$serviceName." Could not be added as a Service");
			}
		}
		$this->storeSilent();

		$errors = $this->getErrors();
		if(!empty($errors))
			return false;
		return true;
    }
    
    public function javaChecks() {
		$this->zendBridgeNotInstalled();
		$this->javaVersionInCorrect(); // Set version to incorrect
		$this->javaNotInstalled(); // Set java to not installed
    	if($this->util->javaSpecified()) {
    		$this->detSettings(); // AutoDetect java settings
    	} else {
			$this->useBridge(); // Use Bridge to get java settings
    	}

    }

    private function detSettings() {
    	$javaExecutable = $this->util->javaSpecified();// Retrieve java bin
    	$cmd = "$javaExecutable -version > output/outJV 2>&1 echo $!";
    	$response = $this->util->pexec($cmd);
    	if(file_exists(OUTPUT_DIR.'outJV')) {
    		$tmp = file_get_contents(OUTPUT_DIR.'outJV');
    		preg_match('/"(.*)"/',$tmp, $matches);
    		if($matches[1] < $this->javaVersion) { // Check Version of java
				$this->javaVersionInCorrect();
				$this->error[] = "Requires Java 1.5+ to be installed";
    		} else {
				$this->javaVersionCorrect();
				$this->javaInstalled();
				$this->java_check = 'tick';
    		}
    	}
    }
    
    private function useBridge() {
		$zendBridge = $this->zendBridge(); // Find Zend Bridge
		if($zendBridge) {
			$this->zendBridgeInstalled();
			if($this->checkZendBridge()) { // Make sure the Zend Bridge is functional
	    		$javaSystem = new Java('java.lang.System');
		    	$version = $javaSystem->getProperty('java.version');
		    	$ver = substr($version, 0, 3);
		    	if($ver < $this->javaVersion) {
					$this->javaVersionInCorrect();
					$this->error[] = "Requires Java 1.5+ to be installed";
		    	} else {
					$this->javaVersionCorrect();
		    	}
			} else {
				$this->javaVersionWarning();
				$this->zendBridgeWarning();
				$this->warnings[] = "Zend Java Bridge Error";
				$this->java_ext_check = 'cross_orange';
			}
		} else {
			$this->warnings[] = "Zend Java Bridge Required";
		}
    	if($this->java != '') { // Find Java JRE
			$this->javaInstalled();
			$this->java_check = 'tick';
    	} else {
    		$this->error[] = "Java runtime environment required";
    	}

    	return true;
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
	* Installs services
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function installService() {
		foreach ($this->services as $serviceName) {
			$className = OS.$serviceName;
			$service = new $className();
			$status = $this->serviceHelper($service);
			if ($status) {
				$this->temp_variables['services'][] = array('class'=>'tick', 'msg'=>$service->getName()." has been added as a Service");
			} else {
				$this->temp_variables['services'][] = array('class'=>'cross', 'msg'=>$service->getName()." Could not be added as a Service");
			}
		}
		
		return true;
    }

   	/**
	* Executes services
	*
	* @author KnowledgeTree Team
	* @param object
	* @access private
	* @return string
	*/
	private function serviceHelper($service) {
		$service->load(); // Load Defaults
		$response = $service->install(); // Install service
		$statusCheck = OS."ServiceInstalled";
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
	public function windowsServiceInstalled($service) {
		$status = $service->status(); // Check if service has been installed
		if($status != 'STOPPED') { // Check service status
			$this->error[] = $service->getName()." Could not be added as a Service";
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
			$this->error[] = $service->getName()." Could not be added as a Service";
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
		foreach ($this->services as $serviceName) {
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
    
    /* Helpers */
    
    private function javaInstalled() {
		$this->temp_variables['java']['class'] = 'tick';
		$this->temp_variables['java']['found'] = "Java Runtime Installed";
    }
    
    private function javaNotInstalled() {
		$this->temp_variables['java']['class'] = 'cross';
		$this->temp_variables['java']['found'] = "Java runtime environment required";
    }
    
    private function javaVersionCorrect() {
		$this->temp_variables['version']['class'] = 'tick';
		$this->temp_variables['version']['found'] = "Java Version 1.5+ Installed";
    }
    
    private function javaVersionWarning() {
		$this->temp_variables['version']['class'] = 'cross_orange';
		$this->temp_variables['version']['found'] = "Java Runtime Version Cannot be detected";
    }
    
    private function javaVersionInCorrect() {
		$this->temp_variables['version']['class'] = 'cross';
		$this->temp_variables['version']['found'] = "Requires Java 1.5+ to be installed";
    }
    
    private function zendBridgeInstalled() {
		$this->temp_variables['extensions']['class'] = 'tick';
		$this->temp_variables['extensions']['found'] = "Java Bridge Installed";
    }
    
    private function zendBridgeNotInstalled() {
		$this->temp_variables['extensions']['class'] = 'cross_orange';
		$this->temp_variables['extensions']['found'] = "Zend Java Bridge Not Installed";
    }
    
    private function zendBridgeWarning() {
		$this->temp_variables['extensions']['class'] = 'cross_orange';
		$this->temp_variables['extensions']['found'] = "Zend Java Bridge Error";
    }
    
    private function storeSilent() {
    	$this->temp_variables['java_check'] = $this->java_check;
    	$this->temp_variables['java_ext_check'] = $this->java_ext_check;
    	$this->temp_variables['service_check'] = $this->service_check;
    }
}
?>