<?php
/**
* Migrater Utilities Library
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright(C) 2008,2009 KnowledgeTree Inc.
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
* @package Migrater
* @version Version 0.1
*/
require_once("../wizard/installUtil.php");

class MigrateUtil extends InstallUtil {
	/**
	* Check if system needs to be migrated
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return boolean
 	*/
	public function isSystemMigrated() {
		if (file_exists(dirname(__FILE__)."/migrate")) {

			return true;
		}

		return false;
	}

	public function error($error) {
		$template_vars['migrate_type'] = strtoupper(substr(INSTALL_TYPE,0,1)).substr(INSTALL_TYPE,1);
		$template_vars['migrate_version'] = $this->readVersion();
		$template_vars['error'] = $error;
		$file = "templates/error.tpl";
		if (file_exists($file)) {
			extract($template_vars); // Extract the vars to local namespace
			ob_start();
			include($file);
	        $contents = ob_get_contents();
	        ob_end_clean();
	        echo $contents;
		}
		return false;
	}

	/**
	* Check if system needs to be migrated
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return mixed
 	*/
    public function checkStructurePermissions() {
    	// Check if Wizard Directory is writable
    	if(!$this->_checkPermission(WIZARD_DIR)) {
    		return 'migrate';
    	}

    	return true;
    }

    public function loadInstallServices() {
    	require_once("../wizard/steps/services.php");
    	$s = new services();
    	return $s->getServices();
    }

    public function loadInstallService($serviceName) {
    	require_once("../wizard/lib/services/service.php");
    	require_once("../wizard/lib/services/".OS."Service.php");
    	require_once("../wizard/lib/services/$serviceName.php");
    	return new $serviceName();
    }

    public function getPort($location) {
    	if(WINDOWS_OS) {
    		$myIni = "my.ini";
    	} else {
    		$myIni = "my.cnf";
    	}
    	$dbConfigPath = $location.DS."mysql".DS."$myIni";
    	if(file_exists($dbConfigPath)) {
			$this->iniUtilities->load($dbConfigPath);
			$dbSettings = $this->iniUtilities->getSection('mysqladmin');
    		return $dbSettings['port'];
    	}

    	return '3306';
    }
}
?>