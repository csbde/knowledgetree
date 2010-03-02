<?php
/**
* Complete Step Controller.
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

class firstloginComplete extends Step {
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
    									"step_name"=>"complete", 
    									"silent"=>$this->silent);
    	if(!$this->inStep("complete")) {
			return  $this->doRun();
    	}
    	if($this->next()) { // Next click
    		$this->completeWizard(); // Apply folder template structures
    		return 'login'; // And go to next step
    	}
    	
        return 'landing';
    }
    
    function doRun() {
    	$ft_dir = "";
    	if (KTPluginUtil::pluginIsActive('fs.FolderTemplatesPlugin.plugin')) { // Check if folder templates plugin is active
            $oRegistry =& KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin('fs.FolderTemplatesPlugin.plugin'); // Get a handle on the plugin
            $ft_dir = $oPlugin->getDirs();
		}
		$this->temp_variables['ft_dir'] = $ft_dir;

        return 'landing';
    }
    
    function completeWizard() {
		$this->util->deleteFirstLogin();    	
    }
    
    public function getErrors() {
    	return $this->error;
    }
}
?>