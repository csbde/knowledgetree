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
	* Flag if step needs to be migrated
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array
	*/
    protected $runMigrate = true;
    
	/**
	* List of services to be migrated
	*
	* @author KnowledgeTree Team
	* @access private
	* @var array
	*/
    private $services = array('Lucene', 'Scheduler', 'OpenOffice');
    
	/**
	* Reference to utility object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
    protected $util;

    
	/**
	* Flag if services are already Stopped
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $alreadyStopped = false;
    
	/**
	* Flag if services are already Stopped
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $luceneStopped = false;
    
	/**
	* Flag if services are already Stopped
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $schedulerStopped = false;
    
	/**
	* Flag if services are already Stopped
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $openOfficeStopped = false;
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
    	$this->storeSilent();
    	if(!$this->inStep("services")) {
    		$this->doRun();
    		return 'landing';
    	}
        if($this->next()) {
			return 'next';
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
    public function alreadyStopped() {
    	$migrated = true;
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
    private function storeSilent() {
    	$this->temp_variables['alreadyStopped'] = $this->alreadyStopped;
    	$this->temp_variables['luceneStopped'] = $this->luceneStopped;
    	$this->temp_variables['schedulerStopped'] = $this->schedulerStopped;
    	$this->temp_variables['openOfficeStopped'] = $this->openOfficeStopped;
    	$this->temp_variables['serviceCheck'] = $this->serviceCheck;
    }
}
?>