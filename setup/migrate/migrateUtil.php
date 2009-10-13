<?php
/**
* Migrater Utilities Library
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright(C) 2008,2009 KnowledgeTree Inc.
* Portions copyright The Jam Warehouse Software(Pty) Limited
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

class MigrateUtil {	
	private $bootstrap = null;
	/**
	* Constructs migrateation object
	*
	* @author KnowledgeTree Team
	* @access public
 	*/
	public function __construct() {
		require_once("../wizard/installUtil.php");
		$this->bootstrap = new InstallUtil();
	}
	
	/**
	* Check if system needs to be migrated
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return boolean
 	*/
	public function isSystemMigrateed() {
		if (file_exists(dirname(__FILE__)."/migrate")) {

			return true;
		}
		
		return false;
	}

	public function error($error) {
		$template_vars['error'] = $error;
		$file = "templates/error.tpl";
		if (!file_exists($file)) {
			return false;
		}
		extract($template_vars); // Extract the vars to local namespace
		ob_start();
		include($file);
        $contents = ob_get_contents();
        ob_end_clean();
        echo $contents;
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

    /**
     * Check whether a given directory / file path exists and is writable
     *
	 * @author KnowledgeTree Team
     * @access private
     * @param string $dir The directory / file to check
     * @param boolean $create Whether to create the directory if it doesn't exist
     * @return array The message and css class to use
     */
    private function _checkPermission($dir)
    {
        if(is_readable($dir) && is_writable($dir)) {
			return true;
        } else {
        	return false;
        }

    }
    
    public function loadInstallDBUtil() {
    	require_once("../wizard/dbUtil.php");
    	return new dbUtil();
    }
    
    public function loadInstallUtil() {
    	require_once("../wizard/steps/services.php");
    	return new services();
    }
    
    public function loadInstallServices() {
    	$s = $this->loadInstallUtil();
    	return $s->getServices();
    }
    
    public function loadInstallService($serviceName) {
    	require_once("../wizard/lib/services/service.php");
    	require_once("../wizard/lib/services/".OS."Service.php");
    	require_once("../wizard/lib/services/$serviceName.php");
    	return new $serviceName();
    }
    
    public function loadInstallIni($path) {
    	require_once("../wizard/ini.php");
    	return new Ini($path);
    }
    
    public function redirect($url, $exit = true, $rfc2616 = false)
    {
		return $this->bootstrap->redirect($url, $exit = true, $rfc2616 = false);
    }

    public function absoluteURI($url = null, $protocol = null, $port = null)
    {
		return $this->bootstrap->absoluteURI($url = null, $protocol = null, $port = null);
    }

   /**
     * Portably execute a command on any of the supported platforms.
     *
	 * @author KnowledgeTree Team
     * @access public
     * @param string $aCmd
     * @param array $aOptions
     * @return array
     */
    public function pexec($aCmd, $aOptions = null) {
		return $this->bootstrap->pexec($aCmd, $aOptions = null);
    }
}
?>