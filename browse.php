<?php

/**
 * $Id$
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
 * @version $Revision$
 * @author Brad Shuttleworth <brad@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 */

// main library routines and defaults
require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");
require_once(KT_LIB_DIR . "/browse/browseutil.inc.php");

require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentType.inc");
require_once(KT_LIB_DIR . "/documentmanagement/Document.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");

require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");
require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_DIR . '/plugins/ktcore/KTFolderActions.php');


/******* NBM's FAMOUS MOVECOLUMN HACK
 *
 * Also in /plugins/ktcore/KTDocumentActions.php
 */

class KTMassMoveColumn extends TitleColumn {
    var $sMoveCode;

    function KTMassMoveColumn($sLabel, $sName, $sMoveCode) {
        $this->sMoveCode = $sMoveCode;
        parent::TitleColumn($sLabel, $sName);
    }
    
    function renderFolderLink($aDataRow) {
        $aFolders = $_SESSION['moves'][$this->sMoveCode]['folders'];
        if (array_search($aDataRow['folder']->getId(), $aFolders) === false) {
            $outStr = '<a href="' . $this->buildFolderLink($aDataRow) . '">';
            $outStr .= $aDataRow["folder"]->getName();
            $outStr .= '</a>';
        } else { 
            $outStr = $aDataRow["folder"]->getName() . ' <span class="descriptiveText">(' . _('you cannot move folders to themselves') . ')';
        }
        return $outStr;    
    
    }
    
    function buildFolderLink($aDataRow) {
        return KTUtil::addQueryStringSelf(sprintf('fMoveCode=%s&fFolderId=%d&action=startMove', $this->sMoveCode, $aDataRow["folder"]->getId()));
    }
}

$sectionName = "browse";

class BrowseDispatcher extends KTStandardDispatcher {

    var $oFolder = null;
    var $sSection = "browse";
    var $browse_mode = null;
    var $query = null;
    var $resultURL;
    var $sHelpPage = 'ktcore/browse/browse.html';
    var $editable;

