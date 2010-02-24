<?php
/**
* Installer Index.
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
include("../wizard/path.php"); // Paths

/**
 * Auto loader to bind installer package
 *
 * @param string $class
 * @return void
 */
function __autoload($class) { // Attempt and autoload classes
	$class = strtolower(substr($class,0,1)).substr($class,1); // Linux Systems.
	if(file_exists(WIZARD_DIR."$class.php")) {
		require_once(WIZARD_DIR."$class.php");
	} elseif (file_exists(STEP_DIR."$class.php")) {
		require_once(STEP_DIR."$class.php");
	} elseif (file_exists(WIZARD_LIB."$class.php")) {
		require_once(WIZARD_LIB."$class.php");
	} elseif (file_exists(SERVICE_LIB."$class.php")) {
		require_once(SERVICE_LIB."$class.php");
	} elseif (file_exists(VALID_DIR."$class.php")) {
		require_once(VALID_DIR."$class.php");
	} else {
		if(preg_match('/Helper/', $class)) {
			require_once(HELPER_DIR."$class.php");
		}
		$base = preg_match("/Base/", $class, $matches);
		if($base) {
			$tmpClass = $class;
			$class = "base";
		}
		switch ($class) { // Could Need a class in another package
			case "template": // Load existing templating classes
				loadTemplate();
			break;
			case "base":
				loadBase($tmpClass);
			break;
		}
	}

}

function loadBase($class) {
	require_once("$class.php");
}

function loadTemplate() {
	require_once("../wizard/template.php");
	require_once("../wizard/lib".DS."helpers".DS."htmlHelper.php");	
}

class WizardBase {
	/**
	* Bypass flag
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var mixed
	*/
	protected $bypass = null;

	/**
	* Level of debugger
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var mixed
	*/
	protected $debugLevel = 0;

	/**
	* Reference to utility object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var boolean
	*/
	protected $util = null;

	/**
	* Display the wizard
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string
	* @return void
 	*/
	public function display() {

	}

	/**
	* Set bypass flag
	*
	* @author KnowledgeTree Team
	* @access public
	* @param boolean
	* @return void
 	*/
	public function setBypass($bypass) {
		$this->bypass = $bypass;
	}

	/**
	* Set debug level
	*
	* @author KnowledgeTree Team
	* @access public
	* @param boolean
	* @return void
 	*/
	public function setDebugLevel($debug) {
		define('DEBUG', $debug);
		$this->debugLevel = $debug;
	}

	/**
	* Set util reference
	*
	* @author KnowledgeTree Team
	* @access public
	* @param object installer utility
	* @return void
 	*/
	public function setIUtil($util) {
		$this->util = $util;
	}

	/**
	* Get bypass flag
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return boolean
 	*/
	public function getBypass() {
		return $this->bypass;
	}

	/**
	* Load default values
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return void
 	*/
	public function load() {
		return false;
	}

	/**
	* Run pre-installation system checks
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return mixed
 	*/
	public function systemChecks() {
		return false;
	}

	/**
	* Control all requests to wizard
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return void
 	*/
	public function dispatch() {
		return false;
	}
}

?>