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
    public $error = array();
    
	/**
	* Flag if step needs to be installed
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $runInstall = true;
    
    protected $services = array('Lucene', 'Scheduler');
    
    protected $java;
    
    protected $util;
    
    protected $response;
    
    protected $javaVersion = '1.5';
    
	/**
	* Flag to store class information in session
	*
	* @author KnowledgeTree Team
	* @access public
	* @var boolean
	*/
    protected $storeInSession = false;
    
    public $temp_variables;
    
	/**
	* Constructs services object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
 	*/
    public function __construct() {
    	$this->temp_variables = array("step_name"=>"services");
    	$this->util = new InstallUtil();
    	$this->setJava();
    }
    
    function tryJava1() {
    	$response = $this->util->pexec("java -version"); // Java Runtime Check
    	if(empty($response['out'])) {
    		return false;
    	}
    	$this->java = 'java';
    	$this->response = $response;
    	return true;
    }
    
    function tryJava2() {
    	$response = $this->util->pexec("java"); // Java Runtime Check
    	if(empty($response['out'])) {
    		return false;
    	}
    	$this->java = 'java';
    	$this->response = $response;
    	return true;
    }
    
    function tryJava3() {
    	$response = $this->util->pexec("whereis java"); // Java Runtime Check
    	if(empty($response['out'])) {
    		return false;
    	}
    	$broke = explode(' ', $response['out'][0]);
		foreach ($broke as $r) {
			$match = preg_match('/bin/', $r);
			if($match) {
				$this->java = preg_replace('/java:/', '', $r);
		    	$this->response = $response;
		    	return true;
			}
		}
    }
    
    function setJava() {
    	$response = $this->tryJava1();
    	if(!$response) {
    		$response = $this->tryJava2();
    		if(!$response) {
    			$response = $this->tryJava3();
    		}
    	}
    }
    
    function getJavaResponse() {
    	return $this->response;
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
		$java = false;
		$mods = get_loaded_extensions();
		foreach ($mods as $k=>$v) {
			if($v == 'Zend Java Bridge') {
				$java = true;
			}
		}
		if(!$java) {
    		$this->error[] = "Zend Java Bridge Required ";
    		return false;			
		}
    	if($this->java == '') {
			$this->error[] = "Java runtime environment required";
			return false;
    	}
    	$javaSystem = new Java('java.lang.System');
    	$version = $javaSystem->getProperty('java.version');
    	$ver = substr($version, 0, 3);
    	if($ver < $this->javaVersion) {
			$this->error[] = "This requires Java 1.5+ to be installed";
			return false;
    	}
		$this->installService();
//		if(count($this->getErrors() > 0))
//			return false;
		return true;
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
				$this->temp_variables['services'][] = $service->getName()." has been added as a Service";
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
}
?>