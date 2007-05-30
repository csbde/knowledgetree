<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

class KTFolderAction extends KTStandardDispatcher {
    var $sName;
    var $sDescription;

    var $_sShowPermission = 'ktcore.permissions.folder_details';
    var $_sDisablePermission;
    var $sHelpPage = 'ktcore/browse.html';    
    
    var $_bAdminAlwaysAvailable = false;

    var $sSection = 'browse';

    function KTFolderAction($oFolder = null, $oUser = null, $oPlugin = null) {
        parent::KTStandardDispatcher();    
        $this->oFolder =& $oFolder;
        $this->oUser =& $oUser;
        $this->oPlugin =& $oPlugin;
        $this->aBreadcrumbs = array(
            array('action' => 'browse', 'name' => _kt('Browse')),
        );
        $this->persistParams(array('fFolderId'));
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
            if (KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
                return true;
            }
        }
        
        return KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oFolder);
    }

    function getURL() {
        $oKTConfig =& KTConfig::getSingleton();
        $sExt = '.php';
        if (KTUtil::arrayGet($_SERVER, 'kt_no_extensions')) {
            $sExt = '';
        }
        if ($oKTConfig->get('KnowledgeTree/pathInfoSupport')) {
            return sprintf('%s/action%s/%s?fFolderId=%d', $GLOBALS['KTRootUrl'], $sExt, $this->sName, $this->oFolder->getID());
        } else {
            return sprintf('%s/action%s?kt_path_info=%s&fFolderId=%d', $GLOBALS['KTRootUrl'], $sExt, $this->sName, $this->oFolder->getID());
        }
    }

    function getInfo() {
        if ($this->_show() === false) {
            return null;
        }

        $aInfo = array(
            'description' => $this->sDescription,
            'name' => $this->getDisplayName(),
            'ns' => $this->sName,
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
            'final' => false,
            'documentaction' => 'viewDocument',
            'folderaction' => 'browse',
        );
        $this->aBreadcrumbs = kt_array_merge($this->aBreadcrumbs,
            KTBrowseUtil::breadcrumbsForFolder($this->oFolder, $aOptions));

        $portlet = new KTActionPortlet(sprintf(_kt('About this folder')));
        $aActions = KTFolderActionUtil::getFolderInfoActionsForFolder($this->oFolder, $this->oUser);        
        $portlet->setActions($aActions,$this->sName);
        $this->oPage->addPortlet($portlet);            

        $portlet = new KTActionPortlet(sprintf(_kt('Actions on this folder')));
        $aActions = KTFolderActionUtil::getFolderActionsForFolder($this->oFolder, $this->oUser);        
        $portlet->setActions($aActions,$this->sName);
        $this->oPage->addPortlet($portlet);            
            
        if (KTPermissionUtil::userHasPermissionOnItem($this->oUser, 'ktcore.permissions.folder_details', $this->oFolder)) {
            $this->oPage->setSecondaryTitle($this->oFolder->getName());
        } else {
            if (KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
                $this->oPage->setSecondaryTitle(sprintf('(%s)', $this->oFolder->getName()));
            } else {
                $this->oPage->setSecondaryTitle('...');
            }
        }
        
        return true;
    }

    function do_main() {
        return _kt('Dispatcher component of action not implemented.');
    }

}

class KTFolderActionUtil {
    function getFolderActions() {
        $oRegistry =& KTActionRegistry::getSingleton();
        return $oRegistry->getActions('folderaction');
    }
    function getFolderInfoActions() {
        $oRegistry =& KTActionRegistry::getSingleton();
        return $oRegistry->getActions('folderinfo');
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
    function &getFolderInfoActionsForFolder($oFolder, $oUser) {
        $aObjects = array();
        foreach (KTFolderActionUtil::getFolderInfoActions() as $aAction) {
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
