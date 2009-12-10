<?php
/**
* Migrate Complete Step Controller.
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

class migrateComplete extends Step {
    /**
     * Flag if step needs to run silently
     * 
     * @access protected
     * @var boolean
     */
    protected $silent = true;
    
    /**
     * Name of BitRock Stack MySql
     * 
     * @access protected
     * @var string
     */
	protected $mysqlServiceName = "KTMysql";
	
    /**
     * Name of BitRock Stack MySql
     * 
     * @access protected
     * @var string
     */
	protected $zendMysql = "MySQL_ZendServer51";
	
	/**
	* Returns step state
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    function doStep() {
    	$this->temp_variables = array("step_name"=>"complete", "silent"=>$this->silent);
        return $this->doRun();
    }
    
    function doRun() {
    	$this->checkSqlDump();
		if(!$this->inStep("complete")) {
			
			return 'landing';
		}
        if($this->next()) {
        	if($this->checkZendMysql()) {
        		return 'binstall';
        	} else {
            	return 'error';
        	}
        }
        $this->removeInstallSessions();
        return 'landing';
    }
    
    private function removeInstallSessions() {
    	$isteps = array('dependencies', 'configuration', 'services', 'database', 'registration', 'install', 'complete');
    	foreach ($isteps as $step) {
	        if(isset($_SESSION['installers'][$step])) {
	        	$_SESSION['installers'][$step] = null;
	        }
    	}
    }
    
    private function checkSqlDump() {
    	$database = $this->getDataFromSession("database"); // Get installation directory
    	$sqlFile = $database['dumpLocation'];
		if(file_exists($sqlFile)) {
			$this->temp_variables['sql']['class'] = "tick";
			$this->temp_variables['sql']['name'] = "";//dms.sql
			$this->temp_variables['sql']['msg'] = $sqlFile;
			return true;
		} else {
			$this->temp_variables['sql']['class'] = "cross";
			$this->temp_variables['sql']['name'] = "dms.sql";
			$this->temp_variables['sql']['msg'] = "Data file has not been created";
			return false;
		}
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
			if($state == "STARTED")
				$running = true;
    	} else {
    		$installation = $this->getDataFromSession("installation"); // Get installation directory
    		$mysqlPid = $installation['location'].DS."mysql".DS."data".DS."mysqld.pid";
    		if(file_exists($mysqlPid))
    			$running = true;
    	}
    	if($running) {
    		$this->temp_variables['ktmysql']['class'] = "cross";
    		$this->temp_variables['ktmysql']['name'] = "KTMysql";
    		$this->temp_variables['ktmysql']['msg'] = "Service Running";
    		$this->error[] = "Service : KTMysql running.<br/>";
    		return false;
    	} else {
    		$this->temp_variables['ktmysql']['class'] = "tick";
    		$this->temp_variables['ktmysql']['name'] = "KTMysql";
    		$this->temp_variables['ktmysql']['msg'] = "Service has been uninstalled";
    		return true;
    	}
    }
    
    private function checkZendMysql() {
    	$running = false;
    	if(WINDOWS_OS) {
			$cmd = "sc query {$this->zendMysql}";
			$response = $this->util->pexec($cmd);
			if($response['out']) {
				$state = preg_replace('/^STATE *\: *\d */', '', trim($response['out'][3])); // Status store in third key
			}
			if($state == "STARTED" || $state == "RUNNING")
				$running = true;
    	} else {
    		//TODO : Read fomr my.cnf file
    		$mysqlPid = "/var/run/mysqld/mysqld.sock";
    		if(file_exists($mysqlPid))
    			$running = true;
    		$mysqlPid = "/var/run/mysqld/mysqld.pid";
    		if(file_exists($mysqlPid))
    			$running = true;
    	}
    	if($running) {
    		$this->temp_variables['zmysql']['class'] = "tick";
    		$this->temp_variables['zmysql']['name'] = "KTMysql";
    		$this->temp_variables['zmysql']['msg'] = "Service Running";
			return true;
    	} else {
    		$this->temp_variables['zmysql']['class'] = "cross";
    		$this->temp_variables['zmysql']['name'] = "Mysql";
    		$this->temp_variables['zmysql']['msg'] = "Service not running";
    		$this->error[] = "Service : KTMysql running.<br/>";
    		return false;
    	}
    }
    
    public function getErrors() {
    	return $this->error;
    }
}
?>