<?php

class KTPageRegistry {
    var $aResources = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTPageRegistry')) {
            $GLOBALS['oKTPageRegistry'] = new KTPageRegistry;
        }
        return $GLOBALS['oKTPageRegistry'];
    }

    function registerPage($sPath, $sClassName, $sFilename = null) {
        $this->aResources[$sPath] = array($sPath, $sClassName, $sFilename);
    }

    function getPage($sPath) {
        $aInfo = KTUtil::arrayGet($this->aResources, $sPath);
        if (empty($aInfo)) {
            return null;
        }
        $sClassName = $aInfo[1];
        $sFilename = $aInfo[2];
        if ($sFilename) {
            require_once($sFilename);
        }
        return new $sClassName;
    }
}

