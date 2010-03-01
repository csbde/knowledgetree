<?php
/**
* Template Step Controller.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
* @package First Login
* @version Version 0.1
*/

// Load needed system files.
require_once(SYSTEM_DIR . "config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class firstloginTemplates extends Step {
    /**
     * Flag if step needs to run silently
     * 
     * @access protected
     * @var boolean
     */
    protected $silent = true;
    
	/**
	* Returns step state
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    function doStep() {
    	$this->temp_variables = array(
    									"step_name"=>"templates",
    									"silent"=>$this->silent);
		if(!$this->inStep("templates")) { // Landing
			$this->doRun(); // Set folder structure templates
    		return 'landing';
    	}
    	if($this->next()) { // Next click
    		$this->applyTemplates(); // Apply folder template structures
    		return 'next'; // And go to next step
    	} else if($this->skip()) { // Skip Step
    		return 'next';
    	}
    	
    	$this->doRun(); // Set folder structure templates
        return 'landing'; // Default to landing
    }
    
    function doRun() {
		$this->temp_variables['aFolderTemplates'] = $this->getTemplates();
        return 'landing';
    }
    
    function applyTemplates() {
    	$templateId = KTUtil::arrayGet($_POST['data'], "templateId", 0);
    	if($templateId < 1) {
			$templateId = KTUtil::arrayGet($_GET, "templateId", 0);// Could be ajax call
    	}
    	if($templateId > 0) {
			if (KTPluginUtil::pluginIsActive('fs.FolderTemplatesPlugin.plugin')) { // Check if folder templates plugin is active
	            $oRegistry =& KTPluginRegistry::getSingleton();
	            $oPlugin =& $oRegistry->getPlugin('fs.FolderTemplatesPlugin.plugin'); // Get a handle on the plugin
	            return $oPlugin->firstLoginAction(1, $templateId);
			}
    	}
    	return false;
    }
    
    function getTemplates() {
		if (KTPluginUtil::pluginIsActive('fs.FolderTemplatesPlugin.plugin')) { // Check if folder templates plugin is active
            $oRegistry =& KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin('fs.FolderTemplatesPlugin.plugin'); // Get a handle on the plugin
            return $oPlugin->getFirstLoginTemplates();
		}
    }
    
    function getTemplateNodes() {
		if (KTPluginUtil::pluginIsActive('fs.FolderTemplatesPlugin.plugin')) { // Check if folder templates plugin is active
            $oRegistry =& KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin('fs.FolderTemplatesPlugin.plugin'); // Get a handle on the plugin
            return $oPlugin->getFirstLoginTemplates();
		}
    }
    
    public function getErrors() {
    	return $this->error;
    }
    
	/**
	* Stores varibles used by template
	*
	* @author KnowledgeTree Team
	* @params none
	* @access public
	* @return array
	*/
    public function getStepVars() {
        return $this->temp_variables;
    }
}
?>