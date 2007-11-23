<?php
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 *
 * The Original Code is: KnowledgeTree Open Source
 *
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/plugins/pluginentity.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class KTPluginResourceRegistry {
    var $aResources = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTPluginResourceRegistry')) {
            $GLOBALS['oKTPluginResourceRegistry'] = new KTPluginResourceRegistry;
        }
        return $GLOBALS['oKTPluginResourceRegistry'];
    }

    function registerResource($sPath) {
        $this->aResources[$sPath] = true;
    }

    function isRegistered($sPath) {
        if (KTUtil::arrayGet($this->aResources, $sPath)) {
            return true;
        }
        $sPath = dirname($sPath);
        if (KTUtil::arrayGet($this->aResources, $sPath)) {
            return true;
        }
        return false;
    }
}

class KTPluginUtil {
    /**
     * Load the plugins for the current page
     *
     * @param unknown_type $sType
     */
    function loadPlugins ($sType) {

        if($sType != 'dashboard'){
          $sType = 'general';
        }
        $GLOBALS['_KT_PLUGIN'] = array();

        $aPlugins = array();
        $aPluginHelpers = array();

        // Get the list of enabled plugins
        $query = "SELECT * FROM plugin_helper h, plugins p
           WHERE p.namespace = h.plugin AND p.disabled = 0 AND h.classtype='plugin'";
        $aPluginHelpers = DBUtil::getResultArray($query);

        // Check that there are plugins and if not, register them
        if (empty($aPluginHelpers)) {
            KTPluginUtil::registerPlugins();

        	$query = "SELECT * FROM plugin_helper h, plugins p
        	   WHERE p.namespace = h.plugin AND p.disabled = 0 AND h.viewtype='{$sType}' AND h.classtype='plugin'";
        	$aPluginHelpers = DBUtil::getResultArray($query);
        }

        // Create plugin objects
        foreach ($aPluginHelpers as $aItem){
            $classname = $aItem['classname'];
            $path = $aItem['pathname'];

            if (!empty($path)) {
                require_once($path);
            }

        	$oPlugin = new $classname($path);
        	$aPlugins[] = $oPlugin;
        	$oPlugin->load();
        }

        // load plugin helpers into global space
        $query = "SELECT h.* FROM plugin_helper h, plugins p
        	   WHERE p.namespace = h.plugin AND p.disabled = 0 ";//WHERE viewtype='{$sType}'";
        $aPluginList = DBUtil::getResultArray($query);
        KTPluginUtil::load($aPluginList);

        // Load the template locations
        $query = "SELECT * FROM plugin_helper h WHERE h.classtype='locations'";
        $aLocations = DBUtil::getResultArray($query);
        if(!empty($aLocations)){
            $oTemplating =& KTTemplating::getSingleton();
            foreach ($aLocations as $location){
                $aParams = explode('|', $location['object']);
                call_user_func_array(array(&$oTemplating, 'addLocation2'), $aParams);
            }
        }
        return;
    }

