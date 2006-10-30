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

require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

class KTDocumentAction extends KTStandardDispatcher {
    var $sName;
    var $sDescription;

    var $_sShowPermission = "ktcore.permissions.read";
    var $_sDisablePermission;
    var $bAllowInAdminMode = false;
    var $sHelpPage = 'ktcore/browse.html';    

    var $sSection = "view_details";

    var $_bMutator = false;
    var $_bMutationAllowedByAdmin = true;
    
    var $sIconClass;

    function KTDocumentAction($oDocument = null, $oUser = null, $oPlugin = null) {
        $this->oDocument =& $oDocument;
        $this->oUser =& $oUser;
        $this->oPlugin =& $oPlugin;
        $this->aBreadcrumbs = array(
            array('action' => 'browse', 'name' => _kt('Browse')),
        );
        
        $this->persistParams('fDocumentId');

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
        $oFolder = Folder::get($this->oDocument->getFolderId());

        if ($this->_bMutator && $this->oDocument->getImmutable()) {
            if ($this->_bMutationAllowedByAdmin === true) {
                if (!KTBrowseUtil::inAdminMode($this->oUser, $oFolder)) {
                    return false;
                }
            } else {
                return false;
            }
        }
        
        if ($this->_bAdminAlwaysAvailable) {
            if (Permission::userIsSystemAdministrator($this->oUser->getId())) {
                return true;
            }
            if (Permission::isUnitAdministratorForFolder($this->oUser, $this->oDocument->getFolderId())) {
                return true;
            }
        }
        $oPermission =& KTPermission::getByName($this->_sShowPermission);
        if (PEAR::isError($oPermission)) {
            return true;
        }
        if (!KTWorkflowUtil::actionEnabledForDocument($this->oDocument, $this->sName)) {
            return false;
        }
        // be nasty in archive/delete status.
        $status = $this->oDocument->getStatusID();
        if (($status == DELETED) || ($status == ARCHIVED)) { return false; } 
        if ($this->bAllowInAdminMode) {
            // check if this user is in admin mode
            if (KTBrowseUtil::inAdminMode($this->oUser, $oFolder)) {             
                return true; 
            }        
        }        
        return KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oDocument);
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
            'description' => $this->sDescription,
            'name' => $this->getDisplayName(),
            'ns' => $this->sName,
            'url' => $url,
            'icon_class' => $this->sIconClass,
        );
        
        $aInfo = $this->customiseInfo($aInfo);
        return $aInfo;
    }

    function getName() {
        return $this->sName;
    }

    function getDisplayName() {
        // Should be overridden by the i18nised display name
        // This is here solely for backwards compatibility
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
        
        $aOptions = array("final" => false,
              "documentaction" => "viewDocument",
              "folderaction" => "browse",
        );
        $this->aBreadcrumbs = kt_array_merge($this->aBreadcrumbs,
            KTBrowseUtil::breadcrumbsForDocument($this->oDocument, $aOptions));

    	$actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser, 'documentinfo');
        $oPortlet = new KTActionPortlet(sprintf(_kt('Info about this document')));
	    $oPortlet->setActions($actions, $this->sName);
	    $this->oPage->addPortlet($oPortlet);              

    	$actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser);
        $oPortlet = new KTActionPortlet(sprintf(_kt('Actions on this document')));
	    $oPortlet->setActions($actions, $this->sName);
	    $this->oPage->addPortlet($oPortlet);              
	
	    $this->oPage->setSecondaryTitle($this->oDocument->getName());            
            
        return true;
    }

    function do_main() {
        return _kt("Dispatcher component of action not implemented.");
    }
}

class KTDocumentActionUtil {
    function getDocumentActionInfo($slot = "documentaction") {
        $oRegistry =& KTActionRegistry::getSingleton();
        return $oRegistry->getActions($slot);
    }

    function &getDocumentActionsForDocument(&$oDocument, $oUser, $slot = "documentaction") {
        $aObjects = array();
        foreach (KTDocumentActionUtil::getDocumentActionInfo($slot) as $aAction) {
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

    function getAllDocumentActions($slot = "documentaction") {
        $aObjects = array();
        $oDocument = null;
        $oUser = null;
        foreach (KTDocumentActionUtil::getDocumentActionInfo($slot) as $aAction) {
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

    function getDocumentActionsByNames($aNames, $slot = "documentaction", $oDocument = null, $oUser = null) {
        $aObjects = array();
        foreach (KTDocumentActionUtil::getDocumentActionInfo($slot) as $aAction) {
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
