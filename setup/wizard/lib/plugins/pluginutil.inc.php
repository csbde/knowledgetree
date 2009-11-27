<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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
 *
 */

//require_once(KT_LIB_DIR . '/plugins/pluginentity.inc.php');
//require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
//
//class KTPluginResourceRegistry {
//    var $aResources = array();
//
//    function &getSingleton() {
//        if (!KTUtil::arrayGet($GLOBALS, 'oKTPluginResourceRegistry')) {
//            $GLOBALS['oKTPluginResourceRegistry'] = new KTPluginResourceRegistry;
//        }
//        return $GLOBALS['oKTPluginResourceRegistry'];
//    }
//
//    function registerResource($sPath) {
//        $this->aResources[$sPath] = true;
//    }
//
//    function isRegistered($sPath) {
//        if (KTUtil::arrayGet($this->aResources, $sPath)) {
//            return true;
//        }
//        $sPath = dirname($sPath);
//        if (KTUtil::arrayGet($this->aResources, $sPath)) {
//            return true;
//        }
//        return false;
//    }
//}

class KTPluginUtil {
//	const CACHE_FILENAME = 'kt_plugins.cache';
//
//	/**
//	 * Store the plugin cache in the cache directory.
//	 * @deprecated
//	 */
//	static function savePluginCache($array)
//	{
//		$config = KTConfig::getSingleton();
//		$cachePlugins = $config->get('cache/cachePlugins', false);
//		if (!$cachePlugins)
//		{
//			return false;
//		}
//
//		$cacheDir = $config->get('cache/cacheDirectory');
//
//		$written = file_put_contents($cacheDir . '/' . KTPluginUtil::CACHE_FILENAME , serialize($array));
//
//		if (!$written)
//		{
//			global $default;
//
//			$default->log->warn('savePluginCache - The cache did not write anything.');
//
//			// try unlink a zero size file - just in case
//			@unlink($cacheFile);
//		}
//	}

//	/**
//	 * Remove the plugin cache.
//	 * @deprecated
//	 */
//	static function removePluginCache()
//	{
//		$config = KTConfig::getSingleton();
//		$cachePlugins = $config->get('cache/cachePlugins', false);
//		if (!$cachePlugins)
//		{
//			return false;
//		}
//		$cacheDir = $config->get('cache/cacheDirectory');
//
//		$cacheFile=$cacheDir  . '/' . KTPluginUtil::CACHE_FILENAME;
//		@unlink($cacheFile);
//	}

//	/**
//	 * Reads the plugin cache file. This must still be unserialised.
//	 * @deprecated
//	 * @return mixed Returns false on failure, or the serialised cache.
//	 */
//	static function readPluginCache()
//	{
//		$config = KTConfig::getSingleton();
//		$cachePlugins = $config->get('cache/cachePlugins', false);
//		if (!$cachePlugins)
//		{
//			return false;
//		}
//		$cacheDir = $config->get('cache/cacheDirectory');
//
//		$cacheFile=$cacheDir  . '/' . KTPluginUtil::CACHE_FILENAME;
//		if (!is_file($cacheFile))
//		{
//			return false;
//		}
//
//		$cache = file_get_contents($cacheFile);
//
//		// we check for an empty cache in case there was a problem. We rather try and reload everything otherwise.
//		if (strlen($cache) == 0)
//		{
//			return false;
//		}
//		if (!class_exists('KTPluginEntityProxy')) {
//            KTEntityUtil::_proxyCreate('KTPluginEntity', 'KTPluginEntityProxy');
//        }
//
//		return unserialize($cache);
//	}

//	/**
//     * Load the plugins for the current page
//     *
//     * @param unknown_type $sType
//     */
//    static function loadPlugins ($sType) {
//
//        // Check the current page - can be extended.
//        // Currently we only distinguish between the dashboard and everything else.
//        if($sType != 'dashboard'){
//          $sType = 'general';
//        }
//
//        $aPlugins = array();
//        $aPluginHelpers = array();
//        $aDisabled = array();
//
//        // Get the list of enabled plugins
//        $query = "SELECT h.classname, h.pathname, h.plugin FROM plugin_helper h
//            INNER JOIN plugins p ON (p.namespace = h.plugin)
//           WHERE p.disabled = 0 AND h.classtype='plugin' ORDER BY p.orderby";
//        $aPluginHelpers = DBUtil::getResultArray($query);
//
//        if(PEAR::isError($aPluginHelpers)){
//            global $default;
//            $default->log->debug('Error in pluginutil: '.$aPluginHelpers->getMessage());
//            return false;
//        }
//
//        // Check that there are plugins and if not, register them
//        if (empty($aPluginHelpers) || (isset($_POST['_force_plugin_truncate']))) {
//            DBUtil::startTransaction();
//            KTPluginUtil::registerPlugins();
//            DBUtil::commit();
//
//        	$query = "SELECT h.classname, h.pathname, h.plugin FROM plugin_helper h
//        	   INNER JOIN plugins p ON (p.namespace = h.plugin)
//        	   WHERE p.disabled = 0 AND h.classtype='plugin' ORDER BY p.orderby";
//        	$aPluginHelpers = DBUtil::getResultArray($query);
//        }
//
//        // Create plugin objects
//        foreach ($aPluginHelpers as $aItem){
//            $classname = $aItem['classname'];
//            $path = $aItem['pathname'];
//
//            if (!empty($path)) {
//                $path = KT_DIR.'/'.$path;
//                require_once($path);
//
//            	$oPlugin = new $classname($path);
//            	if($oPlugin->load()){
//            	   $aPlugins[] = $oPlugin;
//            	}else{
//            	    $aDisabled[] = "'{$aItem['plugin']}'";
//            	}
//            }
//        }
//
//        $sDisabled = implode(',', $aDisabled);
//
//        // load plugin helpers into global space
//        $query = 'SELECT h.* FROM plugin_helper h
//            INNER JOIN plugins p ON (p.namespace = h.plugin)
//        	WHERE p.disabled = 0 ';//WHERE viewtype='{$sType}'";
//        if(!empty($sDisabled)){
//        	   $query .= " AND h.plugin NOT IN ($sDisabled) ";
//        }
//        $query .= ' ORDER BY p.orderby';
//
//        $aPluginList = DBUtil::getResultArray($query);
//
//        KTPluginUtil::load($aPluginList);
//
//        // Load the template locations - ignore disabled plugins
//        // Allow for templates that don't correctly link to the plugin
//        $query = "SELECT * FROM plugin_helper h
//            LEFT JOIN plugins p ON (p.namespace = h.plugin)
//            WHERE h.classtype='locations' AND (disabled = 0 OR disabled IS NULL) AND unavailable = 0";
//
//        $aLocations = DBUtil::getResultArray($query);
//
//        if(!empty($aLocations)){
//            $oTemplating =& KTTemplating::getSingleton();
//            foreach ($aLocations as $location){
//                $aParams = explode('|', $location['object']);
//                call_user_func_array(array(&$oTemplating, 'addLocation2'), $aParams);
//            }
//        }
//        return true;
//    }

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


