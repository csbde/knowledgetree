<?php
/**
* Steap Action Controller.
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
* @package Upgrader
* @version Version 0.1
*/

class stepAction extends stepActionBase {
	/**
	* Constructs step action object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string class name of the current step
 	*/
    public function __construct($step) {
        $this->stepName = $step;
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
		$step_class = "upgrade".$this->makeCamelCase($this->stepName);
		
		return new $step_class();
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
        foreach ($step_vars as $key => $value) { // Set template variables
            $step_tpl->set($key, $value); // Load values to session
            if($this->action->storeInSession()) { // Check if class values need to be stored in session
            	$this->_loadValueToSession($this->stepName, $key, $value);
            }
        }
        $content = $step_tpl->fetch();
		$tpl = new Template("templates" . DS . "wizard.tpl");
        $vars = $this->getVars(); // Get template variables
        $tpl->set("vars", $vars); // Set template errors
		$tpl->set('content', $content);
		echo $tpl->fetch();
	}

	public function getVars() {
		$left = $this->getLeftMenu();
		$vars['left'] = $left; // Set left menu
		$vars['upgrade_version'] = $this->properties['upgrade_version']; // Set version
		$vars['upgrade_type'] = $this->properties['upgrade_type']; // Set type
		return $vars;
	}


}

?>