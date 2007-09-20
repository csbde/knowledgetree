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
    static function loadPlugins () {
        $sPluginCache = KT_DIR . '/var/plugin-cache';
        if (file_exists($sPluginCache)) {
            require_once(KT_LIB_DIR . "/plugins/plugin.inc.php");
            require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
            require_once(KT_LIB_DIR . '/actions/portletregistry.inc.php');
            require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
            require_once(KT_LIB_DIR . '/plugins/pageregistry.inc.php');
            require_once(KT_LIB_DIR . '/authentication/authenticationproviderregistry.inc.php');
            require_once(KT_LIB_DIR . "/plugins/KTAdminNavigation.php");
            require_once(KT_LIB_DIR . "/dashboard/dashletregistry.inc.php");
            require_once(KT_LIB_DIR . "/i18n/i18nregistry.inc.php");
            require_once(KT_LIB_DIR . "/help/help.inc.php");
            require_once(KT_LIB_DIR . "/browse/columnregistry.inc.php");
            require_once(KT_LIB_DIR . "/authentication/interceptorregistry.inc.php");
            require_once(KT_LIB_DIR . "/widgets/widgetfactory.inc.php");
            require_once(KT_LIB_DIR . "/validation/validatorfactory.inc.php");
            $GLOBALS['_KT_PLUGIN'] = unserialize(file_get_contents($sPluginCache));
            $GLOBALS['_KT_PLUGIN']['oKTPluginRegistry']->_aPlugins = array();
            return;
        }
        $GLOBALS['_KT_PLUGIN'] = array();
        $aPlugins = KTPluginEntity::getList("disabled=0");
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
        // file_put_contents($sPluginCache, serialize($GLOBALS['_KT_PLUGIN']));
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
