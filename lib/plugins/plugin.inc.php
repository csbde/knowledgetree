<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

class KTPlugin {
    var $sNamespace;
    var $sFilename = null;
    var $bAlwaysInclude = false;
    var $iVersion = 0;
    var $iOrder = 0;
    
    var $autoRegister = false;
    
    var $_aPortlets = array();
    var $_aTriggers = array();
    var $_aActions = array();
    var $_aPages = array();
    var $_aAuthenticationProviders = array();
    var $_aAdminCategories = array();
    var $_aAdminPages = array();
    var $_aDashlets = array();
    var $_ai18n = array();
    var $_ai18nLang = array();
    var $_aLanguage = array();

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
        $sExt = ".php";
        if (KTUtil::arrayGet($_SERVER, 'kt_no_extensions')) {
            $sExt = "";
        }
        $oKTConfig =& KTConfig::getSingleton();
        if ($oKTConfig->get("KnowledgeTree/pathInfoSupport")) {
            return sprintf('%s/plugin%s/%s/%s', $GLOBALS['KTRootUrl'], $sExt, $this->sNamespace, $sPath);
        } else {
            return sprintf('%s/plugin%s?kt_path_info=%s/%s', $GLOBALS['KTRootUrl'], $sExt, $this->sNamespace, $sPath);
        }
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

    function registerAdminCategory($sPath, $sName, $sDescription) {
        $this->_aAdminCategories[$sPath] = array($sPath, $sName, $sDescription);
    }
    
    function registerDashlet($sClassName, $sNamespace, $sFilename) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aDashlets[$sNamespace] = array($sClassName, $sNamespace, $sFilename, $this->sNamespace);
    }

    function registeri18n($sDomain, $sPath) {
        $sPath = $this->_fixFilename($sPath);
        $this->_ai18n[$sDomain] = array($sDomain, $sPath);
    }

    function registeri18nLang($sDomain, $sLang, $sPath) {
        if ($sPath !== "default") {
            $sPath = $this->_fixFilename($sPath);
        }
        $this->_ai18nLang["$sDomain/$sLang"] = array($sDomain, $sLang, $sPath);
    }

    function registerLanguage($sLanguage, $sLanguageName) {
        $this->_aLanguage[$sLanguage] = array($sLanguage, $sLanguageName);
    }

    function _fixFilename($sFilename) {
        if (empty($sFilename)) {
            $sFilename = $this->sFilename;
        }
        if (!KTUtil::isAbsolutePath($sFilename)) {
            if ($this->sFilename) {
                $sDirPath = dirname($this->sFilename);
                $sFilename = sprintf("%s/%s", $sDirPath, $sFilename);
            }
        }
        return $sFilename;
    }

    function isRegistered() {
        if ($this->bAlwaysInclude) {
            return true;
        }

        require_once(KT_LIB_DIR . '/plugins/pluginentity.inc.php');
        $oEntity = KTPluginEntity::getByNamespace($this->sNamespace);
        if (PEAR::isError($oEntity)) {
            if (is_a($oEntity, 'KTEntityNoObjects')) {
                // plugin not registered in database

                // XXX: nbm: Show an error on the page that a plugin
                // isn't registered or something, perhaps.
                return false;
            }
            return false;
        }
        if (!is_a($oEntity, 'KTPluginEntity')) {
            print "isRegistered\n";
            var_dump($oEntity);
            exit(0);
        }
        if ($oEntity->getDisabled()) {
            return false;
        }
        return true;
    }

    function load() {
        if (!$this->isRegistered()) {
            return;
        }
        $this->setup();

        require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
        require_once(KT_LIB_DIR . '/actions/portletregistry.inc.php');
        require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
        require_once(KT_LIB_DIR . '/plugins/pageregistry.inc.php');
        require_once(KT_LIB_DIR . '/authentication/authenticationproviderregistry.inc.php');
        require_once(KT_LIB_DIR . "/plugins/KTAdminNavigation.php"); 
        require_once(KT_LIB_DIR . "/dashboard/dashletregistry.inc.php"); 
        require_once(KT_LIB_DIR . "/i18n/i18nregistry.inc.php"); 

        $oPRegistry =& KTPortletRegistry::getSingleton();
        $oTRegistry =& KTTriggerRegistry::getSingleton();
        $oARegistry =& KTActionRegistry::getSingleton();
        $oPageRegistry =& KTPageRegistry::getSingleton();
        $oAPRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oAdminRegistry =& KTAdminNavigationRegistry::getSingleton(); 
        $oDashletRegistry =& KTDashletRegistry::getSingleton();
        $oi18nRegistry =& KTi18nRegistry::getSingleton();

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

        foreach ($this->_ai18n as $k => $v) {
            call_user_func_array(array(&$oi18nRegistry, 'registeri18n'), $v);
        }

        foreach ($this->_ai18nLang as $k => $v) {
            call_user_func_array(array(&$oi18nRegistry, 'registeri18nLang'), $v);
        }

        foreach ($this->_aLanguage as $k => $v) {
            call_user_func_array(array(&$oi18nRegistry, 'registerLanguage'), $v);
        }
    }

    function setup() {
        return;
    }

    function stripKtDir($sFilename) {
        if (strpos($sFilename, KT_DIR) === 0) {
            return substr($sFilename, strlen(KT_DIR) + 1);
        }
        return $sFilename;
    }

    function register() {
        $oEntity = KTPluginEntity::getByNamespace($this->sNamespace);
        if (!PEAR::isError($oEntity)) {
            $oEntity->updateFromArray(array(
                'path' => $this->stripKtDir($this->sFilename),
                'version' => $this->iVersion,
            ));
            return $oEntity;
        }
        $disabled = 1;
        if ($this->bAlwaysInclude || $this->autoRegister) { $disabled = 0; }
        $oEntity = KTPluginEntity::createFromArray(array(
            'namespace' => $this->sNamespace,
            'path' => $this->stripKtDir($this->sFilename),
            'version' => $this->iVersion,
            'disabled' => $disabled,
        ));
        if (PEAR::isError($oEntity)) {
            return $oEntity;
        }
        return true;
    }
}

