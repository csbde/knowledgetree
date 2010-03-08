<?php
/**
 * $Id: $
 *
 * The contents of this file are subject to the KnowledgeTree
 * Commercial Editions On-Premise License ("License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.knowledgetree.com/about/legal/
 * The terms of this license may change from time to time and the latest
 * license will be published from time to time at the above Internet address.
 *
 * This edition of the KnowledgeTree software
 * is NOT licensed to you under Open Source terms.
 * You may not redistribute this source code.
 * For more information please see the License above.
 *
 * (c) 2008 KnowledgeTree Inc.
 * All Rights Reserved.
 *
 */

define('PLUGINDIR_UserPreferencesPlugin', dirname(__FILE__));
/* */
require_once("UserPreferences.inc.php");
/* Plugin Base */
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/database/sqlfile.inc.php');
require_once(KT_LIB_DIR . '/database/dbutil.inc');

/* Folder Actions */
require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

class UserPreferencesPlugin extends KTPlugin {
    public $sNamespace = 'up.UserPreferencesPlugin.plugin';
    public $iVersion = 0;
    public $autoRegister = true;
	//public $showInAdmin = false;
	
    /**
     * User Preferences constructor
     *
     * @param string $sFilename
     * @return UserPreferencesPlugin
     */
    function UserPreferencesPlugin($sFilename = null) {
		parent::KTPlugin($sFilename);
		$this->sFriendlyName = _kt('User Preferences Plugin');
		$this->sSQLDir = PLUGINDIR_UserPreferencesPlugin . DIRECTORY_SEPARATOR. 'sql' . DIRECTORY_SEPARATOR;
		$this->dir = dirname(__FILE__);
    }

    /**
     * Basic plugin setup
     * 
     * @param none
     * @return none
     */
    function setup() {
        $this->registerAdminPage("adminuserpreferencesmanagement",
				 'adminManageUserPreferencesDispatcher',
				 'misc',
				 _kt('User Preferences'),
				 _kt('User Preferences'),
				 'manageUserPreferences.php',
				 null);
		$this->registerPage('userpreferencesmanagement', 'ManageUserPreferencesDispatcher', 'manageUserPreferences.php');
		$plugin_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        require_once(KT_LIB_DIR . '/templating/templating.inc.php');
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('UserPreferencesPlugin', $plugin_dir.'templates', 'fs.UserPreferencesPlugin.plugin');
        $this->applySQL(); // Create Table
    }

    function applySQL()
    {
    	$sql = "select * from user_preferences";
    	$result = DBUtil::getResultArray($sql);

    	if (!PEAR::isError($result))
		{
			return; // if we find the table, we assume it has been applied
		}
		$filename = $this->sSQLDir . 'user_preferences.sql';
		$content = file_get_contents($filename);

		global $default;
		DBUtil::setupAdminDatabase();
		$db = $default->_admindb;
		$aQueries = SQLFile::splitSQL($content);
		DBUtil::startTransaction();
        $res = DBUtil::runQueries($aQueries, $db);
        if (PEAR::isError($res)) {
            DBUtil::rollback();
            return $res;
        }
        DBUtil::commit();
    }
    
    /**
     * Method to setup the plugin on rendering it
     *
     * @param none
     * @return boolean
     */
    function run_setup() {
		
	    return true;
    }
    
    /**
     * Register the plugin
     *
     * @return unknown
     */
    function register() {
      $oEnt = parent::register();

      return $oEnt;
    }
    
    public function getUserPreferences($iUserId, $sKey) {
    	$aPref = UserPreferences::getUserPreferences($iUserId, $sKey);
    	if(PEAR::isError($aPref)) {
    		return false;
    	}
    	if(count($aPref) > 1) {
    		return false;
    	}
    	
    	foreach ($aPref as $oPref) {
    		return $oPref->getValue();
    	}
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('UserPreferencesPlugin', 'up.UserPreferencesPlugin.plugin', __FILE__);
?>