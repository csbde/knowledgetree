<?php
/**
* Migrater Controller.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
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
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Migrater
* @version Version 0.1
*/

class Migrater {
	/**
	* Reference to simple xml object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var object SimpleXMLElement
	*/
    protected $simpleXmlObj = null;

	/**
	* Reference to step action object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var object StepAction
	*/
    protected $stepAction = null;

	/**
	* Reference to session object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var object Session
	*/
    protected $session = null;

	/**
	* List of migrateation steps as strings
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
    protected $stepClassNames = array();

	/**
	* List of migrateation steps as human readable strings
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
	protected $stepNames = array();

	/**
	* List of migrateation steps as human readable strings
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
	protected $stepObjects = array();

	/**
	* Order in which steps have to be migrated
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
	protected $migrateOrders = array();

	/**
	* List of migrateation properties
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
	protected $migrateProperties = array();

	/**
	* Flag if a step object needs confirmation
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var boolean
	*/
    protected $stepConfirmation = false;

	/**
	* Flag if a step object needs confirmation
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var boolean
	*/
    protected $stepDisplayFirst = false;

    private $migraterAction = '';

	/**
	* Constructs migrateation object
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
    private function _readXml($name = "config.xml") {
    	try {
        	$this->simpleXmlObj = simplexml_load_file(CONF_DIR.INSTALL_TYPE."_$name");
    	} catch (Exception $e) {
    		$util = new MigrateUtil();
    		$util->error("Error reading configuration file: $e");
    		exit();
    	}
    }

	/**
	* Checks if first step of migrater
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function _firstStep() {
        if(isset($_GET['step_name'])) {
            return false;
        }

        return true;
    }

	/**
	* Checks if first step of migrater
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function _firstStepPeriod() {
        if(isset($_GET['step_name'])) {
        	if($_GET['step_name'] != 'installation')
            	return false;
        }

        return true;
    }

	/**
	* Returns next step
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return string
	*/
    private function _getNextStep() {
        return $this->_getStepName(1);
    }

	/**
	* Returns previous step
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return string
	*/
    private function _getPreviousStep() {
        return $this->_getStepName(-1);
    }

	/**
	* Returns the step name, given a position
	*
	* @author KnowledgeTree Team
	* @param integer $pos current position
	* @access private
	* @return string $name
	*/
    private function _getStepName($pos = 0) {
        if($this->_firstStep()) {
            $step = (string) $this->simpleXmlObj->steps->step[0];
        } else {
            $pos += $this->getStepPosition();
            $step = (string) $this->simpleXmlObj->steps->step[$pos];
        }

        return $step;
    }

	/**
	* Executes next step
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return string
	*/
    private function _proceed() {
        $step_name = $this->_getNextStep();

        return $this->_runStepAction($step_name);
    }

	/**
	* Executes previous step
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return string
	*/
    private function _backward() {
        $step_name = $this->_getPreviousStep();

        return $this->_runStepAction($step_name);
    }

	/**
	* Executes step landing
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return string
	*/
    private function _landing() {
        $step_name = $this->_getStepName();

        return $this->_runStepAction($step_name);
    }

	/**
	* Executes step based on step class name
	*
	* @author KnowledgeTree Team
	* @param string $step_name
	* @access private
	* @return string
	*/
    private function _runStepAction($stepName) {
        $this->stepAction = new stepAction($stepName);
        $this->stepAction->setUpStepAction($this->getSteps(), $this->getStepNames(), $this->getStepConfirmation(), $this->stepDisplayFirst(), $this->getSession(), $this->getMigrateProperties());

        return $this->stepAction->doAction();
    }

    private function stepDisplayFirst() {
    	if($this->migraterAction == 'edit')
    		return false; //
    	$class = $this->stepAction->createStep(); // Get step class
    	return $class->displayFirst(); // Check if class needs to display first
    }

	/**
	* Set steps class names in string format
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return array
	*/
    private function _getMigrateOrders() {
        return $this->migrateOrders;
    }

