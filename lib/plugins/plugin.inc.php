<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 *
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
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
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
    var $showInAdmin = true;

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
    var $_aWidgets = array();
    var $_aValidators = array();
    var $_aCriteria = array();
    var $_aInterceptors = array();

    function KTPlugin($sFilename = null) {
        $this->sFilename = $sFilename;
    }

    function setFilename($sFilename) {
        $this->sFilename = $sFilename;
    }

    function registerPortlet($aLocation, $sPortletClassName, $sPortletNamespace, $sFilename = null) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aPortlets[$sPortletNamespace] = array($aLocation, $sPortletClassName, $sPortletNamespace, $sFilename, $this->sNamespace);

        // Register helper in DB
        if(is_array($aLocation)){
            $aLocation = serialize($aLocation);
        }
        $params = $aLocation.'|'.$sPortletClassName.'|'.$sPortletNamespace.'|'.$sFilename.'|'.$this->sNamespace;
        $this->registerPluginHelper($sPortletNamespace, $sPortletClassName, $sFilename, $params, 'general', 'portlet');
    }

    function registerTrigger($sAction, $sStage, $sTriggerClassName, $sTriggerNamespace, $sFilename = null) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aTriggers[$sTriggerNamespace] = array($sAction, $sStage, $sTriggerClassName, $sTriggerNamespace, $sFilename, $this->sNamespace);

        // Register helper in DB
        $params = $sAction.'|'.$sStage.'|'.$sTriggerClassName.'|'.$sTriggerNamespace.'|'.$sFilename.'|'.$this->sNamespace;
        $this->registerPluginHelper($sTriggerNamespace, $sTriggerClassName, $sFilename, $params, 'general', 'trigger');
    }

    function registerAction($sActionType, $sActionClassName, $sActionNamespace, $sFilename = null) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aActions[$sActionNamespace] = array($sActionType, $sActionClassName, $sActionNamespace, $sFilename, $this->sNamespace);

        // Register helper in DB
        $params = $sActionType.'|'.$sActionClassName.'|'.$sActionNamespace.'|'.$sFilename.'|'.$this->sNamespace;
        $this->registerPluginHelper($sActionNamespace, $sActionClassName, $sFilename, $params, 'general', 'action');
    }

    function registerPage($sWebPath, $sPageClassName, $sFilename = null) {
        $sFilename = $this->_fixFilename($sFilename);
        $sWebPath = sprintf("%s/%s", $this->sNamespace, $sWebPath);

        $this->_aPages[$sWebPath] = array($sWebPath, $sPageClassName, $sFilename, $this->sNamespace);

        // Register helper in DB
        $params = $sWebPath.'|'.$sPageClassName.'|'.$sFilename.'|'.$this->sNamespace;
        $this->registerPluginHelper($sWebPath, $sPageClassName, $sFilename, $params, 'general', 'page');
    }

    function registerWorkflowTrigger($sNamespace, $sTriggerClassName, $sFilename = null) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aWFTriggers[$sNamespace] = array($sNamespace, $sTriggerClassName, $sFilename);

        // Register helper in DB
        $params = $sNamespace.'|'.$sTriggerClassName.'|'.$sFilename;
        $this->registerPluginHelper($sNamespace, $sTriggerClassName, $sFilename, $params, 'general', 'workflow_trigger');
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

        // Register helper in DB
        $params = $sName.'|'.$sClass.'|'.$sNamespace.'|'.$sFilename.'|'.$this->sNamespace;
        $this->registerPluginHelper($sNamespace, $sClass, $sFilename, $params, 'general', 'authentication_provider');
    }

