<?php

class KTPluginRegistry {
    var $_aPluginDetails = array();
    var $_aPlugins = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTPluginRegistry')) {
            $GLOBALS['oKTPluginRegistry'] = new KTPluginRegistry;
        }
        return $GLOBALS['oKTPluginRegistry'];
    }

    function registerPlugin($sClassName, $sNamespace, $sFilename = null) {
        $this->_aPluginDetails[$sNamespace] = array($sClassName, $sNamespace, $sFilename);
    }

    function &getPlugin($sNamespace) {
        $oPlugin =& KTUtil::arrayGet($this->_aPlugins, $sNamespace);
        if (!empty($oPlugin)) {
            return $oPlugin;
        }
        $aDetails = KTUtil::arrayGet($this->_aPluginDetails, $sNamespace);
        if (empty($aDetails)) {
            return null;
        }
        $sFilename = $aDetails[2];
        if (!empty($sFilename)) {
            require_once($sFilename);
        }
        $sClassName = $aDetails[0];
        return new $sClassName($sFilename);
    }
}

