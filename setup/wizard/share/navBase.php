<?php
/**
* Installer Controller.
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
* @package Installer
* @version Version 0.1
*/

class NavBase {
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
	* List of installation steps as strings
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
    protected $stepClassNames = array();

	/**
	* List of installation steps as human readable strings
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
	protected $stepNames = array();

	/**
	* List of installation steps as human readable strings
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
	protected $stepObjects = array();

	/**
	* Order in which steps have to be installed
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
	protected $orders = array();

	/**
	* List of installation properties
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
	protected $properties = array();

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

    protected $action = '';

	/**
	* Read xml configuration file
	*
	* @author KnowledgeTree Team
	* @param string $name of config file
	* @access public
	* @return object
	*/
    public function readXml() {
		
    }

	/**
	* Checks if first step of installer
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function firstStep() {
        if(isset($_GET['step_name'])) {
            return false;
        }

        return true;
    }

	/**
	* Checks if first step of installer
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function firstStepPeriod() {
        if(isset($_GET['step_name'])) {
        	if($_GET['step_name'] != 'welcome')
            	return false;
        }

        return true;
    }

	/**
	* Returns next step
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function getNextStep() {
        return $this->getStepName(1);
    }

	/**
	* Returns previous step
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function getPreviousStep() {
        return $this->getStepName(-1);
    }

	/**
	* Returns the step name, given a position
	*
	* @author KnowledgeTree Team
	* @param integer $pos current position
	* @access public
	* @return string $name
	*/
    public function getStepName($pos = 0) {
        if($this->firstStep()) {
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
	* @access public
	* @return string
	*/
    public function proceed() {
        $step_name = $this->getNextStep();

        return $this->runStepAction($step_name);
    }

	/**
	* Executes previous step
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function backward() {
        $step_name = $this->getPreviousStep();

        return $this->runStepAction($step_name);
    }

	/**
	* Executes step landing
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function landing() {
        $step_name = $this->getStepName();

        return $this->runStepAction($step_name);
    }

	/**
	* Executes step based on step class name
	*
	* @author KnowledgeTree Team
	* @param string $step_name
	* @access public
	* @return string
	*/
    public function runStepAction($stepName) {
        $this->stepAction = new stepAction($stepName);
        $this->stepAction->setUpStepAction($this->getSteps(), $this->getStepNames(), $this->getStepConfirmation(), $this->stepDisplayFirst(), $this->getSession(), $this->getProperties());

        return $this->stepAction->doAction();
    }

    public function stepDisplayFirst() {
    	if($this->action == 'edit')
    		return false; //
    	$class = $this->stepAction->createStep(); // Get step class
    	return $class->displayFirst(); // Check if class needs to display first
    }

	/**
	* Set steps class names in string format
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return array
	*/
    public function getOrders() {
        return $this->orders;
    }

	/**
	* Set steps as names
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function xmlStepsToArray() {
    	if(isset($this->simpleXmlObj)) {
	        foreach($this->simpleXmlObj->steps->step as $d_step) {
	        	$step_name = (string) $d_step[0];
	            $this->stepClassNames[] = $step_name;
	        }
	        $this->loadToSession('stepClassNames', $this->stepClassNames);
    	}
    }

	/**
	* Set steps as human readable strings
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function xmlStepsNames() {
    	if(isset($this->simpleXmlObj)) {
	        foreach($this->simpleXmlObj->steps->step as $d_step) {
	        	$step_name = (string) $d_step[0];
	            $this->stepNames[$step_name] = (string) $d_step['name'];
	        }
	        $this->loadToSession('stepNames', $this->stepNames);
    	}
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
		return false;
    }

	/**
	* Set install properties
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function xmlProperties() {
		return false;
    }

	/**
	* Reset all session information on welcome landing
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function resetSessions() {
    	if($this->session) {
	    	if($this->firstStepPeriod()) {
	    		foreach ($this->getSteps() as $class) {
	    			$this->session->un_setClass($class);
	    		}
	    		foreach ($this->getStepNames() as $class) {
	    			$this->session->un_setClass($class);
	    		}
	    		foreach ($this->getOrders() as $class) {
	    			$this->session->un_setClass($class);
	    		}
	    	}
    	}
    }

    public function loadFromSessions() {
		return false;
    }

    public function loadNeeded() {
		return false;
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
		return false;
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
	* Return install properties
	*
	* @author KnowledgeTree Team
	* @param string
	* @access public
	* @return string
	*/
    public function getProperties() {
    	return $this->properties;
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
	* Display errors that are not allowing the installer to operate
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

    public function loadToSession($type, $values) {
    	if($values) {
    		$this->session->set($type , $values);
    	}
    }
}

?>
