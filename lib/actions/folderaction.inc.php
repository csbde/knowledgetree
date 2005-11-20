<?php

require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');

class KTFolderAction extends KTStandardDispatcher {
    var $sName;
    var $sDescription;
    var $sDisplayName;

    var $_sShowPermission;
    var $_sDisablePermission;

    var $_bDisabled;
    var $_sDisabledText = null;

    var $sSection = "view_details";
    var $aBreadcrumbs = array(
        array('action' => 'browse', 'name' => 'Browse'),
    );

    function KTFolderAction($oFolder = null, $oUser = null) {
        $this->oFolder = $oFolder;
        $this->oUser = $oUser;
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
        return sprintf("/action.php/%s?fFolderId=%d", $this->sName, $this->oFolder->getID());
    }

    function getInfo() {
        if ($this->_show() === false) {
            return null;
        }

        $aInfo = array(
            'disabled' => $this->_disable(),
            'description' => $this->sDescription,
            'name' => $this->sDisplayName,
            'url' => generateLink($this->getURL(), ""),
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

        if (!is_null($this->_sShowPermission)) {
            $oPermission =& KTPermission::getByName($this->_sShowPermission);
            if (!PEAR::isError($oPermission)) {
                $res = KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oFolder);
                if (!$res) {
                    return false;
                }
            }
        }
        $aOptions = array(
            "final" => false,
            "documentaction" => "viewDocument",
            "folderaction" => "browse",
        );
        $this->aBreadcrumbs = array_merge($this->aBreadcrumbs,
            KTBrowseUtil::breadcrumbsForFolder($this->oFolder, $aOptions));
        return true;
    }

    function do_main() {
        return "Dispatcher component of action not implemented.";
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
            list($sClassName, $sPath) = $aAction;
            if (!empty($sPath)) {
                // require_once(KT_DIR .
                // Or something...
            }
            $aObjects[] =& new $sClassName($oFolder, $oUser);
        }
        return $aObjects;
    }
}

class KTBuiltInFolderAction extends KTFolderAction {
    var $sBuildInAction;
    function getURL() {
        return sprintf("/control.php?action=%s&fFolderID=%d", $this->sBuiltInAction, $this->oFolder->getID());
    }
}

class KTBuiltInFolderActionSingle extends KTBuiltInFolderAction {
    function getURL() {
        return sprintf("/control.php?action=%s&fFolderIDs[]=%d&fReturnFolderID=%d", $this->sBuiltInAction, $this->oFolder->getID(), $this->oFolder->getID());
    }
}

?>
