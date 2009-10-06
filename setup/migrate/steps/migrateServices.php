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
* @package Migrater
* @version Version 0.1
*/

class migrateServices extends Step 
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
    private $services = array();
    
	/**
	* Reference to utility object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
    protected $util;

    
	/**
	* Flag if services are already uninstalled
	*
	* @author KnowledgeTree Team
	* @access private
	* @var mixed
	*/
    private $alreadyUninstalled = false;
    
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
    
    protected $conf = array();
	/**
	* Constructs services object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
 	*/
    public function __construct() {
    	$this->temp_variables = array("step_name"=>"migrateServices", "silent"=>$this->silent);
    	$this->util = new MigrateUtil();
    }
    
    public function loadInstallUtil() {
    	require("../wizard/installUtil.php");
    	require("../wizard/steps/services.php");
    	$this->installServices = new services();
    }
    
    public function loadInstallServices() {
    	$this->services = $this->installServices->getServices();
    }
    
    private function loadInstallService($serviceName) {
    	require_once("../wizard/lib/services/$serviceName.php");
    	return new $serviceName();
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
    	$this->loadInstallUtil(); // Use installer utility class
    	$this->loadInstallServices(); // Use installer services class
    	$this->storeSilent();
    	if(!$this->inStep("services")) {
    		$this->doRun();
    		$this->storeSilent();
    		return 'landing';
    	}
        if($this->next()) {
        	if($this->doRun())
				return 'next';
        } else if($this->previous()) {
            return 'previous';
        }
        $this->doRun();
        $this->storeSilent();
        
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
		if(!$this->alreadyUninstalled()) { // Pre-check if services are stopped
			$this->stopServices();
			
		}
		$this->stopServices();
		return $this->checkServices();
    }
    	
    /**
     * Pre-check service status
     *
     * @return boolean If all services are not installed
     */
    public function alreadyUninstalled() {
    	$alreadyUninstalled = true;
    	foreach ($this->services as $serviceName) {
    		$className = OS.$serviceName;
    		$serv = $this->loadInstallService($className);
    		$serv->load();
    		$sStatus = $serv->status();
    		if($sStatus != '') {
    			return false;
    		}
    	}
    	
    	return $alreadyUninstalled;
    }

    /**
     * Attempt to stop services
     *
     */
    private function stopServices() {
    	$this->conf = $this->getDataFromSession("installation"); // Get installation directory
    	if($this->conf['location'] != '') {
	    	$cmd = $this->conf['location']."/dmsctl.sh stop"; // Try the dmsctl

	    	$res = $this->util->pexec($cmd);
    	}
		$this->shutdown();
    }

    public function shutdown() {
    	foreach ($this->services as $serviceName) {
    		$className = OS.$serviceName;
    		$serv = $this->loadInstallService($className);
    		$serv->load();
    		$sStatus = $serv->status();
    		if($sStatus != '') {
    			$res = $serv->uninstall();
    		}
    	}
    }
    
    public function checkServices() {
    	foreach ($this->services as $serviceName) {
    		$className = OS.$serviceName;
    		$serv = $this->loadInstallService($className);
    		$serv->load();
    		$sStatus = $serv->status();
    		if($sStatus == 'STARTED') {
    			$state = 'cross';
    			$this->error[] = "Service : {$serv->getName()} could not be stopped.<br/>";
    			$this->serviceCheck = 'cross';
    			
    		} else {
    			$state = 'tick';
    		}
    		$this->temp_variables['services'][$serv->getName()]['class'] = $state;
    		$this->temp_variables['services'][$serv->getName()]['name'] = $serv->getName();
    		$stopmsg = OS.'GetStopMsg';
    		$this->temp_variables['services'][$serv->getName()]['msg'] = $serv->$stopmsg($this->conf['location']);
    	}
    	if ($this->serviceCheck != 'tick') {
    		return false;
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
    	$this->temp_variables['alreadyUninstalled'] = $this->alreadyUninstalled;
    	$this->temp_variables['serviceCheck'] = $this->serviceCheck;
    }
}
?>