        // Loop through the loaded plugins and register them for access
        foreach ($aPlugins as $plugin){
            $sName = $plugin['namespace'];
        	$sParams = $plugin['object'];
        	$aParams = explode('|', $sParams);
        	$sClassType = $plugin['classtype'];

        	switch ($sClassType) {
        	    case 'portlet':
        	        $aLocation = unserialize($aParams[0]);
        	        if($aLocation != false){
        	           $aParams[0] = $aLocation;
        	        }
                    if(isset($aParams[3])){
                        $aParams[3] = KTPluginUtil::getFullPath($aParams[3]);
                    }
        	        call_user_func_array(array(&$oPRegistry, 'registerPortlet'), $aParams);
        	        break;

        	    case 'trigger':
                    if(isset($aParams[4])){
                        $aParams[4] = KTPluginUtil::getFullPath($aParams[4]);
                    }
        	        call_user_func_array(array(&$oTRegistry, 'registerTrigger'), $aParams);
        	        break;

        	    case 'action':
                    if(isset($aParams[3])){
                        $aParams[3] = KTPluginUtil::getFullPath($aParams[3]);
                    }
        	        call_user_func_array(array(&$oARegistry, 'registerAction'), $aParams);
        	        break;

        	    case 'page':
                    if(isset($aParams[2])){
                        $aParams[2] = KTPluginUtil::getFullPath($aParams[2]);
                    }
        	        call_user_func_array(array(&$oPageRegistry, 'registerPage'), $aParams);
        	        break;

        	    case 'authentication_provider':
                    if(isset($aParams[3])){
                        $aParams[3] = KTPluginUtil::getFullPath($aParams[3]);
                    }
                    $aParams[0] = _kt($aParams[0]);
        	        call_user_func_array(array(&$oAPRegistry, 'registerAuthenticationProvider'), $aParams);
        	        break;

        	    case 'admin_category':
    	            $aParams[1] = _kt($aParams[1]);
    	            $aParams[2] = _kt($aParams[2]);
        	        call_user_func_array(array(&$oAdminRegistry, 'registerCategory'), $aParams);
        	        break;

        	    case 'admin_page':
                    if(isset($aParams[5])){
                        $aParams[5] = KTPluginUtil::getFullPath($aParams[5]);
                    }
                    $aParams[3] = _kt($aParams[3]);
                    $aParams[4] = _kt($aParams[4]);
        	        call_user_func_array(array(&$oAdminRegistry, 'registerLocation'), $aParams);
        	        break;

        	    case 'dashlet':
                    if(isset($aParams[2])){
                        $aParams[2] = KTPluginUtil::getFullPath($aParams[2]);
                    }
        	        call_user_func_array(array(&$oDashletRegistry, 'registerDashlet'), $aParams);
        	        break;

        	    case 'i18nlang':
                    if(isset($aParams[2]) && $aParams[2] != 'default'){
                        $aParams[2] = KTPluginUtil::getFullPath($aParams[2]);
                    }
        	        call_user_func_array(array(&$oi18nRegistry, 'registeri18nLang'), $aParams);


        	    case 'i18n':
                    if(isset($aParams[2])){
                        $aParams[1] = $aParams[2];
                        unset($aParams[2]);
                    } else {
                        $aParams[1] = KTPluginUtil::getFullPath($aParams[1]);
                    }
        	        call_user_func_array(array(&$oi18nRegistry, 'registeri18n'), $aParams);
        	        break;

        	    case 'language':
        	        call_user_func_array(array(&$oi18nRegistry, 'registerLanguage'), $aParams);
        	        break;

        	    case 'help_language':
                    if(isset($aParams[2])){
                        $aParams[2] = KTPluginUtil::getFullPath($aParams[2]);
                    }
        	        call_user_func_array(array(&$oKTHelpRegistry, 'registerHelp'), $aParams);
        	        break;

        	    case 'workflow_trigger':
                    if(isset($aParams[2])){
                        $aParams[2] = KTPluginUtil::getFullPath($aParams[2]);
                    }
        	        call_user_func_array(array(&$oWFTriggerRegistry, 'registerWorkflowTrigger'), $aParams);
        	        break;

        	    case 'column':
                    if(isset($aParams[3])){
                        $aParams[3] = KTPluginUtil::getFullPath($aParams[3]);
                    }
                    $aParams[0] = _kt($aParams[0]);
        	        call_user_func_array(array(&$oColumnRegistry, 'registerColumn'), $aParams);
        	        break;

        	    case 'view':
        	        $aParams[0] = _kt($aParams[0]);
        	        call_user_func_array(array(&$oColumnRegistry, 'registerView'), $aParams);
        	        break;

        	    case 'notification_handler':
                    if(isset($aParams[2])){
                        $aParams[2] = KTPluginUtil::getFullPath($aParams[2]);
                    }
        	        call_user_func_array(array(&$oNotificationHandlerRegistry, 'registerNotificationHandler'), $aParams);
        	        break;

        	    case 'template_location':
                    if(isset($aParams[1])){
                        $aParams[1] = KTPluginUtil::getFullPath($aParams[1]);
                    }
        	        call_user_func_array(array(&$oTemplating, 'addLocation2'), $aParams);
        	        break;

        	    case 'criterion':
            	    $aInit = unserialize($aParams[3]);
            	    if($aInit != false){
        	           $aParams[3] = $aInit;
            	    }
                    if(isset($aParams[2])){
                        $aParams[2] = KTPluginUtil::getFullPath($aParams[2]);
                    }
        	        call_user_func_array(array(&$oCriteriaRegistry, 'registerCriterion'), $aParams);
        	        break;

        	    case 'widget':
                    if(isset($aParams[2])){
                        $aParams[2] = KTPluginUtil::getFullPath($aParams[2]);
                    }
        	        call_user_func_array(array(&$oWidgetFactory, 'registerWidget'), $aParams);
        	        break;

        	    case 'validator':
                    if(isset($aParams[2])){
                        $aParams[2] = KTPluginUtil::getFullPath($aParams[2]);
                    }
        	        call_user_func_array(array(&$oValidatorFactory, 'registerValidator'), $aParams);
        	        break;

        	    case 'interceptor':
                    if(isset($aParams[2])){
                        $aParams[2] = KTPluginUtil::getFullPath($aParams[2]);
                    }
        	        call_user_func_array(array(&$oInterceptorRegistry, 'registerInterceptor'), $aParams);
        	        break;

        	    case 'plugin':
                    if(isset($aParams[2])){
                        $aParams[2] = KTPluginUtil::getFullPath($aParams[2]);
                    }
        	        $oKTPluginRegistry->_aPluginDetails[$sName] = $aParams;
        	        break;
        	}
        }
    }

    /**
     * Get the absolute path
     */
    function getFullPath($sPath = '') {
        if(empty($sPath)){
            return '';
        }
        $sPath = (KTUtil::isAbsolutePath($sPath)) ? $sPath : KT_DIR . '/' . $sPath;
        return $sPath;
    }

    /**
     * This loads the plugins in the plugins folder. It searches for files ending with 'Plugin.php'.
     * This is called by the 'Re-read plugins' action in the web interface.
     */
    function registerPlugins () {
        global $default;

        // Path to lock file
        $cacheDir = $default->cacheDirectory . DIRECTORY_SEPARATOR;
        $lockFile = $cacheDir.'plugin_register.lock';

        // Check if the lock file exists
        if(KTPluginUtil::doCheck($lockFile)){
            return true;
        }

        // Create the lock file, run through the plugin registration and then delete the lock file
        touch($lockFile);
        KTPluginUtil::doPluginRegistration();
        @unlink($lockFile);
    }

    /**
     * Check the lockfile
     */
    function doCheck($lockFile)
    {
        if(file_exists($lockFile)){
            // If it does exist, do a stat on it to check when it was created.
            // if it was accessed more than 5 minutes ago then delete it and proceed with the plugin registration
            // otherwise wait till lock file is deleted signalling that the registration is complete and return.

            $stat = stat($lockFile);

            $time = time() - (60 * 5);
            if($stat['mtime'] > $time){

                $cnt = 0;

                while(file_exists($lockFile)){
                    $cnt++;
                    sleep(2);

                    // if we've been waiting too long - typically it should only take a few seconds so 2 mins is too much time.
                    if($cnt > 60){
                        @unlink($lockFile);
                        return false;
                    }
                }
                return true;
            }
            @unlink($lockFile);
        }
        return false;
    }

    /* Get the priority of the plugin */
    function getPluginPriority($sFile) {
    	$defaultPriority = 10;
    	$priority = array(
    		"ktcore" => 1,
    		"ktstandard" => 2,
    		"i18n" => 3
    	);
    	foreach($priority as $pattern => $priority) {
    		if(ereg($pattern, $sFile)) {
    			return $priority;
    		}
    	}
    	return $defaultPriority;
    }

    /**
     * Read the plugins directory and register all plugins in the database.
     */
    function doPluginRegistration()
    {
        global $default;

        KTPluginUtil::_deleteSmartyFiles();
        require_once(KT_LIB_DIR . '/cache/cache.inc.php');
        $oCache =& KTCache::getSingleton();
        $oCache->deleteAllCaches();

        // Remove all entries from the plugin_helper table and refresh it.
        $query = "DELETE FROM plugin_helper";
        $res = DBUtil::runQuery($query);

        $files = array();
        $plugins = array();

        KTPluginUtil::_walk(KT_DIR . '/plugins', $files);
        foreach ($files as $sFile) {
            $plugin_ending = "Plugin.php";
            if (substr($sFile, -strlen($plugin_ending)) === $plugin_ending) {
            	/* Set default priority */
            	$plugins[$sFile] = KTPluginUtil::getPluginPriority($sFile);
            }
        }

        /* Sort the plugins by priority */
        asort($plugins);

        /*
        Add a check to indicate that plugin registration is occuring.
        This check has been put in place to prevent the plugin being registered on every page load.
        */
        $_SESSION['plugins_registerplugins'] = true;
        foreach($plugins as $sFile => $priority) {
        	require_once($sFile);
        }
        $_SESSION['plugins_registerplugins'] = false;

        $oRegistry =& KTPluginRegistry::getSingleton();
        $aRegistryList = $oRegistry->getPlugins();
        foreach ($aRegistryList as $oPlugin) {
            $res = $oPlugin->register();
            if (PEAR::isError($res)) {
                //var_dump($res);
                $default->log->debug('Register of plugin failed: ' . $res->getMessage());
            }
        }

        $aPluginList = KTPluginEntity::getList();
        foreach ($aPluginList as $oPluginEntity) {
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

        //KTPluginUtil::removePluginCache();
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

    /**
     * Get the full path to the plugin
     *
     * @param string $sNamespace The namespace of the plugin
     * @param bool $relative Whether the path should be relative or full
     * @return string
     */
    static function getPluginPath($sNamespace, $relative = false)
    {
        $oEntity = KTPluginEntity::getByNamespace($sNamespace);

        if(PEAR::isError($oEntity)){
            return $oEntity;
        }
        $dir = dirname($oEntity->getPath()) . '/';

        if(!$relative){
            $dir = KT_DIR . '/' . $dir;
        }

        return $dir;
    }

    // utility function to detect if the plugin is loaded and active.
    static function pluginIsActive($sNamespace) {

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
