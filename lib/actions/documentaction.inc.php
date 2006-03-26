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

require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

class KTDocumentAction extends KTStandardDispatcher {
    var $sName;
    var $sDescription;

    var $_sShowPermission = "ktcore.permissions.read";
    var $_sDisablePermission;
    var $sHelpPage = 'ktcore/browse.html';	

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
            'url' => $url,
        );
        return $this->customiseInfo($aInfo);
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
		
        $aOptions = array(
            "final" => false,
            "documentaction" => "viewDocument",
            "folderaction" => "browse",
        );
        $this->aBreadcrumbs = array_merge($this->aBreadcrumbs,
            KTBrowseUtil::breadcrumbsForDocument($this->oDocument, $aOptions));

	    $actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser);
		$oPortlet = new KTActionPortlet(_kt("Document Actions"));
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

    function &getDocumentActionsForDocument($oDocument, $oUser, $slot = "documentaction") {
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

    function getDocumentActionsByNames($aNames, $slot = "documentaction") {
        $aObjects = array();
        $oDocument = null;
        $oUser = null;
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
