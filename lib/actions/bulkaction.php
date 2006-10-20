<?php

/**
 * $Id: documentaction.inc.php 5848 2006-08-16 15:58:51Z bshuttle $
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
require_once(KT_LIB_DIR . '/actions/entitylist.php');

require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

require_once(KT_LIB_DIR . '/widgets/forms.inc.php');

class KTBulkAction extends KTStandardDispatcher {
    var $sName;
    var $sDescription;

    var $_sDisablePermission;
    var $bAllowInAdminMode = false;
    var $sHelpPage = 'ktcore/browse.html';    

    var $sSection = "view_details";

    var $_bMutator = false;
    var $_bMutationAllowedByAdmin = true;

    var $sIconClass;

    // not 'sShowPermission' - mass actions are always shown
    // this is used to check against individual entities
    var $_sPermission = 'ktcore.permissions.read';

    function KTBulkAction($oUser = null, $oPlugin = null) {
        $this->oEntityList = null;
        $this->oActiveEntityList = null;
        $this->oUser =& $oUser;
        $this->oPlugin =& $oPlugin;

        $this->aBreadcrumbs = array(
            array('action' => 'browse', 'name' => _kt('Browse')),
        );
        
        $this->persistParams('fEntityListCode');
        parent::KTStandardDispatcher();
    }

    function setEntityList(&$oEntityList) {
        $this->oEntityList =& $oEntityList;
    }

    function setUser(&$oUser) {
        $this->oUser =& $oUser;
    }

    function _show() {
        return true;
    }

    function getURL() {
        $oKTConfig =& KTConfig::getSingleton();
        $sExt = ".php";
        if (KTUtil::arrayGet($_SERVER, 'kt_no_extensions')) {
            $sExt = "";
        }
        if ($oKTConfig->get("KnowledgeTree/pathInfoSupport")) {
            return sprintf("%s/action%s/%s", $GLOBALS['KTRootUrl'], $sExt, $this->sName);
        } else {
            return sprintf("%s/action%s?kt_path_info=%s", $GLOBALS['KTRootUrl'], $sExt, $this->sName);
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
        return $this->sDisplayName;
    }

    function getDescription() {
        return $this->sDescription;
    }

    function customiseInfo($aInfo) {
        return $aInfo;
    }

    // helper function
    function _getNames($aIds, $sEntity) {
        if(count($aIds)) {
            $aNames = array();
            $aFunc = array($sEntity, 'get');

            foreach($aIds as $id) {
                $oE =& call_user_func($aFunc, $id);
                $aNames[] = $oE->getName();
            }
            return $aNames;
        } else {
            return array();
        }
    }


    // doesn't actually do checks, as they have to be performed per-entity
    function check() {
        // not necessarily coming from a folder...
        $iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', 1);
        $this->oFolder = Folder::get($iFolderId);
        //$this->oFolder =& $this->oValidator->validateFolder($_REQUEST['fFolderId']);
        
        $aOptions = array(
            "final" => false,
            "documentaction" => "viewDocument",
            "folderaction" => "browse",
        );

        $this->aBreadcrumbs = array(array('name'=>_kt('Bulk Actions')),
                                    array('name'=>$this->getDisplayName()));

        return true;
    }

    
    // check the entire entity list. this needn't be overrided at any point
    function check_entities() {
        $aFailed = array('documents' => array(), 'folders' => array());
        $aSucceeded = array('documents' => array(), 'folders' => array());

        if(!$this->oEntityList) {
            return true;
        }

        foreach($this->oEntityList->getDocumentIds() as $iId) {
            $oDocument =& Document::get($iId);
            
            if(PEAR::isError($oDocument)) {
                $aFailed['documents'][] = array($iId, _kt('No such document'));
            } else {
                $res = $this->check_entity($oDocument);

                // all these checks allow a return from check_entity of:
                // 1. a PEAR error, indicating failure, with the message in the error
                // 2. false, for unknown error
                // 3. true, to pass

                if(PEAR::isError($res)) {
                    $aFailed['documents'][] = array($oDocument->getName(), $res->getMessage());
                } else if($res === false) {
                    $aFailed['documents'][] = array($oDocument->getName(), _kt('Failed (unknown reason)'));
                } else {
                    $aSucceeded['documents'][] = $oDocument->getId();
                }
            }
        }

        foreach($this->oEntityList->getFolderIds() as $iId) {
            $oFolder =& Folder::get($iId);
            
            if(PEAR::isError($oFolder)) {
                $aFailed['folders'][] = array($iId, _kt('No such folder'));
            } else {                
                $res = $this->check_entity($oFolder);

                if(PEAR::isError($res)) {
                    $aFailed['folders'][] = array($oFolder->getName(), $res->getMessage());
                } else if($res === false) {
                    $aFailed['folders'][] = array($oFolder->getName(), _kt('Failed (unknown reason)'));
                } else {
                    $aSucceeded['folders'][] = $oFolder->getId();
                }
            }
        }        
        $this->oActiveEntityList = new KTEntityList($aSucceeded['documents'], $aSucceeded['folders']);
        $this->aFailed = $aFailed;

        return count($aSucceeded['documents']) + count($aSucceeded['folders']);
    }


    // iterate over all entites to act on them
    function perform_action_on_list() {
        $this->aActionResults = array('folders'=>array(), 'documents'=>array());

        foreach($this->oActiveEntityList->getDocumentIds() as $iId) {
            $oDocument =& Document::get($iId);
            if(!PEAR::isError($oDocument)) {
                $sName = $oDocument->getName();
            } else {
                $sName = _kt('Error fetching document name');
            }

            $res = $this->perform_action($oDocument);
            
            if(PEAR::isError($res)) {
                $this->aActionResults['documents'][] = array($sName, $res->getMessage());
            } else {
                $this->aActionResults['documents'][] = array($sName, _kt('Success'));
            }
        }

        foreach($this->oActiveEntityList->getFolderIds() as $iId) {
            $oFolder =& Folder::get($iId);
            if(!PEAR::isError($oFolder)) {
                $sName = $oFolder->getName();
            } else {
                $sName = _kt('Error fetching folder name');
            }

            $res = $this->perform_action($oFolder);
            
            if(PEAR::isError($res)) {
                $this->aActionResults['folders'][] = array($sName, $res->getMessage());
            } else {
                $this->aActionResults['folders'][] = array($sName, _kt('Success'));
            }            
        }
    }



    // list persistance
    // fetch existing lists
    function get_lists() {
        $this->oEntityList = KTEntityList::retrieveList(KTUtil::arrayGet($_REQUEST, 'fListCode', null));
        $this->oActiveEntityList = KTEntityList::retrieveList(KTUtil::arrayGet($_REQUEST, 'fActiveListCode', null));
        if(PEAR::isError($this->oActiveEntityList)) {
            $this->oActiveEntityList = null;
        }
    }

    // persist
    function store_lists() {
        $this->persistParams(array('fListCode', 'fActiveListCode', 'fFolderId', 'fReturnData', 'fReturnAction'));
    }
        



    // forms
    // form to list the entites after checking each one
    function form_listing() {
        $sListCode = $this->oEntityList->getCode();
        $sActiveListCode = $this->oActiveEntityList->getCode();

        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.listing.form',
            'submit_label' => _kt("Continue"),
            'targeturl' => $this->getURL(),
            'action' => 'collectinfo',
            'fail_action' => 'main',
            'noframe' => true,
            'extraargs' => array('fListCode' => $sListCode, 
                                 'fActiveListCode' => $sActiveListCode,
                                 'fFolderId' => $this->oFolder->getId(),
                                 'fReturnAction' => KTUtil::arrayGet($_REQUEST, 'fReturnAction'),
                                 'fReturnData' => KTUtil::arrayGet($_REQUEST, 'fReturnData'),
                                 ),
            'context' => $this,
        ));
        return $oForm;
    }

    // form to show on action completion, and list results
    function form_complete() {
        $sReturnAction = KTUtil::arrayGet($_REQUEST, 'fReturnAction');
        $sReturnData = KTUtil::arrayGet($_REQUEST, 'fReturnData');
        $sAction = 'main';

        if($sReturnAction == 'browse') {
            $sTargetUrl = KTBrowseUtil::getUrlForFolder(Folder::get($sReturnData));
        } else if($sReturnAction == 'simpleSearch') {
            $sTargetUrl = KTBrowseUtil::getSimpleSearchBaseUrl();
            $extraargs = array('fSearchableText'=>$sReturnData);
        } else if($sReturnAction == 'booleanSearch') {
            $sTargetUrl = KTBrowseUtil::getBooleanSearchBaseUrl();
            $sAction = 'performSearch';
            $extraargs = array('boolean_search_id'=>$sReturnData);
        }

        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.complete.form',
            'submit_label' => _kt("Return"),
            'targeturl' => $sTargetUrl,
            'context' => $this,
            'action' => $sAction,
            'extraargs' => $extraargs,
            'noframe' => true,
        ));
        return $oForm;
    }


    // main entry point - checks the entity list and displays lists
    function do_main() {
        // get entities (using the checkboxes atm)
        $aFolders = KTUtil::arrayGet($_REQUEST, 'selection_f' , array());
        $aDocuments = KTUtil::arrayGet($_REQUEST, 'selection_d' , array());
        $this->oEntityList = new KTEntityList($aDocuments, $aFolders);

        // gives us $this->aFailed
        $iActiveCount = $this->check_entities();        

	$oTemplating =& KTTemplating::getSingleton();
	$oTemplate = $oTemplating->loadTemplate('ktcore/bulk_action_listing');
        
        $this->store_lists();            
            
        return $oTemplate->render(array('context' => $this,
                                        'form' => $this->form_listing(),
                                        'failed' => $this->aFailed,
                                        'active' => $this->oActiveEntityList,
                                        'activecount' => $iActiveCount,
                                        'failedform' => $this->form_complete(),
                                        'folders' => $this->_getNames($this->oActiveEntityList->getFolderIds(), 'Folder'),
                                        'documents' => $this->_getNames($this->oActiveEntityList->getDocumentIds(), 'Document')));

    }

    // override to add a screen to get a reason, or whatever
    // remember to pass to perform action next, and call the super's method
    function do_collectinfo() {
        $this->store_lists();
        return $this->do_performaction();
    }

    // perform the action itself, and list results (completion action)
    function do_performaction() {
        $this->get_lists();
        $this->aPersistParams = array();
        $this->perform_action_on_list();

	$oTemplating =& KTTemplating::getSingleton();
	$oTemplate = $oTemplating->loadTemplate('ktcore/bulk_action_complete');
        return $oTemplate->render(array('context' => $this,
                                        'list' => $this->aActionResults,
                                        'form' => $this->form_complete()));
    }






    // main overrides

    // override to do the actual action, on an individual entity
    function perform_action($oEntity) {
        return PEAR::raiseError(_kt("Action component not implemented"));
    }

    // check an individual entity - this should be overrided with additional
    // checks required for the specific action, but should always call its
    // parent implementation
    function check_entity($oEntity) {
        $oPermission =& KTPermission::getByName($this->_sPermission);
        if(PEAR::isError($oPermission)) { 
            return true;
        }

        // basic document checks

        // TODO: check if this is appropriate
        //       should probably store the 'equivalent' action (ie. document.delete)
        //       and check that, rather than add a new list of actions to the workflow 
        //       section
        if(is_a($oEntity, 'Document')) {
            if(!KTWorkflowUtil::actionEnabledForDocument($oEntity, $this->sName)) {
                return PEAR::raiseError(_kt('Action is disabled by workflow'));
            }
            $status = $oEntity->getStatusID();
            if($status==DELETED||$status==ARCHIVED) {
                return PEAR::raiseError(_kt('Document is archived or deleted'));
            }
        }
        
        // admin check
        if($this->bAllowInAdminMode) {
            if(KTBrowseUtil::inAdminMode($this->oUser, null)) {
                return true;
            }
        }
        
        if(!KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $oEntity)) {
            return PEAR::raiseError(_kt('You do not have the required permissions'));
        }

        return true;
    }


}

class KTBulkDocumentAction extends KTBulkAction {
    function check_entity($oEntity) {
        if(!is_a($oEntity, 'Document')) {
            return false;
        } 
        return parent::check_entity($oEntity);
    }
}

class KTBulkFolderAction extends KTBulkAction {
    function check_entity($oEntity) {
        if(!is_a($oEntity, 'Folder')) {
            return false;
        } 
        return parent::check_entity($oEntity);
    }
}



// util class for bulk actions

class KTBulkActionUtil {
    function getBulkActionInfo($slot = "bulkaction") {
        $oRegistry =& KTActionRegistry::getSingleton();
        return $oRegistry->getActions($slot);
    }

    function getAllBulkActions($slot = "bulkaction") {
        $aObjects = array();

        foreach (KTBulkActionUtil::getBulkActionInfo($slot) as $aAction) {
            list($sClassName, $sPath, $sName, $sPlugin) = $aAction;
            $oRegistry =& KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin($sPlugin);
            if (!empty($sPath)) {
                require_once($sPath);
            }
            $aObjects[] =& new $sClassName(null, null, $oPlugin);
        }
        return $aObjects;
    }

    function getBulkActionsByNames($aNames, $slot = "bulkaction", $oUser = null) {
        $aObjects = array();
        foreach (KTBulkActionUtil::getBulkActionInfo($slot) as $aAction) {
            list($sClassName, $sPath, $sName, $sPlugin) = $aAction;
            $oRegistry =& KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin($sPlugin);
            if (!in_array($sName, $aNames)) {
                continue;
            }
            if (!empty($sPath)) {
                require_once($sPath);
            }
            $aObjects[] =& new $sClassName(null, $oUser, $oPlugin);
        }
        return $aObjects;
    }
}

?>