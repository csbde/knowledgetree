<?php

class KTPlugin {
    var $sNamespace;
    var $sFilename = null;

    var $_aPortlets = array();
    var $_aTriggers = array();
    var $_aActions = array();
    var $_aPages = array();

    function KTPlugin($sFilename = null) {
        $this->sFilename = $sFilename;
    }

    function setFilename($sFilename) {
        $this->sFilename = $sFilename;
    }

    function registerPortlet($aLocation, $sPortletClassName, $sPortletNamespace, $sFilename = null) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aPortlets[$sPortletNamespace] = array($aLocation, $sPortletClassName, $sPortletNamespace, $sFilename, $this->sNamespace);
    }

    function registerTrigger($sAction, $sStage, $sTriggerClassName, $sTriggerNamespace, $sFilename = null) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aTriggers[$sTriggerNamespace] = array($sAction, $sStage, $sTriggerClassName, $sTriggerNamespace, $sFilename, $this->sNamespace);
    }

    function registerAction($sActionType, $sActionClassName, $sActionNamespace, $sFilename = null) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aActions[$sActionNamespace] = array($sActionType, $sActionClassName, $sActionNamespace, $sFilename, $this->sNamespace);
    }

    function registerPage($sWebPath, $sPageClassName, $sFilename = null) {
        $sFilename = $this->_fixFilename($sFilename);
        $sWebPath = sprintf("%s/%s", $this->sNamespace, $sWebPath);
        $this->_aPages[$sWebPath] = array($sWebPath, $sPageClassName, $sFilename, $this->sNamespace);
    }

    function getPagePath($sPath) {
        return sprintf('/plugin.php/%s/%s', $this->sNamespace, $sPath);
    }

    function _fixFilename($sFilename) {
        if (empty($sFilename)) {
            $sFilename = $this->sFilename;
        } else if (substr($sFilename, 0, 1) != '/') {
            if ($this->sFilename) {
                $sDirPath = dirname($this->sFilename);
                $sFilename = sprintf("%s/%s", $sDirPath, $sFilename);
            }
        }
        return $sFilename;
    }

    function register() {
        require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
        require_once(KT_LIB_DIR . '/actions/portletregistry.inc.php');
        require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
        require_once(KT_LIB_DIR . '/plugins/pageregistry.inc.php');

        $oPRegistry =& KTPortletRegistry::getSingleton();
        $oTRegistry =& KTTriggerRegistry::getSingleton();
        $oARegistry =& KTActionRegistry::getSingleton();
        $oPageRegistry =& KTPageRegistry::getSingleton();

        foreach ($this->_aPortlets as $k => $v) {
            call_user_func_array(array(&$oPRegistry, 'registerPortlet'), $v);
        }

        foreach ($this->_aTriggers as $k => $v) {
            call_user_func_array(array(&$oTRegistry, 'registerTrigger'), $v);
        }

        foreach ($this->_aActions as $k => $v) {
            call_user_func_array(array(&$oARegistry, 'registerAction'), $v);
        }

        foreach ($this->_aPages as $k => $v) {
            call_user_func_array(array(&$oPageRegistry, 'registerPage'), $v);
        }
    }
}

