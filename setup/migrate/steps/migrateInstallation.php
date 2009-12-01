<?php
/**
* Migrate Step Controller.
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

class migrateInstallation extends step
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

	/**
	* Installation Settings
	*
	* @author KnowledgeTree Team
	* @access public
	* @var object
	*/
    private $settings = array();

    private $supportedVersion = '3.6.1';

    private $foundVersion = 'Unknown';

    private $versionError = false;

    public function doStep() {
		$this->temp_variables = array("step_name"=>"installation", "silent"=>$this->silent);
    	$this->detectInstallation();
    	if(!$this->inStep("installation")) {
    		$this->setDetails();
    		$this->doRun();
    		return 'landing';
    	}
        if($this->next()) {
        	if($this->doRun()) {
        		$this->setDetails();
            	return 'confirm';
        	} else {
            	return 'error';
        	}
        } else if($this->previous()) {
            return 'previous';
        } else if($this->confirm()) {
            	return 'next';
        }
		$this->doRun();

        return 'landing';
    }

    public function detectInstallation() {
    	if(WINDOWS_OS) {
    		$knownWindowsLocations = array("C:\Program Files\ktdms"=>"C:\Program Files\ktdms\knowledgeTree\config\config-path","C:\Program Files (x86)\ktdms"=>"C:\Program Files (x86)\ktdms\knowledgeTree\config\config-path","C:\ktdms"=>"C:\ktdms\knowledgeTree\config\config-path");
    		foreach ($knownWindowsLocations as $loc=>$configPath) {
    			if(file_exists($configPath))
    				$this->location = $loc;
    		}
    	} else {
    		$knownUnixLocations = array("/opt/ktdms"=>"/opt/ktdms/knowledgeTree/config/config-path","/var/www/ktdms"=>"/var/www/ktdms/knowledgeTree/config/config-path");
    		foreach ($knownUnixLocations as $loc=>$configPath) {
    			if(file_exists($configPath))
    				$this->location = $loc;
    		}
    	}
    }

    public function doRun() {
		if(!$this->readConfig()) {
			$this->storeSilent();
			return false;
		} else {
			$this->foundVersion = $this->readVersion();
			if($this->foundVersion) {
				$this->checkVersion();
			}
			$this->storeSilent();
			return true;
		}

    }

    public function checkVersion() {
		if($this->foundVersion < $this->supportedVersion) {
			$this->versionError = true;
			$this->error[] = "KnowledgeTree installation needs to be 3.6.1 or higher";
			return false;
		}

		return true;
    }

    public function readVersion() {
    	$verFile = $this->location."/knowledgeTree/docs/VERSION.txt";
    	if(file_exists($verFile)) {
			$foundVersion = file_get_contents($verFile);
			return $foundVersion;
    	} else {
			$this->error[] = "KnowledgeTree installation version not found";
    	}

		return false;
    }

    public function readConfig() {
		if(isset($_POST['location'])) {
			$ktInstallPath = $_POST['location'];
			if($ktInstallPath != '' || strlen($ktInstallPath) == 0) {
				$this->location = $ktInstallPath;

				return $this->configExists($ktInstallPath);
			}
		} else {

			return false;
		}

		return false;
    }

    private function configExists($ktInstallPath) {
		if(file_exists($ktInstallPath)) {
			$configPath = $ktInstallPath.DS."knowledgeTree".DS."config".DS."config-path";
			if(file_exists($configPath)) {
				$configFilePath = file_get_contents($configPath);
				if(file_exists($configFilePath)) { // For 3.7 and after
					$this->loadConfig($configFilePath);
					$this->storeSilent();

					return true;
				} else {
					$configFilePath = $ktInstallPath.DS."knowledgeTree".DS.$configFilePath; // For older than 3.6.2
					$configFilePath = trim($configFilePath);
					if(file_exists($configFilePath)) {
						$this->loadConfig($configFilePath);
						$this->storeSilent();

						return true;
					}
					$this->error[] = "KnowledgeTree installation configuration file empty";
				}
			} else {
				$this->error[] = "KnowledgeTree installation configuration file not found";
			}
		} else {
			$this->error[] = "Enter a Location";
		}

		return false;
    }

    private function loadConfig($path) {
		$this->util->iniUtilities->load($path);
		$dbSettings = $this->util->iniUtilities->getSection('db');
    	$this->dbSettings = array('dbHost'=> $dbSettings['dbHost'],
    								'dbName'=> $dbSettings['dbName'],
    								'dbUser'=> $dbSettings['dbUser'],
    								'dbPass'=> $dbSettings['dbPass'],
    								'dbAdminUser'=> $dbSettings['dbAdminUser'],
    								'dbAdminPass'=> $dbSettings['dbAdminPass'],
    	);
    	$ktSettings = $this->util->iniUtilities->getSection('KnowledgeTree');
		$froot = $ktSettings['fileSystemRoot'];
		if ($froot == 'default') {
			$froot = $this->location;
		}
		$this->ktSettings = array('fileSystemRoot'=> $froot);
    	$varDir = $froot.DS.'var';
		$this->urlPaths = array(
									array('name'=> 'Document Root', 'path'=> $froot.DS.'Documents'),
    	);
    	$this->dbSettings['dbPort'] = $this->util->getPort($this->location); // Add Port
    	$this->temp_variables['urlPaths'] = $this->urlPaths;
    	$this->temp_variables['ktSettings'] = $this->ktSettings;
    	$this->temp_variables['dbSettings'] = $this->dbSettings;
    }

    private function setDetails() {
    	$inst = $this->getDataFromSession("installation");
    	if ($inst) {
    		if(file_exists($this->location)) {
    			$this->location = $inst['location'];
    		}
    	}
    }

    public function getStepVars() {
        return $this->temp_variables;
    }

    public function getErrors() {
        return $this->error;
    }

    public function storeSilent() {
    	if($this->location==1) { $this->location = '';}
    	$this->temp_variables['location'] = $this->location;
    	$this->temp_variables['foundVersion'] = $this->foundVersion;
    	$this->temp_variables['versionError'] = $this->versionError;
    	$this->temp_variables['settings'] = $this->settings;
    }
}
?>