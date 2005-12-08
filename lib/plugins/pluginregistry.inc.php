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
        if (array_key_exists($sNamespace, $this->_aPlugins)) {
            return $this->_aPlugins[$sNamespace];
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
        $oPlugin =& new $sClassName($sFilename);
        $this->_aPlugins[$sNamespace] =& $oPlugin;
        return $oPlugin;
    }

    function &getPlugins() {
        $aRet = array();
        foreach (array_keys($this->_aPluginDetails) as $sPluginName) {
            $aRet[] =& $this->getPlugin($sPluginName);
        }
        return $aRet;
    }
}