//registerLocation($sName, $sClass, $sCategory, $sTitle, $sDescription, $sDispatcherFilePath = null, $sURL = null)
    function registerAdminPage($sName, $sClass, $sCategory, $sTitle, $sDescription, $sFilename) {
        $sFullname = $sCategory . '/' . $sName;
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aAdminPages[$sFullname] = array($sName, $sClass, $sCategory, $sTitle, $sDescription, $sFilename, null, $this->sNamespace);

        // Register helper in DB
        $params = $sName.'|'.$sClass.'|'.$sCategory.'|'.$sTitle.'|'.$sDescription.'|'.$sFilename.'|'.null.'|'.$this->sNamespace;
        $this->registerPluginHelper($sFullname, $sClass, $sFilename, $params, 'general', 'admin_page');
    }

    function registerAdminCategory($sPath, $sName, $sDescription) {
        $this->_aAdminCategories[$sPath] = array($sPath, $sName, $sDescription);

        // Register helper in DB
        $params = $sPath.'|'.$sName.'|'.$sDescription;
        $this->registerPluginHelper($sPath, $sName, $sPath, $params, 'general', 'admin_category');
    }

    /**
     * Register a new dashlet
     *
     * @param string $sClassName
     * @param string $sNamespace
     * @param string $sFilename
     */
    function registerDashlet($sClassName, $sNamespace, $sFilename) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aDashlets[$sNamespace] = array($sClassName, $sNamespace, $sFilename, $this->sNamespace);

        $params = $sClassName.'|'.$sNamespace.'|'.$sFilename.'|'.$this->sNamespace;
        $this->registerPluginHelper($sNamespace, $sClassName, $sFilename, $params, 'dashboard', 'dashlet');
    }

    function registeri18n($sDomain, $sPath) {
        $sPath = $this->_fixFilename($sPath);
        $this->_ai18n[$sDomain] = array($sDomain, $sPath);

        // Register helper in DB
        $params = $sDomain.'|'.$sPath;
        $this->registerPluginHelper($sDomain, $sDomain, $sPath, $params, 'general', 'i18n');
    }

    function registeri18nLang($sDomain, $sLang, $sPath) {
        if ($sPath !== "default") {
            $sPath = $this->_fixFilename($sPath);
        }
        $this->_ai18nLang["$sDomain/$sLang"] = array($sDomain, $sLang, $sPath);

        // Register helper in DB
        $params = $sDomain.'|'.$sLang.'|'.$sPath;
        $this->registerPluginHelper("$sDomain/$sLang", $sDomain, $sPath, $params, 'general', 'i18nlang');
    }

    function registerLanguage($sLanguage, $sLanguageName) {
        $this->_aLanguage[$sLanguage] = array($sLanguage, $sLanguageName);

        // Register helper in DB
        $params = $sLanguage.'|'.$sLanguageName;
        $this->registerPluginHelper($sLanguage, $sClassName, $sFilename, $params, 'general', 'language');
    }

    function registerHelpLanguage($sPlugin, $sLanguage, $sBasedir) {
        $sBasedir = (!empty($sBasedir)) ? $this->_fixFilename($sBasedir) : '';
        $this->_aHelpLanguage[$sLanguage] = array($sPlugin, $sLanguage, $sBasedir);

        // Register helper in DB
        $params = $sPlugin.'|'.$sLanguage.'|'.$sBasedir;
        $this->registerPluginHelper($sLanguage, $sClassName, '', $params, 'general', 'help_language');
    }

    function registerColumn($sName, $sNamespace, $sClassName, $sFile) {
        $sFile = $this->_fixFilename($sFile);
        $this->_aColumns[$sNamespace] = array($sName, $sNamespace, $sClassName, $sFile);

        // Register helper in DB
        $params = $sName.'|'.$sNamespace.'|'.$sClassName.'|'.$sFile;
        $this->registerPluginHelper($sNamespace, $sClassName, $sFile, $params, 'general', 'column');
    }

    function registerView($sName, $sNamespace) {
        $this->_aViews[$sNamespace] = array($sName, $sNamespace);

        // Register helper in DB
        $params = $sName.'|'.$sNamespace;
        $this->registerPluginHelper($sNamespace, '', '', $params, 'general', 'view');
    }

    function registerNotificationHandler($sName, $sNamespace, $sPath) {
        $sPath = $this->_fixFilename($sPath);
        $this->_aNotificationHandlers[$sNamespace] = array($sNamespace, $sName, $sPath);

        // Register helper in DB
        $params = $sNamespace.'|'.$sName.'|'.$sPath;
        $this->registerPluginHelper($sNamespace, $sName, $sPath, $params, 'general', 'notification_handler');
    }

    function registerTemplateLocation($sName, $sPath) {
        $sPath = $this->_fixFilename($sPath);
        $this->_aTemplateLocations[$sName] = array($sName, $sPath);

        // Register helper in DB
        $params = $sName.'|'.$sPath;
        $this->registerPluginHelper($sName, $sName, $sPath, $params, 'general', 'template_location');
    }


    /**
     * Register a new widget
     *
     * @param unknown_type $sClassname
     * @param unknown_type $sNamespace
     * @param unknown_type $sPath
     */
    function registerWidget($sClassname, $sNamespace, $sPath) {
        $sPath = $this->_fixFilename($sPath);
        $this->_aWidgets[$sNamespace] = array($sClassname, $sNamespace, $sPath);

        // Register helper in DB
        $params = $sClassname.'|'.$sNamespace.'|'.$sPath;
        $this->registerPluginHelper($sNamespace, $sClassname, $sPath, $params, 'general', 'widget');
    }

    function registerValidator($sClassname, $sNamespace, $sPath) {
        $sPath = $this->_fixFilename($sPath);
        $this->_aValidators[$sNamespace] = array($sClassname, $sNamespace, $sPath);

        // Register helper in DB
        $params = $sClassname.'|'.$sNamespace.'|'.$sPath;
        $this->registerPluginHelper($sNamespace, $sClassname, $sPath, $params, 'general', 'validator');
    }


    function registerCriterion($sClassName, $sNamespace, $sFilename = null, $aInitialize = null) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aCriteria[$sNamespace] = array($sClassName, $sNamespace, $sFilename, $aInitialize);

        // Register helper in DB
        if(is_array($aInitialize)){
            $aInitialize = serialize($aInitialize);
        }

        $params = $sClassName.'|'.$sNamespace.'|'.$sFilename.'|'.$aInitialize;
        $this->registerPluginHelper($sNamespace, $sClassName, $sFilename, $params, 'general', 'criterion');
    }

    function registerInterceptor($sClassname, $sNamespace, $sPath = null) {
        $sPath = $this->_fixFilename($sPath);
        $this->_aInterceptors[$sNamespace] = array($sClassname, $sNamespace, $sPath);

        // Register helper in DB
        $params = $sClassname.'|'.$sNamespace.'|'.$sPath;
        $this->registerPluginHelper($sNamespace, $sClassname, $sPath, $params, 'general', 'interceptor');
    }

    /**
     * Register a new document processor class
     * See Search2/DocumentProcessor
     */
    function registerProcessor($sClassname, $sNamespace, $sPath)
    {
        $sPath = $this->_fixFilename($sPath);
        $this->_aInterceptors[$sNamespace] = array($sClassname, $sNamespace, $sPath);

        // Register helper in DB
        $params = $sClassname.'|'.$sNamespace.'|'.$sPath;
        $this->registerPluginHelper($sNamespace, $sClassname, $sPath, $params, 'process', 'processor');
    }

    /* ** Refactor into another class ** */
    /**
     * Register the plugin in the DB
     *
     * @param unknown_type $sClassName
     * @param unknown_type $path
     * @param unknown_type $object
     * @param unknown_type $type
     */
    function registerPluginHelper($sNamespace, $sClassName, $path, $object, $view, $type) {

        $sql = "SELECT id FROM plugin_helper WHERE namespace = '{$sNamespace}' AND classtype = '{$type}'";
        $res = DBUtil::getOneResult($sql);

        // if record exists - ignore it.
        if(!empty($res)){
            return true;
        }

        $aValues = array();
        $aValues['namespace'] = $sNamespace;
        $aValues['plugin'] = (!empty($this->sNamespace)) ? $this->sNamespace : $sNamespace;
        $aValues['classname'] = $sClassName;
        $aValues['pathname'] = $path;
        $aValues['object'] = $object;
        $aValues['viewtype'] = $view;
        $aValues['classtype'] = $type;

        // Insert into DB
        $res = DBUtil::autoInsert('plugin_helper', $aValues);
        if(PEAR::isError($res)){
            return $res;
        }
        return true;
    }

    function deRegisterPluginHelper($sNamespace, $sClass) {
        $aWhere['namespace'] = $sNamespace;
        $aWhere['classtype'] = $sClass;
        $res = DBUtil::whereDelete('plugin_helper', $aWhere);
        return $res;
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

        $sKtDir = str_replace('\\', '/', KT_DIR);
        $sFilename = realpath($sFilename);
        $sFilename = str_replace('\\', '/', $sFilename);
        $sFilename = str_replace($sKtDir.'/', '', $sFilename);
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

    /**
     * Load the actions, portlets, etc as part of the parent plugin
     *
     */
    function load() {
        // Include any required resources, javascript files, etc
        $res = $this->run_setup();

        if(!$res){
            return false;
        }
        return true;
    }

    function loadHelpers() {

        // Get actions, portlets, etc, create arrays as part of plugin
        $query = "SELECT * FROM plugin_helper h WHERE plugin = '{$this->sNamespace}'";
        $aPluginHelpers = DBUtil::getResultArray($query);

        if(!empty($aPluginHelpers)){
            foreach ($aPluginHelpers as $plugin) {
                $sName = $plugin['namespace'];
            	$sParams = $plugin['object'];
            	$aParams = explode('|', $sParams);
            	$sClassType = $plugin['classtype'];

            	switch ($sClassType) {
            	    case 'portlet':
            	        $aLocation = unserialize($aParams[0]);
            	        if($aLocation != false){
        	               $aParams[0] = $aLocation;
            	        }
                        $this->_aPortlets[$sName] = $aParams;
            	        break;

            	    case 'trigger':
            	        $this->_aTriggers[$sName] = $aParams;
            	        break;

            	    case 'action':
            	        $this->_aActions[$sName] = $aParams;
            	        break;

            	    case 'page':
            	        $this->_aPages[$sName] = $aParams;
            	        break;

            	    case 'authentication_provider':
            	        $this->_aAuthenticationProviders[$sName] = $aParams;
            	        break;

            	    case 'admin_category':
            	        $this->_aAdminCategories[$sName] = $aParams;
            	        break;

            	    case 'admin_page':
            	        $this->_aAdminPages[$sName] = $aParams;
            	        break;

            	    case 'dashlet':
            	        $this->_aDashlets[$sName] = $aParams;
            	        break;

            	    case 'i18n':
            	        $this->_ai18n[$sName] = $aParams;
            	        break;

            	    case 'i18nlang':
            	        $this->_ai18nLang[$sName] = $aParams;
            	        break;

            	    case 'language':
            	        $this->_aLanguage[$sName] = $aParams;
            	        break;

            	    case 'help_language':
            	        $this->_aHelpLanguage[$sName] = $aParams;
            	        break;

            	    case 'workflow_trigger':
            	        $this->_aWFTriggers[$sName] = $aParams;
            	        break;

            	    case 'column':
            	        $this->_aColumns[$sName] = $aParams;
            	        break;

            	    case 'view':
            	        $this->_aViews[$sName] = $aParams;
            	        break;

            	    case 'notification_handler':
            	        $this->_aNotificationHandlers[$sName] = $aParams;
            	        break;

            	    case 'template_location':
            	        $this->_aTemplateLocations[$sName] = $aParams;
            	        break;

            	    case 'criterion':
            	        $aInit = unserialize($aParams[3]);
            	        if($aInit != false){
        	               $aParams[3] = $aInit;
            	        }
            	        $this->_aCriteria[$sName] = $aParams;
            	        break;

            	    case 'widget':
            	        $this->_aWidgets[$sName] = $aParams;
            	        break;

            	    case 'validator':
            	        $this->_aValidators[$sName] = $aParams;
            	        break;

            	    case 'interceptor':
            	        $this->_aInterceptors[$sName] = $aParams;
            	        break;
            	}
        	}
        }
        return true;
    }

    /**
     * Original load function for the plugins
     * @deprecated
     */
    function load2() {
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
        require_once(KT_LIB_DIR . "/widgets/widgetfactory.inc.php");
        require_once(KT_LIB_DIR . "/validation/validatorfactory.inc.php");
        require_once(KT_LIB_DIR . "/browse/columnregistry.inc.php");
        require_once(KT_LIB_DIR . "/browse/criteriaregistry.php");
        require_once(KT_LIB_DIR . "/authentication/interceptorregistry.inc.php");

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
        $oWidgetFactory =& KTWidgetFactory::getSingleton();
        $oValidatorFactory =& KTValidatorFactory::getSingleton();
        $oCriteriaRegistry =& KTCriteriaRegistry::getSingleton();
        $oInterceptorRegistry =& KTInterceptorRegistry::getSingleton();

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

        foreach ($this->_aCriteria as $k => $v) {
            call_user_func_array(array(&$oCriteriaRegistry, 'registerCriterion'), $v);
        }

        foreach ($this->_aWidgets as $k => $v) {
            call_user_func_array(array(&$oWidgetFactory, 'registerWidget'), $v);
        }

        foreach ($this->_aValidators as $k => $v) {
            call_user_func_array(array(&$oValidatorFactory, 'registerValidator'), $v);
        }

        foreach ($this->_aInterceptors as $k => $v) {
            call_user_func_array(array(&$oInterceptorRegistry, 'registerInterceptor'), $v);
        }
    }

    function setup() {
        return;
    }

    function run_setup() {
        return true;
    }

    function setAvailability($sNamespace, $bAvailable = true){
    	$aValues = array('unavailable' => $bAvailable);
    	$aWhere = array('namespace' => $sNamespace);
    	$res = DBUtil::whereUpdate('plugins', $aValues, $aWhere);
    	return $res;
    }

    function stripKtDir($sFilename) {
        if (strpos($sFilename, KT_DIR) === 0 ||strpos($sFilename, realpath(KT_DIR)) === 0) {
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
        $iOrder = $this->iOrder;
        global $default;

        if (!empty($this->sFriendlyName)) { $friendly_name = $this->sFriendlyName; }
        if (!PEAR::isError($oEntity)) {

            // check for upgrade.
            $iEndVersion = 0; // dest.
            if ($this->iVersion != $oEntity->getVersion()) {
                // capture the filname version.
                // remember to -start- the upgrade from the "next" version
                $iEndVersion = $this->upgradePlugin($oEntity->getVersion()+1, $this->iVersion);

                if ($iEndVersion != $this->iVersion) {
                    $default->log->error("Plugin register: {$friendly_name}, namespace: {$this->sNamespace} failed to upgrade properly. Original version: {$oEntity->getVersion()}, upgrading to version {$this->iVersion}, current version {$iEndVersion}");

                    // we obviously failed.
                    $oEntity->updateFromArray(array(
                    'path' => $this->stripKtDir($this->sFilename),
                    'version' => $iEndVersion,   // as far as we got.
                    'disabled' => true,
                    'unavailable' => false,
                    'friendlyname' => $friendly_name,
                    ));
                    // FIXME we -really- need to raise an error here, somehow.

                } else {
                    $default->log->debug("Plugin register: {$friendly_name}, namespace: {$this->sNamespace} upgraded. Original version: {$oEntity->getVersion()}, upgrading to version {$this->iVersion}, current version {$iEndVersion}");

                    $oEntity->updateFromArray(array(
                    'path' => $this->stripKtDir($this->sFilename),
                    'version' => $this->iVersion,
                    'unavailable' => false,
                    'friendlyname' => $friendly_name,
                    'orderby' => $iOrder,
                    'list_admin' => $this->showInAdmin,
                    ));

                }
            }else{
                // Update the plugin path, in case it has moved
                $oEntity->updateFromArray(array(
                    'path' => $this->stripKtDir($this->sFilename)
                ));
            }
            /* ** Quick fix for optimisation. Reread must run plugin setup. ** */
            $this->setup();
            return $oEntity;
        }
        if(PEAR::isError($oEntity) && !is_a($oEntity, 'KTEntityNoObjects')){
            $default->log->error("Plugin register: the plugin {$friendly_name}, namespace: {$this->sNamespace} returned an error: ".$oEntity->getMessage());
            return $oEntity;
        }

        $disabled = 1;

        if ($this->bAlwaysInclude || $this->autoRegister) {
            $disabled = 0;
        }

        $default->log->debug("Plugin register: creating {$friendly_name}, namespace: {$this->sNamespace}");

        $iEndVersion = $this->upgradePlugin(0, $this->iVersion);
        $oEntity = KTPluginEntity::createFromArray(array(
            'namespace' => $this->sNamespace,
            'path' => $this->stripKtDir($this->sFilename),
            'version' => $iEndVersion,
            'disabled' => $disabled,
            'unavailable' => false,
            'friendlyname' => $friendly_name,
            'orderby' => $iOrder,
            'list_admin' => $this->showInAdmin,
            ));

        if (PEAR::isError($oEntity)) {
            $default->log->error("Plugin register: the plugin, {$friendly_name}, namespace: {$this->sNamespace} returned an error on creation: ".$oEntity->getMessage());
            return $oEntity;
        }

        /* ** Quick fix for optimisation. Reread must run plugin setup. ** */
        $this->setup();
        return true;
    }

    function getURLPath($filename = null)
    {
		$config = KTConfig::getSingleton();
		$dir = $config->get('KnowledgeTree/fileSystemRoot');

		$path = substr(dirname($this->sFilename), strlen($dir));
		if (!is_null($filename))
		{
			$path .= '/' . $filename;
		}
		return $path;
    }

}

?>
