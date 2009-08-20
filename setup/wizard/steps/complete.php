<?php
/**
* Complete Step Controller. 
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

class complete extends Step {

    /**
	* Reference to Database object
	*
	* @author KnowledgeTree Team
	* @access private
	* @var object
	*/	
    private $_dbhandler = null;
    
    /**
     * List of services to check
     * 
     * @access private
     * @var array
     */
    private $_services = array('Lucene', 'Scheduler');
    
    public function __construct() {
        $this->_dbhandler = new dbUtil();
    }

    function configure() {
        $this->temp_variables = array("step_name"=>"complete");
    }

    function doStep() {
        $this->doRun();
    	return 'landing';
    }
    
    function doRun() {
        // check filesystem (including location of document directory and logging)
        $this->checkFileSystem();        
        // check database
        $this->checkDb();
        // check services
        $this->checkServices();
    }
    
    private function checkFileSystem()
    {
        // defaults
        $this->temp_variables['varDirectory'] = '';
        $this->temp_variables['documentRoot'] = '';
        $this->temp_variables['logDirectory'] = '';
        $this->temp_variables['tmpDirectory'] = '';
        $this->temp_variables['uploadDirectory'] = '';
        $this->temp_variables['config'] = '';
        $this->temp_variables['docLocation'] = '';
        
        $docRoot = '';

        // retrieve path information from session
        $config = $this->getDataFromSession("configuration");
        $paths = $config['paths'];

        $html = '<td><div class="%s"></div></td>'
              . '<td %s>%s</td>';
        $pathhtml = '<td><div class="%s"></div></td>'
                  . '<td>%s</td>'
                  . '<td %s>%s</td>';

        // check paths are writeable
        foreach ($paths as $path)
        {
            $output = '';
            $result = $this->checkPermission($path['path']);
            $output = sprintf($pathhtml, $result['class'], $path['path'], 
                                     (($result['class'] == 'tick') ? 'class="green"' : 'class="error"' ), 
                                     (($result['class'] == 'tick') ? 'Writeable' : 'Not Writeable' ));
            
            $this->temp_variables[($path['setting'] != '') ? $path['setting'] : 'config'] = $output;
            
            // for document location check
            if ($path['setting'] == 'documentRoot') {
                $docRoot = $path['path']; 
            }
        }
        
        // check document path internal/external to web root
        // compare SYSTEM_DIR to root path of documentRoot
        // NOTE preg_replace is to ensure all slash separators are the same (forward slash)
        $sysDir = preg_replace('/\\\\+|\/+/', '\/', SYSTEM_DIR);
        $docRoot = preg_replace('/\\\\+|\/+/', '\/', $docRoot);
        if (($pos = strpos($docRoot, $sysDir)) !== false) {
            $this->temp_variables['docLocation'] = sprintf($html, 'cross_orange', 'class="orange" colspan="2"', 
                                                                  'Your document directory is inside the web root. '
                                                                . 'This may present a security problem if your documents can be accessed from the web, '
                                                                . 'working around the permission system in KnowledgeTree.');
        }
        else {
            $this->temp_variables['docLocation'] = sprintf($html, 'tick', '', 'Your document directory is outside the web root.');
        }
    }
    
    private function checkDb()
    {
        // defaults
        $this->temp_variables['dbConnectAdmin'] = '';
        $this->temp_variables['dbConnectUser'] = '';
        $this->temp_variables['dbPrivileges'] = '';
        $this->temp_variables['dbTransaction'] = '';
        
        $html = '<td><div class="%s"></div></td>'
              . '<td %s>%s</td>';
        
        // retrieve database information from session
        $dbconf = $this->getDataFromSession("database");
        //print_r($dbconf);
        // make db connection - admin
        $loaded = $this->_dbhandler->load($dbconf['dhost'], $dbconf['dmsname'], $dbconf['dmspassword'], $dbconf['dname']);
        if (!$loaded) {
            $this->temp_variables['dbConnectAdmin'] .= sprintf($html, 'cross', 'class="error"', 'Unable to connect to database (user: ' . $dbconf['dmsname'] . ')');
        }
        else
        {
            $this->temp_variables['dbConnectAdmin'] .= sprintf($html, 'tick', '', 'Database connectivity successful (user: ' . $dbconf['dmsname'] . ')');
        }
        
        // make db connection - user
        $loaded = $this->_dbhandler->load($dbconf['dhost'], $dbconf['dmsusername'], $dbconf['dmsuserpassword'], $dbconf['dname']);
        // if we can log in to the database, check access
        // TODO check write access?
        if ($loaded)
        {
            $this->temp_variables['dbConnectUser'] .= sprintf($html, 'tick', '', 'Database connectivity successful (user: ' . $dbconf['dmsusername'] . ')');

            $qresult = $this->_dbhandler->query('SELECT COUNT(id) FROM documents');
            if (!$qresult)
            {
                $this->temp_variables['dbPrivileges'] .= sprintf($html, 'cross', 'class="error"', 'Unable to do a basic database query<br/>Error: ' 
                                                                                        . $this->_dbhandler->getLastError());
            }
            else
            {
                $this->temp_variables['dbPrivileges'] .= sprintf($html, 'tick', '', 'Basic database query successful');
            }
            
            // check transaction support
            $sTable = 'system_settings';
            $this->_dbhandler->startTransaction();
            $this->_dbhandler->query('INSERT INTO ' . $sTable . ' (name, value) VALUES ("transactionTest", "1")');
            $this->_dbhandler->rollback();
            $res = $this->_dbhandler->query("SELECT id FROM $sTable WHERE name = 'transactionTest' LIMIT 1");
            if (!$res) {
                $this->temp_variables['dbTransaction'] .= sprintf($html, 'cross_orange', 'class="orange"', 'Transaction support not available in database');
            } else {
                $this->temp_variables['dbTransaction'] .= sprintf($html, 'tick', '', 'Database has transaction support');
            }
            $this->_dbhandler->query('DELETE FROM ' . $sTable . ' WHERE name = "transactionTest"');
        }
        else
        {
            $this->temp_variables['dbConnectUser'] .= sprintf($html, 'cross', 'class="error"', 'Unable to connect to database (user: ' . $dbconf['dmsusername'] . ')');
        }
    }
    
    private function checkServices()
    {
        // defaults
        $this->temp_variables['luceneServiceStatus'] = '';
        $this->temp_variables['schedulerServiceStatus'] = '';
        
        return null;
        
        $processOrder = array();
        if (strtolower(OS) == 'windows')
        {
            $processOrder[] = 'Start';
            $processOrder[] = 'Stop';
        }
        else if (strtolower(OS) == 'unix')
        {
            $processOrder[] = 'Stop';
            $processOrder[] = 'Start';   
        }
            
        // loop through services and attempt to stop and then start them (in the case of Linux,) or start and stop them (in the case of Windows)
        // (Linux service is started after install, Windows is not)
        foreach ($this->_services as $serviceName)
        {
            // check installed
            $statusCheck = OS."ServiceInstalled";
            $className = OS.$serviceName;
			$service = new $className();
    		$installed = $this->$statusCheck($service);
            if ($installed) {
                
            }
            else {
                
            }
            
            // check start/stop - different orders dependant on system
            foreach($processOrder as $operation)
            {
//                $opExec = 'service' . $operation;
//                $opSuccess = $this->$opExec();
//                if ($opSuccess) {
//                    
//                }
//                else {
//                    
//                }
            }
        }
    }
    
    // FIXME these remaining functions are dupes of ones in steps/service.php and steps/configuration.php - abstract these to another class (parent or helper)
    //       and remove from here and original classes
    
    /**
     * Check whether a given directory / file path exists and is writable
     *
	 * @author KnowledgeTree Team
     * @access private
     * @param string $dir The directory / file to check
     * @param boolean $create Whether to create the directory if it doesn't exist
     * @return array The message and css class to use
     */
    private function checkPermission($dir, $create=false)
    {
        $exist = 'Directory does not exist';
        $write = 'Directory is not writable';
        $ret = array('class' => 'cross');

        if(!file_exists($dir)){
            if($create === false){
                $this->done = false;
                $ret['msg'] = $exist;
                return $ret;
            }
            $par_dir = dirname($dir);
            if(!file_exists($par_dir)){
                $this->done = false;
                $ret['msg'] = $exist;
                return $ret;
            }
            if(!is_writable($par_dir)){
                $this->done = false;
                $ret['msg'] = $exist;
                return $ret;
            }
            mkdir($dir, '0755');
        }

        if(is_writable($dir)){
            $ret['class'] = 'tick';
            return $ret;
        }

        $this->done = false;
        $ret['msg'] = $write;
        return $ret;
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
//	    print_r($service, true)."<BR>";
		$status = $service->status(); // Check if service has been installed
		echo "STAT: ".$status."<BR>";
		if($status != 'STOPPED') { // Check service status
			$this->error[] = $service->getName()." Could not be added as a WINDOWS Service";
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
			$this->error[] = $service->getName()." Could not be added as a UNIX Service";
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
	* Stops service
	*
	* @author KnowledgeTree Team
	* @param object
	* @access private
	* @return string
	*/
	private function serviceStop($service) {
		if(OS == 'windows') {
			$service->load(); // Load Defaults
			$service->stop(); // Stop Service
			return $service->status(); // Get service status
		}
	}
    
}
?>