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

define('UserPreferences_PluginDir', dirname(__FILE__));
$start = strpos(dirname(__FILE__), 'plugins');
$path = substr(dirname(__FILE__), $start);
$path = str_replace("\\", "/", $path);
$file = $path.'/KTUserPreferences.php';
define('UserPreferencesPlugin_KTFile', $file);

/* */
require_once("UserPreferences.inc.php");
/* Plugin Base */
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class UserPreferencesPlugin extends KTPlugin {
    public $sNamespace = 'user.preferences.plugin';
    public $iVersion = 0;
    public $autoRegister = true;
	public $showInAdmin = false;
	
    /**
     * User Preferences constructor
     *
     * @param string $sFilename
     * @return UserPreferencesPlugin
     */
    function UserPreferencesPlugin($sFilename = null) {
		parent::KTPlugin($sFilename);
		$this->sFriendlyName = _kt('User Preferences Plugin');
		$this->sSQLDir = UserPreferences_PluginDir . DIRECTORY_SEPARATOR. 'sql' . DIRECTORY_SEPARATOR;
		$this->dir = dirname(__FILE__);
    }


}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('UserPreferencesPlugin', 'user.preferences.plugin', __FILE__);
?>