    /**
     * Load the plugins into the global space
     *
     * @param array $aPlugins
     */
    function load($aPlugins) {
        require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
        require_once(KT_LIB_DIR . '/actions/portletregistry.inc.php');
        require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
        require_once(KT_LIB_DIR . '/plugins/pageregistry.inc.php');
        require_once(KT_LIB_DIR . '/authentication/authenticationproviderregistry.inc.php');
        require_once(KT_LIB_DIR . "/plugins/KTAdminNavigation.php");
        require_once(KT_LIB_DIR . "/dashboard/dashletregistry.inc.php");
        require_once(KT_LIB_DIR . "/i18n/i18nregistry.inc.php");
        require_once(KT_LIB_DIR . "/help/help.inc.php");
        require_once(KT_LIB_DIR . "/workflow/workflowutil.inc.php");
        require_once(KT_LIB_DIR . "/widgets/widgetfactory.inc.php");
        require_once(KT_LIB_DIR . "/validation/validatorfactory.inc.php");
        require_once(KT_LIB_DIR . "/browse/columnregistry.inc.php");
        require_once(KT_LIB_DIR . "/browse/criteriaregistry.php");
        require_once(KT_LIB_DIR . "/authentication/interceptorregistry.inc.php");

        $oPRegistry =& KTPortletRegistry::getSingleton();
        $oTRegistry =& KTTriggerRegistry::getSingleton();
        $oARegistry =& KTActionRegistry::getSingleton();
        $oPageRegistry =& KTPageRegistry::getSingleton();
        $oAPRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oAdminRegistry =& KTAdminNavigationRegistry::getSingleton();
        $oDashletRegistry =& KTDashletRegistry::getSingleton();
        $oi18nRegistry =& KTi18nRegistry::getSingleton();
        $oKTHelpRegistry =& KTHelpRegistry::getSingleton();
        $oWFTriggerRegistry =& KTWorkflowTriggerRegistry::getSingleton();
        $oColumnRegistry =& KTColumnRegistry::getSingleton();
        $oNotificationHandlerRegistry =& KTNotificationRegistry::getSingleton();
        $oTemplating =& KTTemplating::getSingleton();
        $oWidgetFactory =& KTWidgetFactory::getSingleton();
        $oValidatorFactory =& KTValidatorFactory::getSingleton();
        $oCriteriaRegistry =& KTCriteriaRegistry::getSingleton();
        $oInterceptorRegistry =& KTInterceptorRegistry::getSingleton();
        $oKTPluginRegistry =& KTPluginRegistry::getSingleton();

        // Loop through the loaded plugins and register them
        foreach ($aPlugins as $plugin){
            $sName = $plugin['namespace'];
        	$sParams = $plugin['object'];
        	$aParams = explode('|', $sParams);
        	$sClassType = $plugin['classtype'];

        	switch ($sClassType) {
        	    case 'portlet':
        	        $aLocation = unserialize($aParams[0]);
        	        if($aLocation !== false){
        	           $aParams[0] = $aLocation;
        	        }

        	        call_user_func_array(array(&$oPRegistry, 'registerPortlet'), $aParams);
        	        break;

        	    case 'trigger':
        	        call_user_func_array(array(&$oTRegistry, 'registerTrigger'), $aParams);
        	        break;

        	    case 'action':
        	        call_user_func_array(array(&$oARegistry, 'registerAction'), $aParams);
        	        break;

        	    case 'page':
        	        call_user_func_array(array(&$oPageRegistry, 'registerPage'), $aParams);
        	        break;

        	    case 'authentication_provider':
        	        call_user_func_array(array(&$oAPRegistry, 'registerAuthenticationProvider'), $aParams);
        	        break;

        	    case 'admin_category':
        	        call_user_func_array(array(&$oAdminRegistry, 'registerCategory'), $aParams);
        	        break;

        	    case 'admin_page':
        	        call_user_func_array(array(&$oAdminRegistry, 'registerLocation'), $aParams);
        	        break;

        	    case 'dashlet':
        	        call_user_func_array(array(&$oDashletRegistry, 'registerDashlet'), $aParams);
        	        break;

        	    case 'i18n':
        	        call_user_func_array(array(&$oi18nRegistry, 'registeri18n'), $aParams);
        	        break;

        	    case 'i18nlang':
        	        call_user_func_array(array(&$oi18nRegistry, 'registeri18nLang'), $aParams);
        	        break;

        	    case 'language':
        	        call_user_func_array(array(&$oi18nRegistry, 'registerLanguage'), $aParams);
        	        break;

        	    case 'help_language':
        	        call_user_func_array(array(&$oKTHelpRegistry, 'registerHelp'), $aParams);
        	        break;

        	    case 'workflow_trigger':
        	        call_user_func_array(array(&$oWFTriggerRegistry, 'registerWorkflowTrigger'), $aParams);
        	        break;

        	    case 'column':
        	        call_user_func_array(array(&$oColumnRegistry, 'registerColumn'), $aParams);
        	        break;

        	    case 'view':
        	        call_user_func_array(array(&$oColumnRegistry, 'registerView'), $aParams);
        	        break;

        	    case 'notification_handler':
        	        call_user_func_array(array(&$oNotificationHandlerRegistry, 'registerNotificationHandler'), $aParams);
        	        break;

        	    case 'template_location':
        	        call_user_func_array(array(&$oTemplating, 'addLocation'), $aParams);
        	        break;

        	    case 'criterion':
            	    $aInit = unserialize($aParams[3]);
            	    if($aInit !== false){
        	           $aParams[3] = $aInit;
            	    }
        	        call_user_func_array(array(&$oCriteriaRegistry, 'registerCriterion'), $aParams);
        	        break;

        	    case 'widget':
        	        call_user_func_array(array(&$oWidgetFactory, 'registerWidget'), $aParams);
        	        break;

        	    case 'validator':
        	        call_user_func_array(array(&$oValidatorFactory, 'registerValidator'), $aParams);
        	        break;

        	    case 'interceptor':
        	        call_user_func_array(array(&$oInterceptorRegistry, 'registerInterceptor'), $aParams);
        	        break;

        	    case 'plugin':
        	        $oKTPluginRegistry->_aPluginDetails[$sName] = $aParams;
        	        break;
        	}
        }
    }

