<?php

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

class KTFolderAction extends KTStandardDispatcher {
    var $sName;
    var $sDescription;
    var $sDisplayName;

    var $_sShowPermission;
    var $_sDisablePermission;
    
    var $_bAdminAlwaysAvailable = false;

    var $_bDisabled;
    var $_sDisabledText = null;

    var $sSection = "browse";
    var $aBreadcrumbs = array(
        array('action' => 'browse', 'name' => 'Browse'),
    );

    function KTFolderAction($oFolder = null, $oUser = null, $oPlugin = null) {
        $this->oFolder =& $oFolder;
        $this->oUser =& $oUser;
        $this->oPlugin =& $oPlugin;
        parent::KTStandardDispatcher();
    }

    function setFolder(&$oFolder) {
        $this->oFolder =& $oFolder;
    }

    function setUser(&$oUser) {
        $this->oUser =& $oUser;
    }


    function _show() {
        if (is_null($this->_sShowPermission)) {
            return true;
        }
        $oPermission =& KTPermission::getByName($this->_sShowPermission);
        if (PEAR::isError($oPermission)) {
            return true;
        }

        if ($this->_bAdminAlwaysAvailable) {
            if (Permission::userIsSystemAdministrator($this->oUser->getId())) {
                return true;
            }
            if (Permission::isUnitAdministratorForFolder($this->oUser, $this->oFolder)) {
                return true;
            }
        }
        
        return KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oFolder);
    }

    function _disable() {
        if ($this->_bDisabled === true) {
            return true;
        }
        if (is_null($this->_sDisablePermission)) {
            return false;
        }
        $oPermission =& KTPermission::getByName($this->_sDisablePermission);
        if (PEAR::isError($oPermission)) {
            return false;
        }
        $bResult = KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oFolder);
        if ($bResult === false) {
            $this->_sDisabledText = "Insufficient privileges";
        }
        return !$bResult;
    }

    function getURL() {
        $oKTConfig =& KTConfig::getSingleton();
        $sExt = ".php";
        if (KTUtil::arrayGet($_SERVER, 'kt_no_extensions')) {
            $sExt = "";
        }
        if ($oKTConfig->get("KnowledgeTree/pathInfoSupport")) {
            return sprintf("%s/action%s/%s?fFolderId=%d", $GLOBALS['KTRootUrl'], $sExt, $this->sName, $this->oFolder->getID());
        } else {
            return sprintf("%s/action%s?kt_path_info=%s&fFolderId=%d", $GLOBALS['KTRootUrl'], $sExt, $this->sName, $this->oFolder->getID());
        }
    }

    function getInfo() {
        if ($this->_show() === false) {
            return null;
        }

        $aInfo = array(
            'disabled' => $this->_disable(),
            'description' => $this->sDescription,
            'name' => $this->sDisplayName,
            'url' => $this->getURL(),
            'disabled_text' => $this->_sDisabledText,
        );
        return $this->customiseInfo($aInfo);
    }

    function getName() {
        return $this->sName;
    }

    function getDisplayName() {
        return $this->sDisplayName;
    }

    function getDescription() {
        return $this->sDescription;
    }

    function customiseInfo($aInfo) {
        return $aInfo;
    }

    function check() {
        $this->oFolder =& $this->oValidator->validateFolder($_REQUEST['fFolderId']);
        
        if (!$this->_show()) { return false; }
        
        $aOptions = array(
            "final" => false,
            "documentaction" => "viewDocument",
            "folderaction" => "browse",
        );
        $this->aBreadcrumbs = array_merge($this->aBreadcrumbs,
            KTBrowseUtil::breadcrumbsForFolder($this->oFolder, $aOptions));

        $portlet = new KTActionPortlet(_("Folder Actions"));
        $aActions = KTFolderActionUtil::getFolderActionsForFolder($this->oFolder, $this->oUser);        
        $portlet->setActions($aActions,null);
        $this->oPage->addPortlet($portlet);            
            
        $this->oPage->setSecondaryTitle($this->oFolder->getName());          
        
        return true;
    }

    function do_main() {
        return _("Dispatcher component of action not implemented.");
    }

}

class KTFolderActionUtil {
    function getFolderActions() {
        $oRegistry =& KTActionRegistry::getSingleton();
        return $oRegistry->getActions('folderaction');
    }
    function &getFolderActionsForFolder($oFolder, $oUser) {
        $aObjects = array();
        foreach (KTFolderActionUtil::getFolderActions() as $aAction) {
            list($sClassName, $sPath, $sPlugin) = $aAction;
            $oRegistry =& KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin($sPlugin);
            if (!empty($sPath)) {
                require_once($sPath);
            }
            $aObjects[] =& new $sClassName($oFolder, $oUser, $oPlugin);
        }
        return $aObjects;
    }
}

?>