    function BrowseDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'browse', 'name' => _('Browse')),
        );
        return parent::KTStandardDispatcher();
    }
    
    function check() {
        $this->browse_mode = KTUtil::arrayGet($_REQUEST, 'fBrowseMode', "folder"); 
        $action = KTUtil::arrayGet($_REQUEST, $this->event_var, 'main');
        $this->editable = false;
        
        
        // catch the alternative actions.
        if ($action != 'main') {
            return true;
        } 
        
        // if we're going to main ...
        if ($this->browse_mode == 'folder') {
            $this->editable = true;
            $in_folder_id = KTUtil::arrayGet($_REQUEST, "fFolderId", 1);
            $folder_id = (int) $in_folder_id; // conveniently, will be 0 if not possible.
            if ($folder_id == 0) {
                $folder_id = 1;
            }
            
            $_REQUEST['fBrowseMode'] = 'folder';
            
            // here we need the folder object to do the breadcrumbs.
            $oFolder =& Folder::get($folder_id);

            $this->oPage->setTitle(_('Browse'));
            $this->oPage->setSecondaryTitle($oFolder->getName());
            
            $this->oFolder =& $oFolder;
            if (PEAR::isError($oFolder)) {
                $this->oPage->addError(_("invalid folder"));
                $folder_id = 1;
                $oFolder =& Folder::get($folder_id);
            }
            
            // we now have a folder, and need to create the query.
            $this->oQuery =  new BrowseQuery($oFolder->getId());
            
            $this->aBreadcrumbs = array_merge($this->aBreadcrumbs,
                KTBrowseUtil::breadcrumbsForFolder($oFolder));
                
            $portlet = new KTActionPortlet(_("Folder Actions"));
            $aActions = KTFolderActionUtil::getFolderActionsForFolder($oFolder, $this->oUser);        
            $portlet->setActions($aActions,null);
            $this->oPage->addPortlet($portlet);
            $this->resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("fFolderId=%d", $oFolder->getId()));
        } else if ($this->browse_mode == 'lookup_value') {
            $this->editable = false;
            $field = KTUtil::arrayGet($_REQUEST, 'fField', null);
            $oField = DocumentField::get($field);
            if (PEAR::isError($oField) || ($oField == false)) {
                $this->errorRedirectToMain('No Field selected.');
                exit(0);            
            }
            $value = KTUtil::arrayGet($_REQUEST, 'fValue', null);
            $oValue = MetaData::get($value);
            if (PEAR::isError($oValue) || ($oValue == false)) {
                $this->errorRedirectToMain('No Value selected.');
                exit(0);            
            }
            $this->oQuery = new ValueBrowseQuery($oField, $oValue);
            $this->resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("fBrowseMode=lookup_value&fField=%d&fValue=%d", $field, $value));
            $this->aBreadcrumbs[] = array('name' => _('Lookup Values'), 'url' => KTUtil::addQueryString($_SERVER['PHP_SELF'], 'action=selectField')); 
            $this->aBreadcrumbs[] = array('name' => $oField->getName(), 'url' => KTUtil::addQueryString($_SERVER['PHP_SELF'], 'action=selectLookup&fField=' . $oField->getId()));             
            $this->aBreadcrumbs[] = array('name' => $oField->getName(), 'url' => KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("fBrowseMode=lookup_value&fField=%d&fValue=%d", $field, $value)));             
        } else if ($this->browse_mode == 'document_type') {
            $this->editable = false;
            // FIXME implement document_type browsing.
            $doctype = KTUtil::arrayGet($_REQUEST, 'fType',null);
            $oDocType = DocumentType::get($doctype);
            if (PEAR::isError($oDocType) || ($oDocType == false)) {
                $this->errorRedirectToMain('No Document Type selected.');
                exit(0);
            }
            
            $this->oQuery =  new TypeBrowseQuery($oDocType);
            
            // FIXME probably want to redirect to self + action=selectType
            $this->aBreadcrumbs[] = array('name' => _('Document Types'), 'url' => KTUtil::addQueryString($_SERVER['PHP_SELF'], 'action=selectType')); 
            $this->aBreadcrumbs[] = array('name' => $oDocType->getName(), 'url' => KTUtil::addQueryString($_SERVER['PHP_SELF'], 'fBrowseMode=document_type&fType=' . $oDocType->getId())); 
            
            $this->resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("fType=%s&fBrowseMode=document_type", $doctype));;
        } else {
            // FIXME what should we do if we can't initiate the browse?  we "pretend" to have no perms.
            return false;
        }

        return true;
    }

    function do_main() {
        $collection = new DocumentCollection;
        
        
        $collection->addColumn(new SelectionColumn("Browse Selection","selection"));
        $collection->addColumn(new TitleColumn("Test 1 (title)","title"));
        $collection->addColumn(new DownloadColumn('','download'));
        $collection->addColumn(new DateColumn(_("Created"),"created", "getCreatedDateTime"));
        $collection->addColumn(new DateColumn(_("Last Modified"),"modified", "getLastModifiedDate"));
        $collection->addColumn(new UserColumn(_('Creator'),'creator_id','getCreatorID'));
        $collection->addColumn(new WorkflowColumn(_('Workflow State'),'workflow_state'));
        
        
        // setup the folderside add actions
        // FIXME do we want to use folder actions?
        
        $batchPage = (int) KTUtil::arrayGet($_REQUEST, "page", 0);
        $batchSize = 20;
        
        
        $collection->setBatching($this->resultURL, $batchPage, $batchSize); 
        
        
        // ordering. (direction and column)
        $displayOrder = KTUtil::arrayGet($_REQUEST, 'sort_order', "asc");		
        if ($displayOrder !== "asc") { $displayOrder = "desc"; }
        $displayControl = KTUtil::arrayGet($_REQUEST, 'sort_on', "title");		
        
        
        $collection->setSorting($displayControl, $displayOrder);
        
        // add in the query object.
        $qObj = $this->oQuery;
        $collection->setQueryObject($qObj);
        
        // breadcrumbs
        // FIXME handle breadcrumbs
        $collection->getResults();
        
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/browse");
        $aTemplateData = array(
              "context" => $this,
              "collection" => $collection,
              'browse_mode' => $this->browse_mode,
              'isEditable' => $this->editable,
        );
        return $oTemplate->render($aTemplateData);
    }   
    
    function do_selectField() {
        $aFields = DocumentField::getList('has_lookup = 1');
        
        if (empty($aFields)) {
            $this->errorRedirectToMain('No lookup fields available.');
            exit(0);
        } 
        
        $_REQUEST['fBrowseMode'] = 'lookup_value';
        
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/browse_lookup_selection");
        $aTemplateData = array(
              "context" => $this,
              "fields" => $aFields,
        );
        return $oTemplate->render($aTemplateData);
    }
    
    function do_selectLookup() {
        $field = KTUtil::arrayGet($_REQUEST, 'fField', null);
        $oField = DocumentField::get($field);
        if (PEAR::isError($oField) || ($oField == false) || (!$oField->getHasLookup())) {
            $this->errorRedirectToMain('No Field selected.');
            exit(0);            
        }
        
        $_REQUEST['fBrowseMode'] = 'lookup_value';        
        
        $aValues = MetaData::getByDocumentField($oField);
        
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/browse_lookup_value");
        $aTemplateData = array(
              "context" => $this,
              "oField" => $oField,
              "values" => $aValues,
        );
        return $oTemplate->render($aTemplateData);
    }    
    
    function do_selectType() {
        $aTypes = DocumentType::getList();
        // FIXME what is the error message?
        
        $_REQUEST['fBrowseMode'] = 'document_type';
        
        if (empty($aTypes)) {
            $this->errorRedirectToMain('No document types available.');
            exit(0);
        } 
        
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/browse_types");
        $aTemplateData = array(
              "context" => $this,
              "document_types" => $aTypes,
        );
        return $oTemplate->render($aTemplateData);
    }
    
    function do_massaction() {
        // FIXME replace this by using real actions.
        $act = (array) KTUtil::arrayGet($_REQUEST, 'submit',null);
        
        $targets = array_keys($act);
        if (!empty($targets)) {
            $target = $targets[0];
        } else {
            $this->errorRedirectToMain(_('No action selected.'));
            exit(0);
        }

        $aFolderSelection = KTUtil::arrayGet($_REQUEST, 'selection_f' , array());
        $aDocumentSelection = KTUtil::arrayGet($_REQUEST, 'selection_d' , array());        
        if (empty($aFolderSelection) && empty($aDocumentSelection)) {
            $this->errorRedirectToMain(_('Please select documents or folders first.'));
            exit(0);
        }        
        
        if ($target == 'delete') {
            return $this->do_startDelete();
        } else if ($target == 'move') {
            return $this->do_startMove();
        } else {
            $this->errorRedirectToMain(_('No such action.'));
            exit(0);
        }
        
        return $target;
    }
    
    function do_startMove() {
        $this->oPage->setTitle('Move Files and Folders');
        $this->oPage->setBreadcrumbDetails('Move Files and Folders');
    
        // FIXME double-check that the movecode actually exists...
    
        $sMoveCode = KTUtil::arrayGet($_REQUEST, 'fMoveCode', null);
        if ($sMoveCode == null) {
            $aFolderSelection = KTUtil::arrayGet($_REQUEST, 'selection_f' , array());
            $aDocumentSelection = KTUtil::arrayGet($_REQUEST, 'selection_d' , array());

            $aCantMove = array();
            $aFinalDocumentSelection = array();
            $aMoveData = array('folders' => $aFolderSelection, 'documents' => array());
            foreach ($aDocumentSelection as $iDocumentId) {
                $oDocument = Document::get($iDocumentId);
                if (!KTDocumentUtil::canBeMoved($oDocument)) {
                    $aCantMove['documents'][] = $iDocumentId;
                    continue;
                }
                $aMoveData['documents'][] = $iDocumentId;
            }
            
            $sMoveCode = KTUtil::randomString();
            $moves = KTUtil::arrayGet($_SESSION, 'moves', array());
            $moves = (array) $moves; // ?
            $moves[$sMoveCode] = $aMoveData;
            $_SESSION['moves'] = $moves; // ...
        }

        if (!empty($aCantMove)) {
            $cantMoveItems = array();
            $cantMoveItems['folders'] = array();
            $cantMoveItems['documents'] = array();

            $folderStr = '';
            $documentStr = '';
            
            if (!empty($aCantMove['folders'])) {
                $folderStr = '<strong>' . _('Folders: ') . '</strong>';
                foreach ($aCantMove['folders'] as $iFolderId) {
                    $oF = Folder::get($iFolderId);
                    $cantMoveItems['folders'][] = $oF->getName();
                }
                $folderStr .= implode(', ', $cantMoveItems['folders']);
            }
            
            if (!empty($aCantMove['documents'])) {
                $documentStr = '<strong>' . _('Documents: ') . '</strong>';
                foreach ($aCantMove['documents'] as $iDocId) {
                    $oD = Document::get($iDocId);
                    $cantMoveItems['documents'][] = $oD->getName();
                }
                $documentStr .= implode(', ', $cantMoveItems['documents']);
            }

            if (!empty($folderStr)) {
                $_SESSION["KTErrorMessage"][] = _("The following folders can not be moved") . ": " . $folderStr;
            }
            if (!empty($documentStr)) {
                $_SESSION["KTErrorMessage"][] = _("The following documents can not be moved") . ": " . $documentStr;
            }
        }

        
        
        $oFolder = Folder::get(KTUtil::arrayGet($_REQUEST, 'fFolderId', 1));
        if (PEAR::isError($oFolder)) { 
            $this->errorRedirectToMain(_('Invalid folder selected.'));
            exit(0);
        }

        $moveSet = $_SESSION['moves'][$sMoveCode];

        if (empty($moveSet['folders']) && empty($moveSet['documents'])) {
            $this->errorRedirectToMain(_('Please select documents or folders first.'), sprintf('fFolderId=%d', $oFolder->getId()));
            exit(0);
        }        
        
        // Setup the collection for move display.
        
        $collection = new DocumentCollection();
        $collection->addColumn(new KTMassMoveColumn("Test 1 (title)","title", $sMoveCode));
        $qObj = new FolderBrowseQuery($oFolder->getId());
        $collection->setQueryObject($qObj);

        $batchPage = (int) KTUtil::arrayGet($_REQUEST, "page", 0);
        $batchSize = 20;

        $resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("fMoveCode=%s&fFolderId=%d&action=startMove", $sMoveCode, $oFolder->getId()));
        $collection->setBatching($resultURL, $batchPage, $batchSize);

        // ordering. (direction and column)
        $displayOrder = KTUtil::arrayGet($_REQUEST, 'sort_order', "asc");
        if ($displayOrder !== "asc") { $displayOrder = "desc"; }
        $displayControl = KTUtil::arrayGet($_REQUEST, 'sort_on', "title");

        $collection->setSorting($displayControl, $displayOrder);

        $collection->getResults();

        $aBreadcrumbs = array();
        $folder_path_names = $oFolder->getPathArray();
        $folder_path_ids = explode(',', $oFolder->getParentFolderIds());
        $folder_path_ids[] = $oFolder->getId();
        if ($folder_path_ids[0] == 0) {
            array_shift($folder_path_ids);
            array_shift($folder_path_names);
        }

        foreach (range(0, count($folder_path_ids) - 1) as $index) {
            $id = $folder_path_ids[$index];
            $url = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("fMoveCode=%s&fFolderId=%d&action=startMove", $sMoveCode, $id));
            $aBreadcrumbs[] = array("url" => $url, "name" => $folder_path_names[$index]);
        }
        
        
        // now show the items...
        $moveItems = array();
        $moveItems['folders'] = array();
        $moveItems['documents'] = array();
        
        $folderStr = '';
        $documentStr = '';
        
        if (!empty($moveSet['folders'])) {
            $folderStr = '<strong>' . _('Folders: ') . '</strong>';
            foreach ($moveSet['folders'] as $iFolderId) {
                $oF = Folder::get($iFolderId);
                $moveItems['folders'][] = $oF->getName();
            }
            $folderStr .= implode(', ', $moveItems['folders']);
        }
        
        if (!empty($moveSet['documents'])) {
            $documentStr = '<strong>' . _('Documents: ') . '</strong>';
            foreach ($moveSet['documents'] as $iDocId) {
                $oD = Document::get($iDocId);
                $moveItems['documents'][] = $oD->getName();
            }
            $documentStr .= implode(', ', $moveItems['documents']);
        }
        
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/action/mass_move");
        $aTemplateData = array(
              "context" => $this,
              'folder' => $oFolder,
              'move_code' => $sMoveCode,
              'collection' => $collection,
              'collection_breadcrumbs' => $aBreadcrumbs,
              'folders' => $folderStr,
              'documents' => $documentStr,
        );
        
        return $oTemplate->render($aTemplateData);       
    }
    
    function do_finaliseMove() {
        // FIXME this is a PITA.    
        
        $action_a = (array) KTUtil::arrayGet($_REQUEST, 'submit', null);
        $actions = array_keys($action_a);
        if (empty($actions)) { 
            $this->errorRedirectToMain(_('No action selected.'));
        } else {
            $action = $actions[0];
        }
        if ($action != 'move') {
            $this->successRedirectToMain(_('Move cancelled.'));
        }
        
        $target_folder = KTUtil::arrayGet($_REQUEST, 'fFolderId');
        if ($target_folder == null ) { $this->errorRedirectToMain(_('No folder selected.')); }
        
        $move_code = KTUtil::arrayGet($_REQUEST, 'fMoveCode');
        
        $aFields = array();
        $aFields[] = new KTStaticTextWidget(_('Destination folder'), _('The folder which will contain the previously selected files and folders.'), 'fDocumentId', Folder::getFolderDisplayPath($target_folder), $this->oPage, false);
        $aFields[] = new KTStringWidget(_('Reason'), _('The reason for moving these documents and folders, for historical purposes.'), 'sReason', "", $this->oPage, true); 
        
        
        // now show the items...
        $moveSet = $_SESSION['moves'][$move_code];
        $moveItems = array();
        $moveItems['folders'] = array();
        $moveItems['documents'] = array();
        
        $folderStr = '';
        $documentStr = '';
        
        if (!empty($moveSet['folders'])) {
            $folderStr = '<strong>' . _('Folders: ') . '</strong>';
            foreach ($moveSet['folders'] as $iFolderId) {
                $oF = Folder::get($iFolderId);
                $moveItems['folders'][] = $oF->getName();
            }
            $folderStr .= implode(', ', $moveItems['folders']);
        }
        
        if (!empty($moveSet['documents'])) {
            $documentStr = '<strong>' . _('Documents: ') . '</strong>';
            foreach ($moveSet['documents'] as $iDocId) {
                $oD = Document::get($iDocId);
                $moveItems['documents'][] = $oD->getName();
            }
            $documentStr .= implode(', ', $moveItems['documents']);
        }
        
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/action/finalise_mass_move");
        $aTemplateData = array(
              "context" => $this,
              'form_fields' => $aFields,
              'folder' => $target_folder,
              'move_code' => $move_code,
              'folders' => $folderStr,
              'documents' => $documentStr,              
        );
        return $oTemplate->render($aTemplateData);        
    }
    
    function do_move() {
        $move_code = KTUtil::arrayGet($_REQUEST, 'fMoveCode');
        $target_folder = KTUtil::arrayGet($_REQUEST, 'fFolderId');
        $reason = KTUtil::arrayGet($_REQUEST, 'sReason');
        
        // FIXME check perms?  or will that happen "lower" in the stack.
        
        $aMoveStack = $_SESSION['moves'][$move_code];
        
        
        $oTargetFolder = Folder::get($target_folder);
        
        if (PEAR::isError($oTargetFolder)) {
            return print_r($oTargetFolder, true);
        }
        
        if (!Permission::userHasFolderWritePermission($oTargetFolder)) {
            $this->errorRedirectTo("main", _("You do not have permission to move items to this location"), sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId()));
            exit(0);
        }
        
        
        
        $oStorage =& KTStorageManagerUtil::getSingleton();
        // FIXME refactor this IMMEDIATELY into documentutil::
        foreach ($aMoveStack['documents'] as $iDocId) {
            $this->startTransaction();
            
            $oDoc = Document::get($iDocId);
            if (PEAR::isError($oDoc)) { 
                $this->errorRedirectToMain(_('Invalid document.'));
            }
                
            $oOriginalFolder = Folder::get($oDoc->getFolderId());
            $iOriginalFolderPermissionObjectId = $oOriginalFolder->getPermissionObjectId();
            $iDocumentPermissionObjectId = $oDoc->getPermissionObjectId();
    
            if ($iDocumentPermissionObjectId === $iOriginalFolderPermissionObjectId) {
                $oDoc->setPermissionObjectId($oTargetFolder->getPermissionObjectId());
            }
    
            //put the document in the new folder
            $oDoc->setFolderID($oTargetFolder->getId());
            $res = $oDoc->update(true);
            if (!$res) {
                $this->errorRedirectTo("move", _("There was a problem updating the document's location in the database"), sprintf("fDocumentId=%d&fFolderId=%d", $oDoc->getId(), $oTargetFolder->getId()));
            }    
            
            //move the document on the file system
            $oStorage =& KTStorageManagerUtil::getSingleton();
            if (!$oStorage->moveDocument($oDoc, $oOriginalFolder, $oTargetFolder)) {
                $oDoc->setFolderID($oOriginalFolder->getId());
                $oDoc->update(true);
                $this->errorRedirectTo("move", _("There was a problem updating the document's location in the repository storage"), sprintf("fDocumentId=%d&fFolderId=%d", $oDoc->getId(), $oTargetFolder->getId()));
            }
    
            $sMoveMessage = sprintf("Moved from %s/%s to %s/%s: %s",
                $oOriginalFolder->getFullPath(),
                $oOriginalFolder->getName(),
                $oTargetFolder->getFullPath(),
                $oTargetFolder->getName(),
                $reason);
    
            // create the document transaction record
            
            $oDocumentTransaction = & new DocumentTransaction($oDoc, $sMoveMessage, 'ktcore.transactions.move');
            $oDocumentTransaction->create();

            $this->commitTransaction();
            
            $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
            $aTriggers = $oKTTriggerRegistry->getTriggers('moveDocument', 'postValidate');
            foreach ($aTriggers as $aTrigger) {
                $sTrigger = $aTrigger[0];
                $oTrigger = new $sTrigger;
                $aInfo = array(
                    "document" => $oDoc,
                    "old_folder" => $oDocumentFolder,
                    "new_folder" => $oTargetFolder,
                );
                $oTrigger->setInfo($aInfo);
                $ret = $oTrigger->postValidate();
                if (PEAR::isError($ret)) {
                    $oDoc->delete();
                    return $ret;
                }
            }
        }
        
        
        // now folders ... these are easier.
        $this->startTransaction();
        
        foreach ($aMoveStack['folders'] as $iFolderId) {
            $oFolder = Folder::get($iFolderId);
            if (PEAR::isError($oFolder)) { $this->errorRedirectToMain(_('Invalid folder.')); }
            
            $res = KTFolderUtil::move($oFolder, $oTargetFolder, $this->oUser);
            if (PEAR::isError($res)) {
                $this->errorRedirectToMain(_('Failed to move the folder: ') . $res->getMessage());
            }
        }
        $this->commitTransaction();
        
        
        $this->successRedirectToMain(_('Move completed.'), sprintf('fFolderId=%d', $target_folder));
    }
    
    function do_startDelete() {
        $this->oPage->setTitle('Delete Files and Folders');
        $this->oPage->setBreadcrumbDetails('Delete Files and Folders');
        $fFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', 1);
    
        $aFolderSelection = KTUtil::arrayGet($_REQUEST, 'selection_f' , array());
        $aDocumentSelection = KTUtil::arrayGet($_REQUEST, 'selection_d' , array());
        

        // now show the items...
        $delItems = array();
        $delItems['folders'] = array();
        $delItems['documents'] = array();
        
        $folderStr = '';
        $documentStr = '';
        
        if (!empty($aFolderSelection)) {
            $folderStr = '<strong>' . _('Folders: ') . '</strong>';
            foreach ($aFolderSelection as $iFolderId) {
                $oF = Folder::get($iFolderId);
                $delItems['folders'][] = $oF->getName();
            }
            $folderStr .= implode(', ', $delItems['folders']);
        }
        
        if (!empty($aDocumentSelection)) {
            $documentStr = '<strong>' . _('Documents: ') . '</strong>';
            foreach ($aDocumentSelection as $iDocId) {
                $oD = Document::get($iDocId);
                $delItems['documents'][] = $oD->getName();
            }
            $documentStr .= implode(', ', $delItems['documents']);
        }
        
        $aFields = array();
        $aFields[] = new KTStringWidget(_('Reason'), _('The reason for the deletion of these documents and folders for historical purposes.'), 'sReason', "", $this->oPage, true);
        
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/folder/mass_delete");
        $aTemplateData = array(
              "context" => $this,
              "folder_id" => $fFolderId,
              'form_fields' => $aFields,
              'folders' => $aFolderSelection,
              'documents' => $aDocumentSelection,
              'folder_string' => $folderStr,
              'document_string' => $documentStr,
        );
        return $oTemplate->render($aTemplateData);        
    }

    function do_doDelete() {
        $aFolderSelection = KTUtil::arrayGet($_REQUEST, 'selection_f' , array());
        $aDocumentSelection = KTUtil::arrayGet($_REQUEST, 'selection_d' , array());
        
        $fFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', 1);
        
        $aErrorOptions = array('message' => _('You must supply a reason'));
        
        $res = KTUtil::arrayGet($_REQUEST,'sReason');
        $sReason = $this->oValidator->notEmpty($res, $aErrorOptions);
        
        
        
        // FIXME we need to sort out the (inconsistent) use of transactions here.
        $aFolders = array();
        $aDocuments = array();
        foreach ($aFolderSelection as $id) {
            $oF = Folder::get($id);
            if (PEAR::isError($oF) || ($oF == false)) {
                return $this->errorRedirectToMain(_('Invalid Folder selected.'));
            } else {
                $aFolders[] = $oF;
            }
        }
        foreach ($aDocumentSelection as $id) {
            $oD = Document::get($id);
            if (!Permission::userHasDocumentWritePermission($oD)) {
                return $this->errorRedirectToMain(sprintf(_('You do not have permissions to delete the documen: %s'), $oD->getName()));             
            }
            if (PEAR::isError($oD) || ($oD == false)) {
                return $this->errorRedirectToMain(_('Invalid Document selected.'));
            } else {
                $aDocuments[] = $oD;
            }
        }
        
        foreach ($aFolders as $oFolder) {
            $res = KTFolderUtil::delete($oFolder, $this->oUser, $sReason);
            if (PEAR::isError($res)) {
                return $this->errorRedirectToMain($res->getMessage());
            }
        }
        foreach ($aDocuments as $oDocument) {
            $res = KTDocumentUtil::delete($oDocument, $sReason);
            if (PEAR::isError($res)) {
                return $this->errorRedirectToMain($res->getMessage());
            }
        }
        
        $this->successRedirectToMain('Folders and Documents Deleted.',sprintf('fFolderId=%d', $fFolderId));
    }
}

$oDispatcher = new BrowseDispatcher();
$oDispatcher->dispatch();

?>

