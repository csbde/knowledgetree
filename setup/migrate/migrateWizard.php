<?php
/**
* Migrater Index.
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
* @package Migrater
* @version Version 0.1
*/

require_once("../wizard/share/wizardBase.php");

class MigrateWizard extends WizardBase {
	/**
	* Constructs migrateation wizard object
	*
	* @author KnowledgeTree Team
	* @access public
 	*/
	public function __construct(){}

	/**
	* Check if system has been migrate
	*
	* @author KnowledgeTree Team
	* @access private
	* @param none
	* @return boolean
 	*/
	private function isSystemMigrated() {
		return $this->util->isSystemMigrated();
	}

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
			$ins = new Migrater(); // Instantiate the migrater
			$ins->resolveErrors($response); // Run step
		} else {
			$ins = new Migrater(new Session()); // Instantiate the migrater and pass the session class
			$ins->step(); // Run step
		}
	}

	/**
	* Create migrate file
	*
	* @author KnowledgeTree Team
	* @access private
	* @param none
	* @return void
 	*/
	private function createMigrateFile() {
		touch(SYSTEM_DIR.'var'.DS.'bin'.DS."migrate.lock");
	}

	/**
	* Remove migrate file
	*
	* @author KnowledgeTree Team
	* @access private
	* @param none
	* @return void
 	*/
	private function removeMigrateFile() {
		unlink(SYSTEM_DIR.'var'.DS.'bin'.DS."migrate.lock");
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
		$this->setIUtil(new MigrateUtil());
	}

	/**
	* Run pre-migrateation system checks
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
					$this->util->error("Migrater directory is not writable (KT_Installation_Directory/setup/migrate/)");
					return 'Migrater directory is not writable (KT_Installation_Directory/setup/migrate/)';
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
		if($this->getBypass() === "1") {
			$this->removeMigrateFile();
		} elseif ($this->getBypass() === "0") {
			$this->createMigrateFile();
		}
		if(!$this->isSystemMigrated()) { // Check if the systems not migrated
			$response = $this->systemChecks();
			if($response === true) {
				$this->display();
			} else {
				exit();
			}
		} else {
			$this->util->error("System has been migrated <a href='../../login.php' class='back' style='width:50px;float:none' back button_next>Finish</a>");
		}
	}
}

$ic = new MigrateWizard();
$ic->dispatch();
?>