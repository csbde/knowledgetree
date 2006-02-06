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
        $oKTConfig =& KTConfig::getSingleton();
        $sExt = ".php";
        if (KTUtil::arrayGet($_SERVER, 'kt_no_extensions')) {
            $sExt = "";
        }
        if ($oKTConfig->get("KnowledgeTree/pathInfoSupport")) {
            return sprintf("%s/action%s/%s?fDocumentId=%d", $GLOBALS['KTRootUrl'], $sExt, $this->sName, $this->oDocument->getID());
        } else {
            return sprintf("%s/action%s?kt_path_info=%s&fDocumentId=%d", $GLOBALS['KTRootUrl'], $sExt, $this->sName, $this->oDocument->getID());
        }
    }

    function getInfo() {
        if ($this->_show() === false) {
            return null;
        }

        $url = $this->getURL();

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

		if (!$this->_show()) { return false; }
		
        $aOptions = array(
            "final" => false,
            "documentaction" => "viewDocument",
            "folderaction" => "browse",
        );
        $this->aBreadcrumbs = array_merge($this->aBreadcrumbs,
            KTBrowseUtil::breadcrumbsForDocument($this->oDocument, $aOptions));


	    $actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser);
		$oPortlet = new KTActionPortlet(_("Document Actions"));
		$oPortlet->setActions($actions, $this->sName);
		$this->oPage->addPortlet($oPortlet);              
            
        return true;
    }

    function do_main() {
        return _("Dispatcher component of action not implemented.");
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
                require_once($sPath);
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
                require_once($sPath);
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
                require_once($sPath);
            }
            $aObjects[] =& new $sClassName($oDocument, $oUser, $oPlugin);
        }
        return $aObjects;
    }
}

?>
