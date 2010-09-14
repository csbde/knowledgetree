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
* @package First Login
* @version Version 0.1
*/

require_once(realpath(dirname(__FILE__)  . '/../wizard/share/stepActionBase.php'));

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
		$step_class = "firstlogin".$this->makeCamelCase($this->stepName);

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

	public function getVars() {
		$left = $this->getLeftMenu();
		$vars['left'] = $left; // Set left menu
		$vars['fl_version'] = $this->properties['fl_version']; // Set version
		$vars['fl_type'] = $this->properties['fl_type']; // Set type
		if (KTPluginUtil::pluginIsActive('folder.templates.plugin')) { // Check if folder templates plugin is active
			$ft_dir = FolderTemplatesPlugin_RDIR . DIRECTORY_SEPARATOR . "KTFolderTemplates.php";
		}
		$vars['ft_handle'] = $ft_dir; // Set type
		return $vars;
	}

}

?>