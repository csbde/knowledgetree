<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . '/database/sqlfile.inc.php');

class KTPlugin {
    var $sNamespace;
    var $sFilename = null;
    var $bAlwaysInclude = false;
    var $iVersion = 0;
    var $iOrder = 0;
    var $sFriendlyName = null;
    var $sSQLDir = null;
    
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
    var $_aHelpLanguage = array();
    var $_aWFTriggers = array();
    var $_aColumns = array();    
    var $_aViews = array();       
    var $_aNotificationHandlers = array();       
    var $_aTemplateLocations = array();       

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
    
    function registerWorkflowTrigger($sNamespace, $sTriggerClassName, $sFilename = null) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aWFTriggers[$sNamespace] = array($sNamespace, $sTriggerClassName, $sFilename);
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
    
    function registerHelpLanguage($sPlugin, $sLanguage, $sBasedir) {
        $this->_aHelpLanguage[$sLanguage] = array($sPlugin, $sLanguage, $sBasedir);
    }
    
    function registerColumn($sName, $sNamespace, $sClassName, $sFile) {
        $sFile = $this->_fixFilename($sFile);
        $this->_aColumns[$sNamespace] = array($sName, $sNamespace, $sClassName, $sFile);
    }    
    
    function registerView($sName, $sNamespace) {
        $this->_aViews[$sNamespace] = array($sName, $sNamespace);
    }        

    function registerNotificationHandler($sName, $sNamespace, $sPath) {
        $sPath = $this->_fixFilename($sPath);
        $this->_aNotificationHandlers[$sNamespace] = array($sNamespace, $sName, $sPath);
    }        

    function registerTemplateLocation($sName, $sPath) {
        $sPath = $this->_fixFilename($sPath);
        $this->_aTemplateLocations[$sName] = array($sName, $sPath);
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
        require_once(KT_LIB_DIR . "/help/help.inc.php");
        require_once(KT_LIB_DIR . "/workflow/workflowutil.inc.php");
        require_once(KT_LIB_DIR . "/browse/columnregistry.inc.php");        

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
        
        foreach ($this->_aHelpLanguage as $k => $v) {
            call_user_func_array(array(&$oKTHelpRegistry, 'registerHelp'), $v);
        }
        
        foreach ($this->_aWFTriggers as $k => $v) {
            call_user_func_array(array(&$oWFTriggerRegistry, 'registerWorkflowTrigger'), $v);
        }
        
        foreach ($this->_aColumns as $k => $v) {
            call_user_func_array(array(&$oColumnRegistry, 'registerColumn'), $v);
        }        
        
        foreach ($this->_aViews as $k => $v) {
            call_user_func_array(array(&$oColumnRegistry, 'registerView'), $v);
        }                

        foreach ($this->_aNotificationHandlers as $k => $v) {
            call_user_func_array(array(&$oNotificationHandlerRegistry, 'registerNotificationHandler'), $v);
        }                

        foreach ($this->_aTemplateLocations as $k => $v) {
            call_user_func_array(array(&$oTemplating, 'addLocation'), $v);
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
    
    function upgradePlugin($iStart, $iEnd) {
        if (is_null($this->sSQLDir)) { 
            return $iEnd; // no db changes, must reach the "end".
        }
        global $default;
        DBUtil::setupAdminDatabase();
        for ($i = $iStart; $i <= $iEnd; $i++) {
            $sqlfile = sprintf("%s/upgradeto%d.sql", $this->sSQLDir, $i);

            if (!file_exists($sqlfile)) {
                continue; // skip it.
            }
            $queries = SQLFile::sqlFromFile($sqlfile);            
            $res = DBUtil::runQueries($queries, $default->_admindb);

            if (PEAR::isError($res)) {
                return $i; // break out completely, indicating how far we got pre-error.
            }
        }
        return $iEnd;
    }

    function register() {
        $oEntity = KTPluginEntity::getByNamespace($this->sNamespace);
        $friendly_name = '';
        if (!empty($this->sFriendlyName)) { $friendly_name = $this->sFriendlyName; }
        if (!PEAR::isError($oEntity)) {

            // check for upgrade.
            $iEndVersion = 0; // dest.
            if ($this->iVersion != $oEntity->getVersion()) {
                // capture the filname version.
                // remember to -start- the upgrade from the "next" version
                $iEndVersion = $this->upgradePlugin($oEntity->getVersion()+1, $this->iVersion);
            }
            if ($iEndVersion != $this->iVersion) {
                // we obviously failed.
                $oEntity->updateFromArray(array(
                    'path' => $this->stripKtDir($this->sFilename),
                    'version' => $iEndVersion,   // as far as we got.
                    'disabled' => true,
                    'unavailable' => false,
                    'friendlyname' => $friendly_name,
                ));
                // FIXME we -really- need to raise an error here, somehow.
                return $oEntity; 
            } else {
                $oEntity->updateFromArray(array(
                    'path' => $this->stripKtDir($this->sFilename),
                    'version' => $this->iVersion,
                    'unavailable' => false,
                    'friendlyname' => $friendly_name,
                ));
                return $oEntity;
            }
        }
        $disabled = 1;
        if ($this->bAlwaysInclude || $this->autoRegister) { $disabled = 0; }
        $iEndVersion = $this->upgradePlugin(0, $this->iVersion);
        $oEntity = KTPluginEntity::createFromArray(array(
            'namespace' => $this->sNamespace,
            'path' => $this->stripKtDir($this->sFilename),
            'version' => $iEndVersion,
            'disabled' => $disabled,
            'unavailable' => false,
            'friendlyname' => $friendly_name,
        ));
        if (PEAR::isError($oEntity)) {
            return $oEntity;
        }
        return true;
    }
}

