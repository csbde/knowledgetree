<?php

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
    function loadPlugins () {
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
            $oPlugin->register();
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
}

?>
