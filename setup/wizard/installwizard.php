<?php
/**
* Installer Index.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
* Portions copyright The Jam Warehouse Software (Pty) Limited
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
include("path.php"); // Paths
require_once("install_util.php"); // Utility functions
require_once("session.php"); // Session management
require_once("template.inc"); // Template management
require_once("step_action.php"); // Step actions control

class InstallWizard {
	/**
	* Install bypass flag
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var mixed
	*/
	protected $bypass = null;

	/**
	* Reference to installer utility object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var boolean
	*/
	protected $iutil = null;
	
	/**
	* Constructs installation wizard object
	*
	* @author KnowledgeTree Team
	* @access public
 	*/
	public function __construct(){}

	/**
	* Check if system has been install
	*
	* @author KnowledgeTree Team
	* @access private
	* @param none
	* @return boolean
 	*/
	private function isSystemInstalled() {
		return $this->iutil->isSystemInstalled();
	}
	
	/**
	* Display the wizard
	*
	* @author KnowledgeTree Team
	* @access private
	* @param string
	* @return void
 	*/
	public function displayInstaller($response = null) {
		require("installer.php");
		if($response) {
			$ins = new Installer(); // Instantiate the installer
			$ins->resolveErrors($response); // Run step
		} else {
			$ins = new Installer(new Session()); // Instantiate the installer and pass the session class
			$ins->step(); // Run step
		}
	}
	
	/**
	* Set bypass flag
	*
	* @author KnowledgeTree Team
	* @access private
	* @param boolean
	* @return void
 	*/
	private function setBypass($bypass) {
		$this->bypass = $bypass;
	}
	
	/**
	* Set util reference
	*
	* @author KnowledgeTree Team
	* @access private
	* @param object installer utility
	* @return void
 	*/
	private function setIUtil($iutil) {
		$this->iutil = $iutil;
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
	* Bypass and force an install
	*
	* @author KnowledgeTree Team
	* @access private
	* @param none
	* @return boolean
 	*/
	private function bypass() {
		
	}
	
	/**
	* Create install file
	*
	* @author KnowledgeTree Team
	* @access private
	* @param none
	* @return void
 	*/
	private function createInstallFile() {
		touch("install");
	}
	
	/**
	* Remove install file
	*
	* @author KnowledgeTree Team
	* @access private
	* @param none
	* @return void
 	*/
	private function removeInstallFile() {
		unlink("install");
	}
	
	/**
	* Load default values
	*
	* @author KnowledgeTree Team
	* @access private
	* @param none
	* @return void
 	*/
	function load() {
		if(isset($_GET['bypass'])) {
			$this->setBypass($_GET['bypass']);
		}
		$this->setIUtil(new InstallUtil());
	}
	
	/**
	* Run pre-installation system checks
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return void
 	*/
	public function systemChecks() {
		$res = $this->iutil->checkStructurePermissions();
		if($res === true) return $res;
		switch ($res) {
			case "wizard":
					return 'Installer directory is not writable<br/>';
				break;
			case "/":
					return 'System root is not writable<br/>';
				break;
			default:
					return true;
				break;
		}
		
		return $res;
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
		$this->load();
		if($this->getBypass() === 1) {
			$this->createInstallFile();
		} elseif ($this->getBypass() === 0) {
			$this->removeInstallFile();
		}
		if(!$this->isSystemInstalled()) { // Check if the systems not installed
			$response = $this->systemChecks();
			if($response === true) {
				$this->displayInstaller();
			} else {
				$this->displayInstaller($response);
			}
		} else {
			// TODO: Die gracefully
			echo "System has been installed <a href='../../'>Goto Login</a>";
		}
	}
}

$ic = new InstallWizard();
$ic->dispatch();
?>