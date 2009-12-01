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
    
    protected $mysqlServiceName = "KTMysql";
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
    	$this->services = $this->util->loadInstallServices(); // Use installer services class
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
    	$installation = $this->getDataFromSession("installation"); // Get installation directory
    	$this->conf = $installation['location'];
		if(!$this->alreadyUninstalled()) { // Pre-check if services are uninstalled
			$this->uninstallServices();
		}
		//$this->uninstallServices();
		return $this->checkServices();
    }
    	
    /**
     * Pre-check service status
     *
     * @return boolean If all services are not installed
     */
    private function alreadyUninstalled() {
    	$alreadyUninstalled = true;
    	foreach ($this->services as $serviceName) {
    		$className = OS.$serviceName;
    		$serv = $this->util->loadInstallService($className);
    		$serv->load();
    		$sStatus = $serv->status();
    		if($sStatus != '') {
    			return false;
    		}
    	}
    	if($this->mysqlRunning()) {
    		return false;
    	}
    	
    	return $alreadyUninstalled;
    }

    private function mysqlRunning() {
    	$running = false;
    	if(WINDOWS_OS) {
			$cmd = "sc query {$this->mysqlServiceName}";
			$response = $this->util->pexec($cmd);
			if($response['out']) {
				$state = preg_replace('/^STATE *\: *\d */', '', trim($response['out'][3])); // Status store in third key
			}
			if($state == "STARTED" || $state == 'RUNNING') {
				$running = true;
			}
    	} else {
    		$installation = $this->getDataFromSession("installation"); // Get installation directory
    		$mysqlPid = $installation['location'].DS."mysql".DS."data".DS."mysqld.pid";
    		if(file_exists($mysqlPid))
    			$running = true;
    	}
		return $running;
    }
    
    /**
     * Attempt to uninstall services
     *
     */
    private function uninstallServices() {
		$func = OS."Stop";
		//echo "$func";
		$this->$func();

		$this->shutdown();
    }

    /**
     * Attempt to uninstall unix services
     *
     */
    private function unixStop() {
    	$cmd = $this->conf['location']."/dmsctl.sh stop lucene";
    	$this->util->pexec($cmd);
    	$cmd = $this->conf['location']."/dmsctl.sh stop scheduler";
    	$this->util->pexec($cmd);
    	$cmd = $this->conf['location']."/dmsctl.sh stop soffice";
    	$this->util->pexec($cmd);
    }
    
    /**
     * Attempt to uninstall windows services
     *
     */
    private function windowsStop() {
    	$cmd = "sc stop KTScheduler";
    	$response = $this->util->pexec($cmd);
    	$cmd = "sc stop KTLucene";
    	$response = $this->util->pexec($cmd);
    	$cmd = "sc stop KTOpenoffice";
    	$response = $this->util->pexec($cmd);
    	$cmd = "sc delete KTOpenoffice";
    	$response = $this->util->pexec($cmd);
    	$cmd = "sc delete KTLucene";
    	$response = $this->util->pexec($cmd);
    	$cmd = "sc delete KTScheduler";
    	$response = $this->util->pexec($cmd);
    }
    
    /**
     * Attempt to uninstall services created by webserver
     *
     */
    private function shutdown() {
    	foreach ($this->services as $serviceName) {
    		$className = OS.$serviceName;
    		$serv = $this->util->loadInstallService($className);
    		$serv->load();
    		$sStatus = $serv->status();
    		if($sStatus != '') {
    			$serv->uninstall();
    		}
    	}
    	$this->shutdownMysql();
    }
    
    private function shutdownMysql() {
		$cmd = "sc stop {$this->mysqlServiceName}";
		$response = $this->util->pexec($cmd);
		
    }
    
    /**
 * Check if services are uninstall
     *
     */
    private function checkServices() {
    	foreach ($this->services as $serviceName) {
    		$className = OS.$serviceName;
    		$serv = $this->util->loadInstallService($className);
    		$serv->load();
    		$sStatus = $serv->status();
    		if($sStatus == 'STARTED' || $sStatus == 'RUNNING') {
    			$state = 'cross';
    			$this->error[] = "Service : {$serv->getName()} could not be uninstalled.<br/>";
    			$this->serviceCheck = 'cross';
    			$this->temp_variables['services'][$serv->getName()]['msg'] = "Service Running";
    		} elseif ($sStatus == 'STOPPED') { 
    			$state = 'cross';
    			$this->error[] = "Service : {$serv->getName()} could not be uninstalled.<br/>";
    			$this->serviceCheck = 'cross';
    			$this->temp_variables['services'][$serv->getName()]['msg'] = "Service Stopped, uninstall service";
    		} else {
    			$state = 'tick';
    			$this->temp_variables['services'][$serv->getName()]['msg'] = "Service has been uninstalled";
    		}
    		$this->temp_variables['services'][$serv->getName()]['class'] = $state;
    		$this->temp_variables['services'][$serv->getName()]['name'] = $serv->getHRName();
    	}
    	if(!$this->checkMysql()) {
    		return false;
    	}
    	if ($this->serviceCheck != 'tick') {
    		return false;
    	}
    	
    	return true;
    }
    
    /**
     * Check if services are uninstall
     *
     */
    private function checkMysql() {
    	$running = false;
    	if(WINDOWS_OS) {
			$cmd = "sc query {$this->mysqlServiceName}";
			$response = $this->util->pexec($cmd);
			if($response['out']) {
				$state = preg_replace('/^STATE *\: *\d */', '', trim($response['out'][3])); // Status store in third key
			}
			if($state == "STARTED" || $state == "RUNNING") {
				$running = true;
			}
    	} else {
    		$installation = $this->getDataFromSession("installation"); // Get installation directory
    		$mysqlPid = $installation['location'].DS."mysql".DS."data".DS."mysqld.pid";
    		if(file_exists($mysqlPid))
    			$running = true;
    	}
    	if($running) {
    		$this->temp_variables['services']['KTMysql']['class'] = "cross";
    		if(WINDOWS_OS) {
    			$this->temp_variables['services']['KTMysql']['name'] = "KnowledgeTree Mysql Service. (KTMysql)";
    		} else {
    			$this->temp_variables['services']['KTMysql']['name'] = "KnowledgeTree Mysql Service.";
    		}
    		$this->temp_variables['services']['KTMysql']['msg'] = "Service Running";
    		$this->error[] = "Service : KTMysql running.<br/>";
    		return false;
    	} else {
    		$this->temp_variables['services']['KTMysql']['class'] = "tick";
    		if(WINDOWS_OS) {
    			$this->temp_variables['services']['KTMysql']['name'] = "KnowledgeTree Mysql Service. (KTMysql)";
    		} else {
    			$this->temp_variables['services']['KTMysql']['name'] = "KnowledgeTree Mysql Service.";
    		}
    		$this->temp_variables['services']['KTMysql']['msg'] = "Service has been stopped";
    		return true;
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
    private function storeSilent() {
    	$this->temp_variables['alreadyUninstalled'] = $this->alreadyUninstalled;
    	$this->temp_variables['serviceCheck'] = $this->serviceCheck;
    	$this->temp_variables['msg'] = "Turn off KnowledgeTree Mysql Instance.";
    }
}
?>