<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
    public $sNamespace;
    public $sFilename = null;
    public $bAlwaysInclude = false;
    public $iVersion = 0;
    public $iOrder = 0;
    public $sFriendlyName = null;
    public $sSQLDir = null;

    public $autoRegister = false;
    public $showInAdmin = true;

    public $_aPortlets = array();
    public $_aTriggers = array();
    public $_aActions = array();
    public $_aPages = array();
    public $_aAuthenticationProviders = array();
    public $_aAdminCategories = array();
    public $_aAdminPages = array();
    public $_aDashlets = array();
    public $_ai18n = array();
    public $_ai18nLang = array();
    public $_aLanguage = array();
    public $_aHelpLanguage = array();
    public $_aWFTriggers = array();
    public $_aColumns = array();
    public $_aViews = array();
    public $_aNotificationHandlers = array();
    public $_aTemplateLocations = array();
    public $_aWidgets = array();
    public $_aValidators = array();
    public $_aCriteria = array();
    public $_aInterceptors = array();

    public function KTPlugin($sFilename = null)
    {
        $this->sFilename = $sFilename;
    }

    public function setFilename($sFilename)
    {
        $this->sFilename = $sFilename;
    }

    public function registerPortlet($aLocation, $sPortletClassName, $sPortletNamespace, $sFilename = null)
    {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aPortlets[$sPortletNamespace] = array($aLocation, $sPortletClassName, $sPortletNamespace, $sFilename, $this->sNamespace);

        // Register helper in DB
        if (is_array($aLocation)) {
            $aLocation = serialize($aLocation);
        }
        $params = $aLocation.'|'.$sPortletClassName.'|'.$sPortletNamespace.'|'.$sFilename.'|'.$this->sNamespace;
        $this->registerPluginHelper($sPortletNamespace, $sPortletClassName, $sFilename, $params, 'general', 'portlet');
    }

    public function registerTrigger($sAction, $sStage, $sTriggerClassName, $sTriggerNamespace, $sFilename = null)
    {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aTriggers[$sTriggerNamespace] = array($sAction, $sStage, $sTriggerClassName, $sTriggerNamespace, $sFilename, $this->sNamespace);

        // Register helper in DB
        $params = $sAction.'|'.$sStage.'|'.$sTriggerClassName.'|'.$sTriggerNamespace.'|'.$sFilename.'|'.$this->sNamespace;
        $this->registerPluginHelper($sTriggerNamespace, $sTriggerClassName, $sFilename, $params, 'general', 'trigger');
    }

    public function registerAction($sActionType, $sActionClassName, $sActionNamespace, $sFilename = null)
    {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aActions[$sActionNamespace] = array($sActionType, $sActionClassName, $sActionNamespace, $sFilename, $this->sNamespace);

        // Register helper in DB
        $params = $sActionType.'|'.$sActionClassName.'|'.$sActionNamespace.'|'.$sFilename.'|'.$this->sNamespace;
        $this->registerPluginHelper($sActionNamespace, $sActionClassName, $sFilename, $params, 'general', 'action');
    }

    public function registerPage($webPath, $pageClassName, $filename = null)
    {
        $filename = $this->_fixFilename($filename);
        $webPath = sprintf("%s/%s", $this->sNamespace, $webPath);

        $this->_aPages[$webPath] = array($webPath, $pageClassName, $filename, $this->sNamespace);

        // Register helper in DB
        $params = $webPath.'|'.$pageClassName.'|'.$filename.'|'.$this->sNamespace;
        $this->registerPluginHelper($webPath, $pageClassName, $filename, $params, 'general', 'page');
    }

    public function registerWorkflowTrigger($sNamespace, $sTriggerClassName, $sFilename = null) {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aWFTriggers[$sNamespace] = array($sNamespace, $sTriggerClassName, $sFilename);

        // Register helper in DB
        $params = $sNamespace.'|'.$sTriggerClassName.'|'.$sFilename;
        $this->registerPluginHelper($sNamespace, $sTriggerClassName, $sFilename, $params, 'general', 'workflow_trigger');
    }

    public function getPagePath($sPath)
    {
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

    public function registerAuthenticationProvider($sName, $sClass, $sNamespace, $sFilename = null)
    {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aAuthenticationProviders[$sNamespace] = array($sName, $sClass, $sNamespace, $sFilename, $this->sNamespace);

        // Register helper in DB
        $params = $sName.'|'.$sClass.'|'.$sNamespace.'|'.$sFilename.'|'.$this->sNamespace;
        $this->registerPluginHelper($sNamespace, $sClass, $sFilename, $params, 'general', 'authentication_provider');
    }

    public function registerAdminCategory($path, $name, $description, $order = 0)
    {
        $this->_aAdminCategories[$path] = array($path, $name, $description);

        $params = "$path|$name|$description|$order";
        $this->registerPluginHelper($path, $name, $path, $params, 'general', 'admin_category');
    }

    public function registerAdminPage($name, $class, $category, $title, $description, $filename, $url = null, $order = 0)
    {
        $fullname = $category . '/' . $name;
        $filename = $this->_fixFilename($filename);
        $this->_aAdminPages[$fullname] = array(
                                                $name,
                                                $class,
                                                $category,
                                                $title,
                                                $description,
                                                $filename,
                                                $url,
                                                $this->sNamespace
        );

        $params = "$name|$class|$category|$title|$description|$filename|$url|{$this->sNamespace}|$order";
        $this->registerPluginHelper($fullname, $class, $filename, $params, 'general', 'admin_page');
    }

    /**
     * Register a new dashlet
     *
     * @param string $sClassName
     * @param string $sNamespace
     * @param string $sFilename
     */
    public function registerDashlet($sClassName, $sNamespace, $sFilename)
    {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aDashlets[$sNamespace] = array($sClassName, $sNamespace, $sFilename, $this->sNamespace);

        $params = $sClassName.'|'.$sNamespace.'|'.$sFilename.'|'.$this->sNamespace;
        $this->registerPluginHelper($sNamespace, $sClassName, $sFilename, $params, 'dashboard', 'dashlet');
    }

    public function registeri18n($sDomain, $sPath)
    {
        $sPath = $this->_fixFilename($sPath);
        $this->_ai18n[$sDomain] = array($sDomain, $sPath);

        // Register helper in DB
        $params = $sDomain.'|'.$sPath;
        $this->registerPluginHelper($sDomain, $sDomain, $sPath, $params, 'general', 'i18n');
    }

    public function registeri18nLang($sDomain, $sLang, $sPath)
    {
        if ($sPath !== "default") {
            $sPath = $this->_fixFilename($sPath);
        }
        $this->_ai18nLang["$sDomain/$sLang"] = array($sDomain, $sLang, $sPath);

        // Register helper in DB
        $params = $sDomain.'|'.$sLang.'|'.$sPath;
        $this->registerPluginHelper("$sDomain/$sLang", $sDomain, $sPath, $params, 'general', 'i18nlang');
    }

    public function registerLanguage($sLanguage, $sLanguageName)
    {
        $this->_aLanguage[$sLanguage] = array($sLanguage, $sLanguageName);

        // Register helper in DB
        $params = $sLanguage.'|'.$sLanguageName;
        $this->registerPluginHelper($sLanguage, $sClassName, $sFilename, $params, 'general', 'language');
    }

    public function registerHelpLanguage($sPlugin, $sLanguage, $sBasedir)
    {
        $sBasedir = (!empty($sBasedir)) ? $this->_fixFilename($sBasedir) : '';
        $this->_aHelpLanguage[$sLanguage] = array($sPlugin, $sLanguage, $sBasedir);

        // Register helper in DB
        $params = $sPlugin.'|'.$sLanguage.'|'.$sBasedir;
        $this->registerPluginHelper($sLanguage, $sClassName, '', $params, 'general', 'help_language');
    }

    public function registerColumn($sName, $sNamespace, $sClassName, $sFile)
    {
        $sFile = $this->_fixFilename($sFile);
        $this->_aColumns[$sNamespace] = array($sName, $sNamespace, $sClassName, $sFile);

        // Register helper in DB
        $params = $sName.'|'.$sNamespace.'|'.$sClassName.'|'.$sFile;
        $this->registerPluginHelper($sNamespace, $sClassName, $sFile, $params, 'general', 'column');
    }

    public function registerView($sName, $sNamespace)
    {
        $this->_aViews[$sNamespace] = array($sName, $sNamespace);

        // Register helper in DB
        $params = $sName.'|'.$sNamespace;
        $this->registerPluginHelper($sNamespace, '', '', $params, 'general', 'view');
    }

    public function registerNotificationHandler($sName, $sNamespace, $sPath)
    {
        $sPath = $this->_fixFilename($sPath);
        $this->_aNotificationHandlers[$sNamespace] = array($sNamespace, $sName, $sPath);

        // Register helper in DB
        $params = $sNamespace.'|'.$sName.'|'.$sPath;
        $this->registerPluginHelper($sNamespace, $sName, $sPath, $params, 'general', 'notification_handler');
    }

    public function registerTemplateLocation($sName, $sPath)
    {
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
    public function registerWidget($sClassname, $sNamespace, $sPath)
    {
        $sPath = $this->_fixFilename($sPath);
        $this->_aWidgets[$sNamespace] = array($sClassname, $sNamespace, $sPath);

        // Register helper in DB
        $params = $sClassname.'|'.$sNamespace.'|'.$sPath;
        $this->registerPluginHelper($sNamespace, $sClassname, $sPath, $params, 'general', 'widget');
    }

    public function registerValidator($sClassname, $sNamespace, $sPath)
    {
        $sPath = $this->_fixFilename($sPath);
        $this->_aValidators[$sNamespace] = array($sClassname, $sNamespace, $sPath);

        // Register helper in DB
        $params = $sClassname.'|'.$sNamespace.'|'.$sPath;
        $this->registerPluginHelper($sNamespace, $sClassname, $sPath, $params, 'general', 'validator');
    }


    public function registerCriterion($sClassName, $sNamespace, $sFilename = null, $aInitialize = null)
    {
        $sFilename = $this->_fixFilename($sFilename);
        $this->_aCriteria[$sNamespace] = array($sClassName, $sNamespace, $sFilename, $aInitialize);

        // Register helper in DB
        if (is_array($aInitialize)) {
            $aInitialize = serialize($aInitialize);
        }

        $params = $sClassName.'|'.$sNamespace.'|'.$sFilename.'|'.$aInitialize;
        $this->registerPluginHelper($sNamespace, $sClassName, $sFilename, $params, 'general', 'criterion');
    }

    public function registerInterceptor($sClassname, $sNamespace, $sPath = null)
    {
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
    public function registerProcessor($sClassname, $sNamespace, $sPath)
    {
        $sPath = $this->_fixFilename($sPath);
        $this->_aInterceptors[$sNamespace] = array($sClassname, $sNamespace, $sPath);

        // Register helper in DB
        $params = $sClassname.'|'.$sNamespace.'|'.$sPath;
        $this->registerPluginHelper($sNamespace, $sClassname, $sPath, $params, 'process', 'processor');
    }

    /**
     * Register search criteria for a plugin
     * See Search2/search/fieldRegistry.inc.php
     */
    public function registerSearchCriteria($sNamespace, $sPath)
    {
        $sPath = $this->_fixFilename($sPath);

        // Register helper in DB
        $params = $sNamespace.'|'.$sPath;
        $this->registerPluginHelper($sNamespace, '', $sPath, $params, 'general', 'search_criteria');
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
    function registerPluginHelper($namespace, $className, $path, $object, $view, $type) 
    {
        $options = array(
            'namespace' => $namespace,
            'plugin' => (!empty($this->sNamespace)) ? $this->sNamespace : $namespace,
            'classname' => $className,
            'pathname' => $path,
            'object' => $object,
            'viewtype' => $view,
            'classtype' => $type
        );
        
        $pluginCache = PluginCache::getPluginCache();
        return $pluginCache->addPluginHelper($options);
    }

    function deRegisterPluginHelper($namespace, $class) 
    {
        $pluginCache = PluginCache::getPluginCache();
        return $pluginCache->removePluginHelper($namespace, $class);
    }

    public function _fixFilename($sFilename)
    {
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

    public function isRegistered()
    {
        if ($this->bAlwaysInclude) {
            return true;
        }

        require_once(KT_LIB_DIR . '/plugins/pluginentity.inc.php');
        $oEntity = KTPluginEntity::getByNamespace($this->sNamespace);
        if (PEAR::isError($oEntity)) {
            if ($oEntity instanceof KTEntityNoObjects) {
                // plugin not registered in database

                // XXX: nbm: Show an error on the page that a plugin
                // isn't registered or something, perhaps.
                return false;
            }

            return false;
        }
        if (!($oEntity instanceof KTPluginEntity)) {
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
    public function load()
    {
        return $this->run_setup();
    }

    public function loadHelpers()
    {
        // Get actions, portlets, etc, create arrays as part of plugin
        $query = "SELECT * FROM plugin_helper h WHERE plugin = '{$this->sNamespace}'";
        $aPluginHelpers = DBUtil::getResultArray($query);

        if (!empty($aPluginHelpers)) {
            foreach ($aPluginHelpers as $plugin) {
                $sName = $plugin['namespace'];
                $sParams = $plugin['object'];
                $aParams = explode('|', $sParams);
                $sClassType = $plugin['classtype'];

                switch ($sClassType) {
                    case 'portlet':
                        $aLocation = unserialize($aParams[0]);
                        if ($aLocation != false) {
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
                        if ($aInit != false) {
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

    public function setup()
    {
        return;
    }

    public function run_setup()
    {
        return true;
    }

    public function setAvailability($sNamespace, $bAvailable = true)
    {
        $aValues = array('unavailable' => $bAvailable);
        $aWhere = array('namespace' => $sNamespace);
        $res = DBUtil::whereUpdate('plugins', $aValues, $aWhere);
        return $res;
    }

    public function stripKtDir($sFilename)
    {
        if (strpos($sFilename, KT_DIR) === 0 ||strpos($sFilename, realpath(KT_DIR)) === 0) {
            return substr($sFilename, strlen(KT_DIR) + 1);
        }
        return $sFilename;
    }

    public function upgradePlugin($iStart, $iEnd)
    {
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

    public function register() {
        $oEntity = KTPluginEntity::getByNamespace($this->sNamespace);
        $friendly_name = '';
        $iOrder = $this->iOrder;
        global $default;

        if (!empty($this->sFriendlyName)) { $friendly_name = $this->sFriendlyName; }
        if (!PEAR::isError($oEntity)) {
            
            $default->log->debug('PLUGINS: Register plugin ' . $this->sFriendlyName);

            // check for upgrade.
            $iEndVersion = 0; // dest.
            if ($this->iVersion != $oEntity->getVersion()) {
                $default->log->debug("PLUGINS: Upgrading from version {$oEntity->getVersion()} to version {$this->iVersion}");
                
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
            if (!$oEntity->getDisabled() && !$oEntity->getUnavailable()) {
                $this->setup();
            }
            return $oEntity;
        }
        if (PEAR::isError($oEntity) && !($oEntity instanceof KTEntityNoObjects)) {
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
        if (!$disabled) {
            $this->setup();
        }
        return true;
    }

    public function getURLPath($filename = null)
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
