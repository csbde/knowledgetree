<?php
/**
* Installer Controller.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
*
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
* Contributor( s): ______________________________________
*/

/**
*
* @copyright 2008-2010, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Installer
* @version Version 0.1
*/

class Installer extends NavBase {
	/**
	* Constructs installation object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param object Session $session Instance of the Session object
 	*/
    public function __construct($session = null) {
        $this->session = $session;
    }

	/**
	* Read xml configuration file
	*
	* @author KnowledgeTree Team
	* @param string $name of config file
	* @access private
	* @return object
	*/
    public function readXml($name = "config.xml") {
    	try {
        	$this->simpleXmlObj = simplexml_load_file(CONF_DIR.INSTALL_TYPE."_$name");
    	} catch (Exception $e) {
    		$util = new InstallUtil();
    		$util->error("Error reading configuration file: $e");
    		exit();
    	}
    }


	/**
	* Set install properties
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    public function xmlProperties() {
    	if(isset($this->simpleXmlObj)) {
    		$this->properties['install_version'] = (string) $this->simpleXmlObj['version'];
    		$this->properties['install_type'] = (string) $this->simpleXmlObj['type'];
			$this->loadToSession('installProperties', $this->properties);
    	}
    }

	/**
	* Install steps
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function runStepsInstallers() {
    	$steps = $this->getOrders();
    	for ($i=1; $i< count($steps)+1; $i++) {
    		$this->installHelper($steps[$i]);
    	}

    	$this->completeInstall();
    }

	/**
	* Install steps helper
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function installHelper($className) {
    	$stepAction = new stepAction($className); // Instantiate a step action
    	$class = $stepAction->createStep(); // Get step class
    	if($class) { // Check if class Exists
	    	if($class->runInstall()) { // Check if step needs to be installed
				$class->setDataFromSession($className); // Set Session Information
				$class->setPostConfig(); // Set any posted variables
				$class->installStep(); // Run install step
	    	}
    	} else {
    		$util = new InstallUtil();
    		$util->error("Class File Missing in Step Directory: $className");
    		exit();
    	}
    }
    
	/**
	* Complete install cleanup process
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function completeInstall() {
    	touch(SYSTEM_DIR.'var'.DS.'bin'.DS."install.lock");
    	touch(SYSTEM_DIR.'var'.DS.'bin'.DS."firstlogin.lock");
    }
    
	/**
	* Set steps install order
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function xmlStepsOrders() {
    	if(isset($this->simpleXmlObj)) {
	        foreach($this->simpleXmlObj->steps->step as $d_step) {
				if(isset($d_step['order'])) {
					$step_name = (string) $d_step[0];
					$order = (string) $d_step['order'];
	            	$this->orders[$order] = $step_name; // Store step install order
	            }
	        }
	        $this->loadToSession('installOrders', $this->orders);
    	}
    }
    
    public function loadFromSessions() {
        $this->stepClassNames = $this->session->get('stepClassNames');
        if(!$this->stepClassNames) {
    		$this->xmlStepsToArray(); // String steps
    	}
    	$this->stepNames = $this->session->get('stepNames');
    	if(!$this->stepNames) {
    		$this->xmlStepsNames();
    	}
    	$this->orders = $this->session->get('installOrders');
    	if(!$this->orders) {
    		$this->xmlStepsOrders();
    	}
    	$this->properties = $this->session->get('installProperties');
    	if(!$this->properties) {
    		$this->xmlProperties();
    	}
    }
    
    public function loadNeeded() {
    	$this->readXml(); // Xml steps
    	// Make sure session is cleared
        $this->resetSessions();
        $this->loadFromSessions();
    	if(isset($_POST['Next'])) {
    		$this->action = 'next';
    		$this->response = 'next';
    	} elseif (isset($_POST['Previous'])) {
    		$this->action = 'previous';
    		$this->response = 'previous';
   	  	} elseif (isset($_POST['Confirm'])) {
   	  		$this->action = 'confirm';
    		$this->response = 'next';
    	} elseif (isset($_POST['Install'])) {
    		$this->action = 'install';
    		$this->response = 'next';
    	} elseif (isset($_POST['Edit'])) {
    		$this->action = 'edit';
    		$this->response = 'next';
    	} else {
    		$this->response = '';
    		$this->action = '';
    	}
    }
    
	/**
	* Main control to handle the flow of install
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function step() {
		$this->loadNeeded();
        switch($this->response) {
            case 'next':
        		$step_name = $this->getStepName();
        		$res = $this->runStepAction($step_name);
				if($res == 'next')
                	$this->proceed(); // Load next window
                elseif ($res == 'install') {
                	// Load the current steps vars into session
                	$vars = $this->stepAction->getStepVars();
                	$this->stepAction->loadToSes($vars);
                	$this->runStepsInstallers(); // Load landing
                	$this->proceed(); // Load next window
                } elseif ($res == 'confirm') {
                	if(!$this->stepDisplayFirst())
                		$this->stepConfirmation = true;
                	$this->landing();
                } elseif ($res == 'landing') {
					$this->landing();
                } else {
                }
            	break;
            case 'previous':
                $this->backward(); // Load previous page
            	break;
            default:
            	$this->landing();
            	break;
        }
        $this->stepAction->paintAction(); // Display step
    }
}

?>
