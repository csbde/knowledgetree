<?php

require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

class KTDocumentAction extends KTStandardDispatcher {
    var $sName;
    var $sDescription;
    var $sDisplayName;

    var $_sShowPermission = "ktcore.permissions.read";
    var $_sDisablePermission;

    var $_bDisabled;
    var $_sDisabledText = null;

    var $sSection = "view_details";
    var $aBreadcrumbs = array(
        array('action' => 'browse', 'name' => 'Browse'),
    );

    function KTDocumentAction($oDocument = null, $oUser = null, $oPlugin = null) {
        $this->oDocument =& $oDocument;
        $this->oUser =& $oUser;
        $this->oPlugin =& $oPlugin;
        parent::KTStandardDispatcher();
    }

    function setDocument(&$oDocument) {
        $this->oDocument =& $oDocument;
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
        return KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oDocument);
    }

    function _disable() {
        if ($this->_bDisabled === true) {
            return true;
        }
        if (!KTWorkflowUtil::actionEnabledForDocument($this->oDocument, $this->sName)) {
            $this->_sDisabledText = "Workflow does not allow this action at this time";
            return true;
        }
        if (is_null($this->_sDisablePermission)) {
            return false;
        }
        $oPermission =& KTPermission::getByName($this->_sDisablePermission);
        if (PEAR::isError($oPermission)) {
            return false;
        }
        $bResult = KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oDocument);
        if ($bResult === false) {
            $this->_sDisabledText = "Insufficient privileges";
        }
        return !$bResult;
    }

    function getURL() {
        return sprintf("/action.php/%s?fDocumentId=%d", $this->sName, $this->oDocument->getID());
    }

    function getInfo() {
        if ($this->_show() === false) {
            return null;
        }

        $url = $this->getURL();
        if (substr($url, 0, 1) == '/') {
            $url = generateLink($url, "");
        }

        $aInfo = array(
            'disabled' => $this->_disable(),
            'description' => $this->sDescription,
            'name' => $this->sDisplayName,
            'url' => $url,
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
        $this->oDocument =& $this->oValidator->validateDocument($_REQUEST['fDocumentId']);

        if (!is_null($this->_sShowPermission)) {
            $oPermission =& KTPermission::getByName($this->_sShowPermission);
            if (!PEAR::isError($oPermission)) {
                $res = KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oDocument);
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
            KTBrowseUtil::breadcrumbsForDocument($this->oDocument, $aOptions));
        return true;
    }

    function do_main() {
        return "Dispatcher component of action not implemented.";
    }
}

class KTDocumentActionUtil {
    function getDocumentActionInfo() {
        $oRegistry =& KTActionRegistry::getSingleton();
        return $oRegistry->getActions('documentaction');
    }
    function &getDocumentActionsForDocument($oDocument, $oUser) {
        $aObjects = array();
        foreach (KTDocumentActionUtil::getDocumentActionInfo() as $aAction) {
            list($sClassName, $sPath, $sPlugin) = $aAction;
            $oRegistry =& KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin($sPlugin);
            if (!empty($sPath)) {
                // require_once(KT_DIR .
                // Or something...
            }
            $aObjects[] =& new $sClassName($oDocument, $oUser, $oPlugin);
        }
        return $aObjects;
    }

    function getAllDocumentActions() {
        $aObjects = array();
        $oDocument = null;
        $oUser = null;
        foreach (KTDocumentActionUtil::getDocumentActionInfo() as $aAction) {
            list($sClassName, $sPath, $sName, $sPlugin) = $aAction;
            $oRegistry =& KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin($sPlugin);
            if (!empty($sPath)) {
                // require_once(KT_DIR .
                // Or something...
            }
            $aObjects[] =& new $sClassName($oDocument, $oUser, $oPlugin);
        }
        return $aObjects;
    }

    function getDocumentActionsByNames($aNames) {
        $aObjects = array();
        $oDocument = null;
        $oUser = null;
        foreach (KTDocumentActionUtil::getDocumentActionInfo() as $aAction) {
            list($sClassName, $sPath, $sName, $sPlugin) = $aAction;
            $oRegistry =& KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin($sPlugin);
            if (!in_array($sName, $aNames)) {
                continue;
            }
            if (!empty($sPath)) {
                // require_once(KT_DIR .
                // Or something...
            }
            $aObjects[] =& new $sClassName($oDocument, $oUser, $oPlugin);
        }
        return $aObjects;
    }
}

class KTBuiltInDocumentAction extends KTDocumentAction {
    var $sBuildInAction;
    function getURL() {
        return sprintf("/control.php?action=%s&fDocumentID=%d", $this->sBuiltInAction, $this->oDocument->getID());
    }
}

class KTBuiltInDocumentActionSingle extends KTBuiltInDocumentAction {
    function getURL() {
        return sprintf("/control.php?action=%s&fDocumentIDs[]=%d&fReturnDocumentID=%d", $this->sBuiltInAction, $this->oDocument->getID(), $this->oDocument->getID());
    }
}

/* require_once(KT_DIR . '/plugins/ktcore/documentaction.inc.php'); */

?>
