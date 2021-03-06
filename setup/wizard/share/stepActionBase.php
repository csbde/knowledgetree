<?php
/**
* Step Action Controller.
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

class StepActionBase {
	/**
	* Step class name
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var string
	*/
    protected $stepName = '';

	/**
	* Step names for classes
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array
	*/
    protected $stepClassNames = array();

	/**
	* Flag if step needs confirmation
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var boolean
	*/
    protected $displayConfirm = false;

	/**
	* Returns whether or not to display the confirmation page first
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var boolean
	*/
    protected $displayFirst = false;

	/**
	* List of properties
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var boolean
	*/
    protected $properties = array();

	/**
	* Reference to session object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var object Session
	*/
    protected $session = null;

	/**
	* Reference to current step object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var object class Step
	*/
    protected $action = null;

	/**
	* Helper to initialize step actions
	*
	* @author KnowledgeTree Team
	* @param string
	* @access public
	* @return string
	*/
    public function setUpStepAction($steps, $stepNames, $stepConfirmation, $stepDisplayFirst, $session, $properties) {
        $this->setSteps($steps);
        $this->setStepNames($stepNames);
        $this->setDisplayConfirm($stepConfirmation);
        $this->setDisplayFirst($stepDisplayFirst);
        $this->loadSession($session);
        $this->setProperties($properties);
    }

	/**
	* Sets steps class names in string format
	*
	* @author KnowledgeTree Team
	* @param array
	* @access public
	* @return void
	*/
    public function setSteps($stepClassNames) {
        $this->stepClassNames = $stepClassNames;
    }

	/**
	* Sets steps in human readable string format
	*
	* @author KnowledgeTree Team
	* @param array
	* @access public
	* @return void
	*/
    public function setStepNames($step_names) {
        $this->step_names = $step_names;
    }

	/**
	* Sets confirmation page flag
	*
	* @author KnowledgeTree Team
	* @param boolean
	* @access public
	* @return void
	*/
    public function setDisplayConfirm($displayConfirm) {
        $this->displayConfirm = $displayConfirm;
    }

	/**
	* Sets confirmation page first flag
	*
	* @author KnowledgeTree Team
	* @param boolean
	* @access public
	* @return void
	*/
    public function setDisplayFirst($displayFirst) {
        $this->displayFirst = $displayFirst;
    }

	/**
	* Sets session object
	*
	* @author KnowledgeTree Team
	* @param object Session
	* @access public
	* @return void
	*/
    public function loadSession($ses) {
        $this->session = $ses;
    }

	/**
	* Sets properties
	*
	* @author KnowledgeTree Team
	* @param array
	* @access public
	* @return void
	*/
    public function setProperties($properties) {
    	$this->properties = $properties;
    }

	/**
	* Main control to handle the steps actions
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function doAction() {
        if($this->stepName != '') {
			$this->action = $this->createStep();
			if(!$this->action) {
	        	$this->stepName = 'errors';
	        	$this->action = $this->createStep();
	        	$this->action->error = array('Class Files Missing in Step Directory');
			}
            $response = $this->action->doStep();
            if($this->action->storeInSession()) { // Check if class values need to be stored in session
            	$this->_loadStepToSession($this->stepName); // Send class to session
            }
            if ($response == 'error') {
            	$this->_handleErrors(); // Send Errors to session
            } else {
            	$this->_clearErrors($this->stepName); // Send Errors to session
            }
        } else {
        	$this->stepName = 'errors';
        	$this->action = $this->createStep();
        	$this->action->error = array('Class File Missing in Step Directory');
        }
        return $response;
    }

	/**
	* Instantiate a step.
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return object Step
	*/
    public function createStep() {
		$step_class = $this->makeCamelCase($this->stepName);
		return new $step_class();
    }

	/**
	* Converts string to camel case
	*
	* @author KnowledgeTree Team
	* @param string
	* @access public
	* @return string
	*/
    public function makeCamelCase($str) {
        $upper=ucwords($str);
        $str=str_replace('_', '', $upper);

        return $str;
    }

	/**
	* Converts string to human readable heading
	*
	* @author KnowledgeTree Team
	* @param string
	* @access public
	* @return string
	*/
    public function makeHeading($str) {
        $str = str_replace('_', ' ', $str);
        $str = ucwords($str);

        return $str;
    }

	/**
	* Returns current step name
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function getCurrentStepName() {
    	if($this->stepName != 'errors')
        	return $this->step_names[$this->stepName];
        return '';
    }

	/**
	* Returns left menu
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function getLeftMenu()
    {
    	$sideMenuElements = array();
        $active = false;
		if($this->stepClassNames) {
			$ele = array();
	        foreach ($this->stepClassNames as $k=>$step) {
	        	$ele['step'] = $step;
	            if($this->step_names[$step] != '') {
	            	$ele['name'] = $this->step_names[$step];
	            } else {
	            	$ele['name'] = $this->makeHeading($step);
	            }
	            if($step == $this->stepName) {
	            	$ele['class'] = 'current';
	            	$active = true;
	            } else {
	                if($active) {
	                	$ele['class'] = 'inactive';
	                } else {
	                	$ele['class'] = 'indicator';
	                }
	            }
	            $sideMenuElements[] = $ele;
	        }
		}
		$step_tpl = new Template("../wizard/templates/sidemenu.tpl"); // Create template
		$step_tpl->set("sideMenuElements", $sideMenuElements); // Set side menu elements
		$step_tpl->set("ajax", AJAX); // Set ajax state

        return $step_tpl->fetch();
    }

	/**
	* Returns confirmation page flag
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function displayConfirm() {
        return $this->displayConfirm;
    }

	/**
	* Returns whether or not to display the confirmation page first
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function displayFirst() {
    	return $this->displayFirst;
    }

	/**
	* Returns session object
	*
	* @author KnowledgeTree Team
	* @param object Session
	* @access public
	* @return object
	*/
    public function getSession() {
        return $this->session;
    }

	/**
	* Returns step tenplate content
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function paintAction() {
        $step_errors = $this->action->getErrors(); // Get errors
        $step_warnings = $this->action->getWarnings(); // Get warnings
                if($this->displayConfirm()) { // Check if theres a confirm step
            $template = "templates" . DS . "{$this->stepName}_confirm.tpl";
    	} else {
        	if($this->displayFirst()) {
            	$template = "templates" . DS . "{$this->stepName}_confirm.tpl";
        	} else {
        		$template = "templates" . DS . "{$this->stepName}.tpl";
        	}
    	}
        $step_tpl = new Template($template);
        $step_tpl->set("errors", $step_errors); // Set template errors
        $step_tpl->set("warnings", $step_warnings); // Set template warnings
        $step_vars = $this->action->getStepVars(); // Get template variables
        $step_tpl->set("step_vars", $step_vars); // Set template errors
		$this->loadToSes($step_vars);
		$this->loadToTpl($step_tpl, $step_vars);
		if(AJAX && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    		echo $step_tpl->fetch();
		} else {
	        $content = $step_tpl->fetch();
			$tpl = new Template("templates/wizard.tpl");
	        $vars = $this->getVars(); // Get template variables
	        $tpl->set("vars", $vars); // Set template errors
			$tpl->set('content', $content);
			echo $tpl->fetch();
		}
	}

	public function loadToSes($step_vars) {
		if($this->action->storeInSession()) { // Check if class values need to be stored in session
        	foreach ($step_vars as $key => $value) { // Set template variables
				$this->_loadValueToSession($this->stepName, $key, $value);
            }
        }
	}

	public function loadToTpl($step_tpl, $step_vars) {
		foreach ($step_vars as $key => $value) { // Set template variables
			$step_tpl->set($key, $value); // Load values to session
		}
	}

	public function getStepVars() {
		return $this->action->getStepVars();
	}

	public function getVars() {
		return array();
	}

    /**
     * Load class to session
     *
     * @author KnowledgeTree Team
     * @param string $class name of class
     * @param array $v array of values
     * @param boolean $overwrite whether or not to overwrite existing
     * @access public
     * @return void
     */
     public function _loadStepToSession($class, $v = array(), $overwrite = false) {
         if($this->session != null) {
             if($overwrite) {
                 $this->session->set($class , $v);
             } else {
                 if(!$this->session->is_set($class))
                    $this->session->set($class , $v);
            }
         } else {
			$this->stepName = 'errors';
        	$this->action = $this->createStep();
        	$this->action->error = array('Sessions Are Disabled');
         }
     }

    /**
     * Load class value to session
     *
     * @author KnowledgeTree Team
     * @param string $class name of class
     * @param string $k key value
     * @param string $v value to store
     * @param boolean $overwrite whether or not to overwrite existing
     * @access public
     * @return void
     */
     public function _loadValueToSession($class, $k, $v, $overwrite = false) {
         if($this->session != null) {
            $this->session->setClass($class, $k, $v);
         } else {
			$this->stepName = 'errors';
        	$this->action = $this->createStep();
        	$this->action->error = array('Sessions Are Disabled');
         }
     }

    /**
     * Load all class errors value to session
     *
     * @author KnowledgeTree Team
     * @param none
     * @access public
     * @return void
     */
     public function _handleErrors() {
        $step_errors = $this->action->getErrors(); // Get errors
        foreach ($step_errors as $key => $value) {
            $this->_loadErrorToSession($this->stepName, $key, $value); // Load values session
        }
     }

   /**
     * Remove all class errors value to session
     *
     * @author KnowledgeTree Team
     * @param none
     * @access public
     * @return void
     */
     public function _clearErrors($class) {
     	if($this->session) {
     		$this->session->clearErrors($class);
     	}
     }

    /**
     * Load class error value to session
     *
     * @author KnowledgeTree Team
     * @param string $class name of class
     * @param string $k key value
     * @param string $v value to store
     * @param boolean $overwrite whether or not to overwrite existing
     * @access public
     * @return void
     */
     public function _loadErrorToSession($class, $k, $v, $overwrite = false) {
         $k = "errors";
         if($this->session != null) {
            $this->session->setClassError($class, $k, $v);
         } else {
			$this->stepName = 'errors';
        	$this->action = $this->createStep();
        	$this->action->error = array('Sessions Are Disabled');
         }
     }
}

?>