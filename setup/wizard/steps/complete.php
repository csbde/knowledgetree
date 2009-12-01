<?php
/**
* Complete Step Controller.
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

class complete extends Step {

    /**
     * List of services to check
     *
     * @access private
     * @var array
     */
    private $services_check = 'tick';
    private $paths_check = 'tick';
    private $privileges_check = 'tick';
    private $database_check = 'tick';
    private $migrate_check = false;
    public $silent = true;
    private $servicesValidation = false;
    private $pathsSection = true;
    private $databaseSection = true;
    private $privilegesSection = true;

    function doStep() {
    	$this->temp_variables = array("step_name"=>"complete", "silent"=>$this->silent);
		$this->temp_variables['isCE'] = false;
		$type = $this->util->getVersionType();
		if($type == "community")
		 	$this->temp_variables['isCE'] = true;
        $this->doRun();
    	return 'landing';
    }

    function doRun() {
        $this->checkFileSystem(); // check filesystem (including location of document directory and logging)
        $this->checkDb(); // check database
        $this->checkServices(); // check services
        $this->checkInstallType(); // Set silent mode variables
        $this->storeSilent(); // Set silent mode variables
    }

    /**
     * Check all the system paths
     *
     */
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
        $config = $this->getDataFromSession("configuration"); // retrieve path information from session
        $paths = $config['paths'];
        $html = '<td><div class="%s"></div></td>'
              . '<td %s>%s</td>';
        $pathhtml = '<td><div class="%s"></div></td>'
                  . '<td>%s</td>'
                  . '<td %s>%s</td>';
        if(is_array($paths)) { // check paths are writeable
	        foreach ($paths as $path)
	        {
	            $output = '';
	            $path['path'] = $class = strtolower(substr($path['path'],0,1)).substr($path['path'],1); // Damn you windows
	            $result = $this->util->checkPermission($path['path']);
            	$output = sprintf($pathhtml, $result['class'], $path['path'],
                                     (($result['class'] == 'tick') ? 'class="green"' : 'class="error"' ),
                                     (($result['class'] == 'tick') ? 'Writeable' : 'Not Writeable' ));
	            $this->temp_variables[($path['setting'] != '') ? $path['setting'] : 'config'] = $output;
	            if($result['class'] != 'tick') {
	            	$this->pathsSection = false;
					$this->paths_check = $result['class'];
	            }
	            // for document location check
	            if ($path['setting'] == 'documentRoot') {
	                $docRoot = $path['path'];
	            }
	        }
        }
        // check document path internal/external to web root
        // compare SYSTEM_DIR to root path of documentRoot
        // NOTE preg_replace is to ensure all slash separators are the same (forward slash)
        $sysDir = preg_replace('/\\\\+|\/+/', '\/', SYSTEM_DIR);
        $docRoot = preg_replace('/\\\\+|\/+/', '\/', $docRoot);
        if (($pos = strpos($docRoot, $sysDir)) !== false) {
            $this->temp_variables['docLocation'] = '<td><div class="cross_orange"></div></td>'
                                                 . '<td class="warning" colspan="2">Your document directory is set to the default, which is inside the web root. '
                                                 . 'This may present a security problem if your documents can be accessed from the web, '
                                                 . 'working around the permission system in KnowledgeTree.</td>';
			$this->paths_check = 'cross_orange';
			$this->warnings[] = 'Move var directory';
			$this->pathsSection = false;
        }
        else {
            $this->temp_variables['docLocation'] = sprintf($html, 'tick', '', 'Your document directory is outside the web root.');
        }
    }

    /**
     * Check if a database connection can be made
     *
     */
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
        // make db connection - admin
        $this->util->dbUtilities->load($dbconf['dhost'], $dbconf['dport'], $dbconf['dmsname'], $dbconf['dmspassword'], $dbconf['dname']);
        $loaded = $this->util->dbUtilities->getDatabaseLink();
        if (!$loaded) {
			$this->database_check = 'cross';
            $this->temp_variables['dbConnectAdmin'] .= sprintf($html, 'cross', 'class="error"', 'Unable to connect to database (user: ' . $dbconf['dmsname'] . ')');
            $this->privilegesSection = false;
        }
        else
        {
            $this->temp_variables['dbConnectAdmin'] .= sprintf($html, 'tick', '', 'Database connectivity successful (user: ' . $dbconf['dmsname'] . ')');
        }

        // make db connection - user
        $this->util->dbUtilities->load($dbconf['dhost'], $dbconf['dport'], $dbconf['dmsusername'], $dbconf['dmsuserpassword'], $dbconf['dname']);
        $loaded = $this->util->dbUtilities->getDatabaseLink();
        // if we can log in to the database, check access
        // TODO check write access?
        if ($loaded)
        {
            $this->temp_variables['dbConnectUser'] .= sprintf($html, 'tick', '', 'Database connectivity successful (user: ' . $dbconf['dmsusername'] . ')');

            $qresult = $this->util->dbUtilities->query('SELECT COUNT(id) FROM documents');
            if (!$qresult)
            {
                $this->temp_variables['dbPrivileges'] .= '<td style="width:15px;"><div class="cross" style="float:left;"></div></td>'
                                                      .  '<td class="error" style="width:500px;">'
                                                      .  'Unable to do a basic database query. Error: ' . $this->util->dbUtilities->getLastError()                                                   .  '</td>';
				$this->privileges_check = 'cross';
				$this->privilegesSection = false;
            }
            else
            {
                $this->temp_variables['dbPrivileges'] .= sprintf($html, 'tick', '', 'Basic database query successful');

            }

            // check transaction support
            $sTable = 'system_settings';
            $this->util->dbUtilities->startTransaction();
            $this->util->dbUtilities->query('INSERT INTO ' . $sTable . ' (name, value) VALUES ("transactionTest", "1")');
            $this->util->dbUtilities->rollback();
            $res = $this->util->dbUtilities->query("SELECT id FROM $sTable WHERE name = 'transactionTest' LIMIT 1");
            if (!$res) {
                $this->temp_variables['dbTransaction'] .= sprintf($html, 'cross', 'class="error"', 'Transaction support not available in database');
                $this->privileges_check = 'cross';
                $this->privilegesSection = false;
            } else {
                $this->temp_variables['dbTransaction'] .= sprintf($html, 'tick', '', 'Database has transaction support');
            }
            $this->util->dbUtilities->query('DELETE FROM ' . $sTable . ' WHERE name = "transactionTest"');
        }
        else
        {
            $this->temp_variables['dbConnectUser'] .= sprintf($html, 'cross', 'class="error"', 'Unable to connect to database (user: ' . $dbconf['dmsusername'] . ')');
        }
    }

    /**
     * Check if all services are deactivated
     *
     */
    private function checkServices()
    {
        $services = new services();
        foreach ($services->getServices() as $serviceName) {
			$className = OS.$serviceName;
			$service = new $className();
			$service->load();
			$status = $services->serviceStarted($service);
			if($status) {
				$this->temp_variables[$serviceName."Status"] = 'tick';
			} else {
				$this->temp_variables[$serviceName."Status"] = 'cross_orange';
				$this->services_check = 'cross_orange';
			}
        }
		return true;
    }

    /**
     * Check the install type and store
     *
     */
    function checkInstallType() {
    	if ($this->util->isMigration()) {
    		$this->migrate_check = true;
    		$this->registerPlugins(); // Set silent mode variables
    	} else {
    		$this->migrate_check = false;
    	}
    }

    /**
     * Register extra commercial plugins
     *
     */
    private function registerPlugins() {

    }
    
    /**
     * Set all silent mode varibles
     *
     */
    private function storeSilent() {
    	$this->temp_variables['services_check'] = $this->services_check;
    	$this->temp_variables['paths_check'] = $this->paths_check;
    	$this->temp_variables['privileges_check'] = $this->privileges_check;
    	$this->temp_variables['database_check'] = $this->database_check;
    	$this->temp_variables['migrate_check'] = $this->migrate_check;
    	$this->temp_variables['servicesValidation'] = $this->servicesValidation;
    	$this->temp_variables['pathsSection'] = $this->pathsSection;
    	$this->temp_variables['databaseSection'] = $this->databaseSection;
    	$this->temp_variables['privilegesSection'] = $this->privilegesSection;
    }
}
?>