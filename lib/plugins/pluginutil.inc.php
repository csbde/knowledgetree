<?php
/**
 * $Id$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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
	const CACHE_FILENAME = 'kt_plugins.cache';

	/**
	 * Store the plugin cache in the cache directory.
	 *
	 */
	static function savePluginCache($array)
	{
		$config = KTConfig::getSingleton();
		$cachePlugins = $config->get('cache/cachePlugins', false);
		if (!$cachePlugins)
		{
			return false;
		}

		$cacheDir = $config->get('cache/cacheDirectory');

		$written = file_put_contents($cacheDir . '/' . KTPluginUtil::CACHE_FILENAME , serialize($array));

		if (!$written)
		{
			global $default;

			$default->log->warn('savePluginCache - The cache did not write anything.');

			// try unlink a zero size file - just in case
			@unlink($cacheFile);
		}
	}

	/**
	 * Remove the plugin cache.
	 *
	 */
	static function removePluginCache()
	{
		$config = KTConfig::getSingleton();
		$cachePlugins = $config->get('cache/cachePlugins', false);
		if (!$cachePlugins)
		{
			return false;
		}
		$cacheDir = $config->get('cache/cacheDirectory');

		$cacheFile=$cacheDir  . '/' . KTPluginUtil::CACHE_FILENAME;
		@unlink($cacheFile);
	}

	/**
	 * Reads the plugin cache file. This must still be unserialised.
	 *
	 * @return mixed Returns false on failure, or the serialised cache.
	 */
	static function readPluginCache()
	{
		$config = KTConfig::getSingleton();
		$cachePlugins = $config->get('cache/cachePlugins', false);
		if (!$cachePlugins)
		{
			return false;
		}
		$cacheDir = $config->get('cache/cacheDirectory');

		$cacheFile=$cacheDir  . '/' . KTPluginUtil::CACHE_FILENAME;
		if (!is_file($cacheFile))
		{
			return false;
		}

		$cache = file_get_contents($cacheFile);

		// we check for an empty cache in case there was a problem. We rather try and reload everything otherwise.
		if (strlen($cache) == 0)
		{
			return false;
		}
		if (!class_exists('KTPluginEntityProxy')) {
            KTEntityUtil::_proxyCreate('KTPluginEntity', 'KTPluginEntityProxy');
        }

		return unserialize($cache);
	}

    static function loadPlugins () {

        $GLOBALS['_KT_PLUGIN'] = array();
        $cache = KTPluginUtil::readPluginCache();
        if ($cache === false)
        {
        	$aPlugins = KTPluginEntity::getList("disabled=0");
        	KTPluginUtil::savePluginCache($aPlugins);
        }
        else
        {
        	$aPlugins = $cache;
        }

        if (count($aPlugins) === 0) {
            KTPluginUtil::registerPlugins();
        }
        $aPaths = array();
        $aPaths[] = KT_DIR . '/plugins/ktcore/KTCorePlugin.php';
        $aPaths[] = KT_DIR . '/plugins/ktcore/KTCoreLanguagePlugin.php';
        foreach ($aPlugins as $oPlugin) {
            if (!is_a($oPlugin, 'KTPluginEntity')) {
                print "<pre>";
                print "loadPlugins()\n";
                var_dump($aPlugins);
                exit(0);
            }
            $sPath = $oPlugin->getPath();
            if (!KTUtil::isAbsolutePath($sPath)) {
                $sPath = sprintf("%s/%s", KT_DIR, $sPath);
            }
            $aPaths[] = $sPath;
        }
        $aPaths = array_unique($aPaths);
        foreach ($aPaths as $sPath) {
            if (file_exists($sPath)) {
                require_once($sPath);
            }
        }
        $oRegistry =& KTPluginRegistry::getSingleton();
        $aPlugins =& $oRegistry->getPlugins();
        foreach ($aPlugins as $oPlugin) {
            if (!isset($aOrder[$oPlugin->iOrder])) {
                $aOrder[$oPlugin->iOrder] = array();
            }
            $aOrder[$oPlugin->iOrder][] = $oPlugin;
        }
        ksort($aOrder, SORT_NUMERIC);
        foreach ($aOrder as $iOrder => $aOrderPlugins) {
            foreach ($aOrderPlugins as $oPlugin) {
                $oPlugin->load();
            }
        }
    }

    /**
     * This loads the plugins in the plugins folder. It searches for files ending with 'Plugin.php'.
     * This is called by the 'Re-read plugins' action in the web interface.
     */
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

        KTPluginUtil::removePluginCache();
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