	/**
	* Set steps as names
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function _xmlStepsToArray() {
    	if(isset($this->simpleXmlObj)) {
	        foreach($this->simpleXmlObj->steps->step as $d_step) {
	        	$step_name = (string) $d_step[0];
	            $this->stepClassNames[] = $step_name;
	        }
	        $this->_loadToSession('stepClassNames', $this->stepClassNames);
    	}
    }

	/**
	* Set steps as human readable strings
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function _xmlStepsNames() {
    	if(isset($this->simpleXmlObj)) {
	        foreach($this->simpleXmlObj->steps->step as $d_step) {
	        	$step_name = (string) $d_step[0];
	            $this->stepNames[$step_name] = (string) $d_step['name'];
	        }
	        $this->_loadToSession('stepNames', $this->stepNames);
    	}
    }

	/**
	* Set steps migrate order
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function _xmlStepsOrders() {
    	if(isset($this->simpleXmlObj)) {
	        foreach($this->simpleXmlObj->steps->step as $d_step) {
				if(isset($d_step['order'])) {
					$step_name = (string) $d_step[0];
					$order = (string) $d_step['order'];
	            	$this->migrateOrders[$order] = $step_name; // Store step migrate order
	            }
	        }
	        $this->_loadToSession('migrateOrders', $this->migrateOrders);
    	}
    }

	/**
	* Set migrate properties
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function _xmlMigrateProperties() {
    	if(isset($this->simpleXmlObj)) {
    		$this->migrateProperties['migrate_version'] = (string) $this->simpleXmlObj['version'];
    		$this->migrateProperties['migrate_type'] = (string) $this->simpleXmlObj['type'];
			$this->_loadToSession('migrateProperties', $this->migrateProperties);
    	}
    }

	/**
	* Migrate steps
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function _runStepsMigraters() {
    	$steps = $this->_getMigrateOrders();
    	for ($i=1; $i< count($steps)+1; $i++) {
    		$this->_migrateHelper($steps[$i]);
    	}

    	$this->_completeMigrate();
    }

	/**
	* Complete migrate cleanup process
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function _completeMigrate() {
    	touch("migrate");
    }

	/**
	* Migrate steps helper
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function _migrateHelper($className) {
    	$stepAction = new stepAction($className); // Instantiate a step action
    	$class = $stepAction->createStep(); // Get step class
    	if($class) { // Check if class Exists
	    	if($class->runMigrate()) { // Check if step needs to be migrated
				$class->setDataFromSession($className); // Set Session Information
				$class->setPostConfig(); // Set any posted variables
				$class->migrateStep(); // Run migrate step
	    	}
    	} else {
    		$util = new MigrateUtil();
    		$util->error("Class File Missing in Step Directory: $className");
    		exit();
    	}
    }

	/**
	* Reset all session information on welcome landing
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function _resetSessions() {
    	if($this->session) {
	    	if($this->_firstStepPeriod()) {
	    		foreach ($this->getSteps() as $class) {
	    			$this->session->un_setClass($class);
	    		}
	    		foreach ($this->getStepNames() as $class) {
	    			$this->session->un_setClass($class);
	    		}
	    		foreach ($this->_getMigrateOrders() as $class) {
	    			$this->session->un_setClass($class);
	    		}
	    	}
    	}
    }

    function _loadFromSessions() {
        $this->stepClassNames = $this->session->get('stepClassNames');
        if(!$this->stepClassNames) {
    		$this->_xmlStepsToArray(); // String steps
    	}
    	$this->stepNames = $this->session->get('stepNames');
    	if(!$this->stepNames) {
    		$this->_xmlStepsNames();
    	}
    	$this->migrateOrders = $this->session->get('migrateOrders');
    	if(!$this->migrateOrders) {
    		$this->_xmlStepsOrders();
    	}
    	$this->migrateProperties = $this->session->get('migrateProperties');
    	if(!$this->migrateProperties) {
    		$this->_xmlMigrateProperties();
    	}
    }

    private function loadNeeded() {
    	$this->_readXml(); // Xml steps
    	// Make sure session is cleared
        $this->_resetSessions();
        $this->_loadFromSessions();
    	if(isset($_POST['Next'])) {
    		$this->migraterAction = 'next';
    		$this->response = 'next';
    	} elseif (isset($_POST['Previous'])) {
    		$this->migraterAction = 'previous';
    		$this->response = 'previous';
   	  	} elseif (isset($_POST['Confirm'])) {
   	  		$this->migraterAction = 'confirm';
    		$this->response = 'next';
    	} elseif (isset($_POST['Migrate'])) {
    		$this->migraterAction = 'migrate';
    		$this->response = 'next';
    	} elseif (isset($_POST['Edit'])) {
    		$this->migraterAction = 'edit';
    		$this->response = 'next';
    	} elseif (isset($_POST['Install'])) {
    		$this->migraterAction = 'install';
    		$this->response = 'install';
    	} elseif (isset($_POST['BInstall'])) {
    		$this->migraterAction = 'binstall';
    		$this->response = 'binstall';
    	} else {
    		$this->response = '';
    		$this->migraterAction = '';
    	}
    }

	/**
	* Main control to handle the flow of migrate
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
        		$step_name = $this->_getStepName();
        		$res = $this->_runStepAction($step_name);
				if($res == 'next') {
                	$this->_proceed(); // Load next window
        		} elseif ($res == 'migrate') {
                	$this->_runStepsMigraters(); // Load landing
                	$this->_proceed(); // Load next window
                } elseif ($res == 'confirm') {
                	if(!$this->stepDisplayFirst())
                		$this->stepConfirmation = true;
                	$this->_landing();
                } elseif ($res == 'landing') {
					$this->_landing();
                } elseif ($res == 'binstall') {
                	$util = new MigrateUtil();
                	$util->redirect('../wizard/index.php?step_name=dependencies');
                } else {
                }
            	break;
            case 'previous':
				$this->_backward(); // Load previous page
				break;
            case 'install':
                $util = new MigrateUtil();
                $util->redirect('../wizard/index.php?step_name=installtype');
            	break;
//            case 'binstall':
//                $util = new MigrateUtil();
//                $util->redirect('../wizard/index.php?step_name=dependencies');
//            	break;
            default:
            	// TODO : handle silent
            	$this->_landing();
            	break;
        }
        $this->stepAction->paintAction(); // Display step
    }

	/**
	* Returns the step number
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return integer $pos
	*/
    public function getStepPosition() {
        $pos = 0;
        foreach($this->simpleXmlObj->steps->step as $d_step) {
            $step = (string) $d_step;
            if ($step == $_GET['step_name']) {
                   break;
            }
            $pos++;
        }
        if(isset($_GET['step'])) {
            if($_GET['step'] == "next")
                $pos = $pos+1;
            else
                $pos = $pos-1;
        }

        return $pos;
    }

	/**
	* Returns the step names for classes
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return array
	*/
    public function getSteps() {
        return $this->stepClassNames;
    }

	/**
	* Returns the steps as human readable string
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return array
	*/
    public function getStepNames() {
        return $this->stepNames;
    }

	/**
	* Returns whether or not a confirmation step is needed
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function getStepConfirmation() {
    	return $this->stepConfirmation;
    }

	/**
	* Return migrate properties
	*
	* @author KnowledgeTree Team
	* @param string
	* @access public
	* @return string
	*/
    public function getMigrateProperties() {
    	return $this->migrateProperties;
    }

	/**
	* Returns session
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function getSession() {
    	return $this->session;
    }

	/**
	* Dump of SESSION
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return array
	*/
    public function showSession() {
        echo '<pre>';
        print_r($_SESSION);
        echo '</pre>';
    }

	/**
	* Display errors that are not allowing the migrater to operate
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function resolveErrors($errors) {
    	echo $errors;
    	exit();
    }

    private function _loadToSession($type, $values) {
    	if($values) {
    		$this->session->set($type , $values);
    	}
    }
}

?>
