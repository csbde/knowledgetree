<?php
/**
* Installer Index.
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
require_once("../wizard/share/wizardBase.php");
require_once("installUtil.php");

class InstallWizard extends WizardBase {
	/**
	* Constructs installation wizard object
	*
	* @author KnowledgeTree Team
	* @access public
 	*/
	public function __construct(){}

	/**
	* Display the wizard
	*
	* @author KnowledgeTree Team
	* @access private
	* @param string
	* @return void
 	*/
	public function display($response = null) {
		if($response) {
			$ins = new Installer(); // Instantiate the installer
			$ins->resolveErrors($response); // Run step
		} else {
			$ins = new Installer(new wSession()); // Instantiate the installer and pass the session class
			$ins->step(); // Run step
		}
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
		touch(SYSTEM_DIR.'var'.DS.'bin'.DS."install.lock");
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
		if(file_exists(SYSTEM_DIR.'var'.DS.'bin'.DS."install.lock"))
			unlink(SYSTEM_DIR.'var'.DS.'bin'.DS."install.lock");
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
		if(isset($_GET['debug'])) {
			$this->setDebugLevel($_GET['debug']);
		} else {
			$this->setDebugLevel($this->debugLevel);
		}
		$this->setIUtil(new InstallUtil());
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
		$res = $this->util->checkStructurePermissions();
		if($res === true) return $res;
		switch ($res) {
			case "wizard":
					$this->util->error("Installer directory is not writable (KT_Installation_Directory/setup/wizard/)");
					return 'Installer directory is not writable (KT_Installation_Directory/setup/wizard/)';
				break;
			case "/":
					$this->util->error("System root is not writable (KT_Installation_Directory/)");
					return "System root is not writable (KT_Installation_Directory/)";
				break;
			default:
					return true;
				break;
		}
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
		if($this->getBypass() === "1") { // Helper to force install
			$this->removeInstallFile();
		} elseif ($this->getBypass() === "0") {
			$this->createInstallFile();
		}
		if ($this->util->finishInstall()) { // Check if the installer has completed
			$this->util->redirect('../../login.php');
		} elseif ($this->util->upgradeInstall()) { // Check if the upgrader needs to be accessed
				$this->util->redirect('../upgrade/index.php');
		}
		if(!$this->util->isSystemInstalled()) { // Check if the systems not installed
			if($this->util->loginSpecified()) { // Back to wizard from upgrader
				$this->util->redirect('../../control.php');
			} elseif($this->util->migrationSpecified()) { // Check if the migrator needs to be accessed
				$this->util->redirect('../migrate/index.php');
			} elseif ($this->util->upgradeSpecified()) { // Check if the upgrader needs to be accessed
				$this->util->redirect('../upgrade/index.php?action=installer');
			}
			$response = $this->systemChecks();
			if($response === true) {
				$this->display();
			} else {
				exit();
			}
		} else {
			$this->util->error("System has been installed  <a href='../../login.php' class='back' style='width:50px;float:none' class='back button_next'>Finish</a>");
		}
	}
}

$ic = new InstallWizard();
$ic->dispatch();
?>