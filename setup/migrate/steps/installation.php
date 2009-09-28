<?php
/**
* Migrate Step Controller. 
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

class installation extends step 
{
	/**
	* Flag to display confirmation page first
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
	public $displayFirst = false;
	
	/**
	* Flag to store class information in session
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $storeInSession = true;

	/**
	* List of paths
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $paths = array();

	/**
	* Flag if step needs to run silently
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $silent = false;
    
	private $location = '';
	private $dbSettings = array();
	private $ktSettings = array();
	private $urlPaths = array();
	
    function __construct() {
        $this->temp_variables = array("step_name"=>"installation", "silent"=>$this->silent);
    }

    public function doStep() {
    	$this->detectInstallation();
    	if(!$this->inStep("installation")) {
    		$this->setDetails();
    		$this->doRun();
    		
    		return 'landing';
    	}
        if($this->next()) {
        	if($this->doRun()) {
        	
            	return 'next';
        	} else {
            
            	return 'error';
        	}
        } else if($this->previous()) {
        	
            return 'previous';
        } else if($this->confirm()) {
        	$this->setDetails();
        	if($this->doRun()) {
            	return 'next';
        	}
        	return 'error';
        }
		$this->doRun();
        
        return 'landing'; 
    }

    public function detectInstallation() {
    	if(WINDOWS_OS) {
    		$path1 = "'C:\\Program Files\ktdms'";
    		$path2 = "'C:\\Program Files x86\ktdms'";
    		if(file_exists($path1))
    			$this->location = "C:\\Program Files\ktdms";
    		elseif (file_exists($path2))
    			$this->location = "C:\\Program Files x86\ktdms";
    	} else {
    		$path1 = "/opt/ktdms";
    		$path2 = "/var/www/ktdms";
    		if(file_exists($path1))
    			$this->location = $path1;
			elseif(file_exists($path2))
				$this->location = $path2;
    	}
    }
    
    public function doRun() {
		$ktInstallPath = isset($_POST['location']) ? $_POST['location']: '';
		if($ktInstallPath != '') {
			$this->location = $ktInstallPath;
			if(file_exists($ktInstallPath)) {
				$configPath = $ktInstallPath.DS."knowledgeTree".DS."config".DS."config-path";
				if(file_exists($configPath)) {
					$configFilePath = file_get_contents($configPath);
					if(file_exists($configFilePath)) {
						$this->readConfig($configFilePath);
						return true;
					} else {
						$this->error[] = "KT installation configuration file empty";
					}
				} else {
					$this->error[] = "KT installation configuration file not found";
				}
			} else {
				$this->error[] = "KT installation not found";
			}
		}
		$this->storeSilent();
		
		return false;
    }
    
    private function readConfig($path) {
    	$ini = new Ini($path);
    	$dbSettings = $ini->getSection('db');
    	$this->dbSettings = array('dbHost'=> $dbSettings['dbHost'],
    								'dbName'=> $dbSettings['dbName'],
    								'dbUser'=> $dbSettings['dbUser'],
    								'dbPass'=> $dbSettings['dbPass'],
    								'dbPort'=> $dbSettings['dbPort'],
    								'dbAdminUser'=> $dbSettings['dbAdminUser'],
    								'dbAdminPass'=> $dbSettings['dbAdminPass'],
    	);
		$ktSettings = $ini->getSection('KnowledgeTree');
		$this->ktSettings = array('fileSystemRoot'=> $ktSettings['fileSystemRoot'],
    	);
    	$urlPaths = $ini->getSection('urls');
		$this->urlPaths = array('varDirectory'=> $ktSettings['fileSystemRoot'].DS.'var',
    								'logDirectory'=> $ktSettings['fileSystemRoot'].DS.'var'.DS.'log',
    								'documentRoot'=> $ktSettings['fileSystemRoot'].DS.'var'.DS.'documentRoot',
    								'uiDirectory'=> $ktSettings['fileSystemRoot'].DS.'presentation'.DS.'lookAndFeel'.DS.'knowledgeTree',
    								'tmpDirectory'=> $ktSettings['fileSystemRoot'].DS.'var'.DS.'tmp',
    								'cacheDirectory' => $ktSettings['fileSystemRoot'].DS.'var'.DS.'cache',
    	);
    }
    
    private function setDetails() {
    	$inst = $this->getDataFromSession("installation");
    	if ($inst) {
    		$this->location = $inst['location'];
    	}
    }
    
    public function getStepVars() {
        return $this->temp_variables;
    }

    public function getErrors() {
        return $this->error;
    }
    
    public function storeSilent() {
    	$this->temp_variables['location'] = $this->location;
    	
    }
    
}
?>