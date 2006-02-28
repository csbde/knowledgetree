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

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

class KTFolderAction extends KTStandardDispatcher {
    var $sName;
    var $sDescription;

    var $_sShowPermission;
    var $_sDisablePermission;
    var $sHelpPage = 'ktcore/browse.html';    
    
    var $_bAdminAlwaysAvailable = false;

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
            'description' => $this->sDescription,
            'name' => $this->getDisplayName(),
            'url' => $this->getURL(),
        );
        return $this->customiseInfo($aInfo);
    }

    function getName() {
        return $this->sName;
    }

    function getDisplayName() {
        // This should be overridden by the i18nised display name
        // This implementation is only here for backwards compatibility
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
