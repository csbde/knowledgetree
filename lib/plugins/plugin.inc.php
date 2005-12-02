<?php

class KTPlugin {
    var $sNamespace;
    var $sFilename = null;

    
    var $_aPortlets = array();
    var $_aTriggers = array();
    var $_aActions = array();
    var $_aPages = array();
    var $_aAuthenticationProviders = array();
    var $_aAdminCategories = array();
    var $_aAdminPages = array();
    var $_aDashlets = array();

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
        return sprintf($GLOBALS['KTRootUrl'] . '/plugin.php/%s/%s', $this->sNamespace, $sPath);
    }

    function registerAuthenticationProvider($sName, $sClass, $sNamespace, $sFilename = null) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aAuthenticationProviders[$sNamespace] = array($sName, $sClass, $sNamespace, $sFilename, $this->sNamespace);
    }

//registerLocation($sName, $sClass, $sCategory, $sTitle, $sDescription, $sDispatcherFilePath = null, $sURL = null)
    function registerAdminPage($sName, $sClass, $sCategory, $sTitle, $sDescription, $sFilename) {
        $sFullname = $sCategory . '/' . $sName;
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aAdminPages[$sFullname] = array($sName, $sClass, $sCategory, $sTitle, $sDescription, $sFilename, null, $this->sNamespace);
    }

    function registerAdminCategories($sPath, $sName, $sDescription) {
        $this->_aAdminCategories[$sPath] = array($sPath, $sName, $sDescription);
    }
    
    function registerDashlet($sClassName, $sNamespace, $sFilename) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aDashlets[$sNamespace] = array($sClassName, $sNamespace, $sFilename, $this->sNamespace);
    }

    function _fixFilename($sFilename) {
        if (empty($sFilename)) {
            $sFilename = $this->sFilename;
        } else if (OS_WINDOWS && (substr($sFilename, 1, 2) == ':\\')) {
            $sFilename = $this->sFilename;
        } else if (OS_WINDOWS && (substr($sFilename, 1, 2) == ':/')) {
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
        require_once(KT_LIB_DIR . '/authentication/authenticationproviderregistry.inc.php');
        require_once(KT_LIB_DIR . "/plugins/KTAdminNavigation.php"); 
        require_once(KT_LIB_DIR . "/dashboard/dashletregistry.inc.php"); 

        $oPRegistry =& KTPortletRegistry::getSingleton();
        $oTRegistry =& KTTriggerRegistry::getSingleton();
        $oARegistry =& KTActionRegistry::getSingleton();
        $oPageRegistry =& KTPageRegistry::getSingleton();
        $oAPRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oAdminRegistry =& KTAdminNavigationRegistry::getSingleton(); 
        $oDashletRegistry =& KTDashletRegistry::getSingleton();

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

        foreach ($this->_aAuthenticationProviders as $k => $v) {
            call_user_func_array(array(&$oAPRegistry, 'registerAuthenticationProvider'), $v);
        }

        foreach ($this->_aAdminCategories as $k => $v) {
            call_user_func_array(array(&$oAdminRegistry, 'registerCategory'), $v);
        }

        foreach ($this->_aAdminPages as $k => $v) {
            call_user_func_array(array(&$oAdminRegistry, 'registerLocation'), $v);
        }
        
        foreach ($this->_aDashlets as $k => $v) {
            call_user_func_array(array(&$oDashletRegistry, 'registerDashlet'), $v);
        }
    }
}