    function registerPlugins () {
        KTPluginUtil::_deleteSmartyFiles();
        require_once(KT_LIB_DIR . '/cache/cache.inc.php');
        $oCache =& KTCache::getSingleton();
        $oCache->deleteAllCaches();

        $files = array();
        KTPluginUtil::_walk(KT_DIR . '/plugins', $files);
        foreach ($files as $sFile) {
            $plugin_ending = "Plugin.php";
            if (substr($sFile, -strlen($plugin_ending)) === $plugin_ending) {
                require_once($sFile);
            }
        }
        $oRegistry =& KTPluginRegistry::getSingleton();
        foreach ($oRegistry->getPlugins() as $oPlugin) {
            $res = $oPlugin->register();
            if (PEAR::isError($res)) {
                var_dump($res);
            }
        }

        foreach (KTPluginEntity::getList() as $oPluginEntity) {
            $sPath = $oPluginEntity->getPath();
            if (!KTUtil::isAbsolutePath($sPath)) {
                $sPath = sprintf("%s/%s", KT_DIR, $sPath);
            }
            if (!file_exists($sPath)) {
                $oPluginEntity->setUnavailable(true);
                $oPluginEntity->setDisabled(true);
                $res = $oPluginEntity->update();
            }
        }
        KTPluginEntity::clearAllCaches();

        KTPluginUtil::_deleteSmartyFiles();
        require_once(KT_LIB_DIR . '/cache/cache.inc.php');
        $oCache =& KTCache::getSingleton();
        $oCache->deleteAllCaches();

        $sPluginCache = KT_DIR . '/var/plugin-cache';
        @unlink($sPluginCache);
    }

    function _deleteSmartyFiles() {
        $oConfig =& KTConfig::getSingleton();
        $dir = sprintf('%s/%s', $oConfig->get('urls/varDirectory'), 'tmp');

        $dh = @opendir($dir);
        if (empty($dh)) {
            return;
        }
        $aFiles = array();
        while (false !== ($sFilename = readdir($dh))) {
            if (substr($sFilename, -10) == "smarty.inc") {
               $aFiles[] = sprintf('%s/%s', $dir, $sFilename);
            }
            if (substr($sFilename, -10) == "smarty.php") {
               $aFiles[] = sprintf('%s/%s', $dir, $sFilename);
            }
        }
        foreach ($aFiles as $sFile) {
            @unlink($sFile);
        }
    }

    function _walk ($path, &$files) {
        if (!is_dir($path)) {
            return;
        }
        $dirh = opendir($path);
        while (($entry = readdir($dirh)) !== false) {
            if (in_array($entry, array('.', '..'))) {
                continue;
            }
            $newpath = $path . '/' . $entry;
            if (is_dir($newpath)) {
                KTPluginUtil::_walk($newpath, $files);
            }
            if (!is_file($newpath)) {
                continue;
            }
            $files[] = $newpath;
        }
    }

    function resourceIsRegistered($path) {
        $oRegistry =& KTPluginResourceRegistry::getSingleton();
        return $oRegistry->isRegistered($path);
    }

    function registerResource($path) {
        $oRegistry =& KTPluginResourceRegistry::getSingleton();
        $oRegistry->registerResource($path);
    }

    function readResource($sPath) {
        global $default;
        $php_file = ".php";
        if (substr($sPath, -strlen($php_file)) === $php_file) {
            require_once($php_file);
        } else {
            $pi = pathinfo($sPath);
            $mime_type = "";
            $sExtension = KTUtil::arrayGet($pi, 'extension');
            if (!empty($sExtension)) {
                $mime_type = DBUtil::getOneResultKey(array("SELECT mimetypes FROM " . $default->mimetypes_table . " WHERE LOWER(filetypes) = ?", $sExtension), "mimetypes");
            }
            if (empty($mime_type)) {
                $mime_type = "application/octet-stream";
            }
            $sFullPath = KT_DIR . '/plugins' . $sPath;
            header("Content-Type: $mime_type");
            header("Content-Length: " . filesize($sFullPath));
            readfile($sFullPath);
        }
    }

    // utility function to detect if the plugin is loaded and active.
    function pluginIsActive($sNamespace) {



		$oReg =& KTPluginRegistry::getSingleton();
		$plugin = $oReg->getPlugin($sNamespace);



		if (is_null($plugin) || PEAR::isError($plugin)) { return false; }  // no such plugin
		else { // check if its active
			$ent = KTPluginEntity::getByNamespace($sNamespace);

			if (PEAR::isError($ent)) { return false; }

			// we now can ask
			return (!$ent->getDisabled());
		}
    }
}

?>
