<?php
/**
* Upgrader Index.
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

require_once("../wizard/share/wizardBase.php");

class UpgradeWizard extends WizardBase {
	/**
	* Constructs upgradeation wizard object
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
			$ins = new Upgrader(); // Instantiate the upgrader
			$ins->resolveErrors($response); // Run step
		} else {
			$ins = new Upgrader(new session()); // Instantiate the upgrader and pass the session class
			$ins->step(); // Run step
		}
	}
	
	/**
	* Remove upgrade file
	*
	* @author KnowledgeTree Team
	* @access private
	* @param none
	* @return void
 	*/
	private function removeUpgradeFile() {
		unlink(SYSTEM_DIR.'var'.DS.'bin'.DS."upgrade.lock");
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
		$this->setIUtil(new UpgradeUtil());
	}
	
	/**
	* Run pre-upgradeation system checks
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
					$this->util->error("Upgrader directory is not writable (KT_Installation_Directory/setup/upgrade/)");
					return 'Upgrader directory is not writable (KT_Installation_Directory/setup/upgrade/)';
				break;
			case "/":
					$this->util->error("System root is not writable (KT_Installation_Directory/)");
					return "System root is not writable (KT_Installation_Directory/)";
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
		$response = $this->systemChecks();
		if($this->util->installationSpecified()) { // Check if the migrator needs to be accessed
			$this->util->redirect('../wizard/index.php?step_name=install_type');
		} elseif ($this->util->finishInstall()) { // Check if the installer has completed
			$this->util->redirect('../../login.php');
		}
		if($response === true) {
			$this->display();
		} else {
			exit();
		}
	}
}

$ic = new UpgradeWizard();
$ic->dispatch();
?>