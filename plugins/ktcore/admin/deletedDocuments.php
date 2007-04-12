<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
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
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');

require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

class DeletedDocumentsDispatcher extends KTAdminDispatcher {
var $sHelpPage = 'ktcore/admin/deleted documents.html';
    function do_main () {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Deleted Documents'));
        
        $this->oPage->setBreadcrumbDetails(_kt('view'));
    
        $aDocuments =& Document::getList("status_id=" . DELETED);
		$items = count($aDocuments);
		
		if(fmod($items, 10) > 0){
			$pages = floor($items/10)+1;
		}else{
			$pages = ($items/10);
		}
		for($i=1; $i<=$pages; $i++){
			$aPages[] = $i;
		}
		
		for($i = 0; $i <= 9; $i++){
			$aDocumentsList[] = $aDocuments[$i];
		}
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/deletedlist');
        $oTemplate->setData(array(
            'context' => $this,
            'documents' => $aDocumentsList,
            'pagelist' => $aPages,
            'pagecount' => $pages,
            'itemcount' => $items,
        ));
        return $oTemplate;
    }
    
    function do_branchConfirm() {
        $submit = KTUtil::arrayGet($_REQUEST, 'submit' , array());
        if (array_key_exists('expunge',$submit)) {
            return $this->do_confirm_expunge();
        }
        if (array_key_exists('restore', $submit)) {
            return $this->do_confirm_restore();
        }
        $this->errorRedirectToMain(_kt('No action specified.'));
    }
    
    function do_confirm_expunge() {
        $this->aBreadcrumbs[] = array('url' =>  $_SERVER['PHP_SELF'], 'name' => _kt('Deleted Documents'));
        
        $selected_docs = KTUtil::arrayGet($_REQUEST, 'selected_docs', array()); 
        
        $this->oPage->setTitle(sprintf(_kt('Confirm Expunge of %d documents'), count($selected_docs)));
        
        $this->oPage->setBreadcrumbDetails(sprintf(_kt('confirm expunge of %d documents'), count($selected_docs)));
    
        $aDocuments = array();
        foreach ($selected_docs as $doc_id) {
            $oDoc =& Document::get($doc_id);
            if (PEAR::isError($oDoc) || ($oDoc === false)) { 
                $this->errorRedirectToMain(_kt('Invalid document id specified. Aborting expunge'));
            } else if ($oDoc->getStatusId() != DELETED) {
                $this->errorRedirectToMain(sprintf(_kt('%s is not a deleted document. Aborting expunge'), $oDoc->getName()));
            }
            $aDocuments[] = $oDoc;
        }
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/expungeconfirmlist');
        $oTemplate->setData(array(
            'context' => $this,
            'documents' => $aDocuments,
        ));
        return $oTemplate;
    }

    function do_finish_expunge() {

        
        $selected_docs = KTUtil::arrayGet($_REQUEST, 'selected_docs', array()); 
    
        $aDocuments = array();
        foreach ($selected_docs as $doc_id) {
            $oDoc =& Document::get($doc_id);
            if (PEAR::isError($oDoc) || ($oDoc === false)) { 
                $this->errorRedirectToMain(_kt('Invalid document id specified. Aborting expunge'));
            } else if ($oDoc->getStatusId() != DELETED) {
                $this->errorRedirectToMain(sprintf(_kt('%s is not a deleted document. Aborting expunge'), $oDoc->getName()));
            }
            $aDocuments[] = $oDoc;
        }
     
        $this->startTransaction();
        $aErrorDocuments = array();
        $aSuccessDocuments = array();			

        $oStorage =& KTStorageManagerUtil::getSingleton();

        foreach ($aDocuments as $oDoc) {
            // first evaluate the folder for inconsistencies.
            $oFolder = Folder::get($oDoc->getFolderID());
            if (PEAR::isError($oFolder)) { $oDoc->setFolderId(1); $oDoc->update(); }
        
            if (!$oStorage->expunge($oDoc)) { $aErrorDocuments[] = $oDoc->getDisplayPath(); }
            else {
                $oDocumentTransaction = & new DocumentTransaction($oDoc, "Document expunged", 'ktcore.transactions.expunge');
                $oDocumentTransaction->create();
    
                // delete this from the db now
                if (!$oDoc->delete()) { $aErrorDocuments[] = $oDoc->getId(); } 
                else {
                    // removed succesfully
                    $aSuccessDocuments[] = $oDoc->getDisplayPath();
        
                    // remove any document data
                    $oDoc->cleanupDocumentData($oDoc->getId()); // silly - why the redundancy?
                }
            }
        }
        $this->commitTransaction();
        $msg = sprintf(_kt('%d documents expunged.'), count($aSuccessDocuments));
        if (count($aErrorDocuments) != 0) { $msg .= _kt('Failed to expunge') . ': ' . join(', ', $aErrorDocuments); }
        $this->successRedirectToMain($msg);
    }
    
    function do_confirm_restore() {
        $this->aBreadcrumbs[] = array('url' =>  $_SERVER['PHP_SELF'], 'name' => _kt('Deleted Documents'));
        
        $selected_docs = KTUtil::arrayGet($_REQUEST, 'selected_docs', array()); 
        
        $this->oPage->setTitle(sprintf(_kt('Confirm Restore of %d documents'), count($selected_docs)));
        
        $this->oPage->setBreadcrumbDetails(sprintf(_kt('Confirm Restore of %d documents'), count($selected_docs)));
    
        $aDocuments = array();
        foreach ($selected_docs as $doc_id) {
            $oDoc =& Document::get($doc_id);
            if (PEAR::isError($oDoc) || ($oDoc === false)) { 
                $this->errorRedirectToMain(_kt('Invalid document id specified. Aborting expunge'));
            } else if ($oDoc->getStatusId() != DELETED) {
                $this->errorRedirectToMain(sprintf(_kt('%s is not a deleted document. Aborting expunge'), $oDoc->getName()));
            }
            $aDocuments[] = $oDoc;
        }
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/restoreconfirmlist');
        $oTemplate->setData(array(
            'context' => $this,
            'documents' => $aDocuments,
        ));
        return $oTemplate;
    }

    function do_finish_restore() {        
        $selected_docs = KTUtil::arrayGet($_REQUEST, 'selected_docs', array()); 
    
        $aDocuments = array();
        foreach ($selected_docs as $doc_id) {
            $oDoc =& Document::get($doc_id);
            if (PEAR::isError($oDoc) || ($oDoc === false)) { 
                $this->errorRedirectToMain(_kt('Invalid document id specified. Aborting restore'));
            } else if ($oDoc->getStatusId() != DELETED) {
                $this->errorRedirectToMain(sprintf(_kt('%s is not a deleted document. Aborting restore'), $oDoc->getName()));
            }
            $aDocuments[] = $oDoc;
        }
     
        $this->startTransaction();
        $aErrorDocuments = array();
        $aSuccessDocuments = array();			

        $oStorage =& KTStorageManagerUtil::getSingleton();

        foreach ($aDocuments as $oDoc) {
            $oFolder = Folder::get($oDoc->getRestoreFolderId());
            // move to root if parent no longer exists.
            if (PEAR::isError($oFolder)) { 
                $oDoc->setFolderId(1);  
                $oFolder = Folder::get(1);
            } else { 
                $oDoc->setFolderId($oDoc->getRestoreFolderId());
            }
            
            if ($oStorage->restore($oDoc)) {
                $oDoc = Document::get($oDoc->getId()); // storage path has changed for most recent object...
                $oDoc->setStatusId(LIVE);
                $oDoc->setPermissionObjectId($oFolder->getPermissionObjectId());
                $res = $oDoc->update();
                if (PEAR::isError($res) || ($res == false)) {
                    $aErrorDocuments[] = $oDoc->getName();
                    continue; // skip transactions, etc.
                }
                
                $res = KTPermissionUtil::updatePermissionLookup($oDoc);
                
                if (PEAR::isError($res)) {
                    $aErrorDocuments[] = $oDoc->getName();
                    continue; // skip transactions, etc.
                }
                
                // create a doc-transaction.
                // FIXME does this warrant a transaction-type?
                $oTransaction = new DocumentTransaction($oDoc, 'Restored from deleted state by ' . $this->oUser->getName(), 'ktcore.transactions.update');
                if (!$oTransaction->create()) {
                    ; // do nothing?  the state of physicaldocumentmanager...
                }
                $aSuccessDocuments[] = $oDoc->getName();
            } else {
                $aErrorDocuments[] = $oDoc->getName();
            }
        }
        $this->commitTransaction();
        $msg = sprintf(_kt('%d documents restored.'), count($aSuccessDocuments));
        if (count($aErrorDocuments) != 0) { $msg .= _kt('Failed to restore') . ': ' . join(', ', $aErrorDocuments); }
        $this->successRedirectToMain($msg);
    }    
    
    function getRestoreLocationFor($oDocument) {
        $iFolderId = $oDocument->getRestoreFolderId();    
        $oFolder = Folder::get($iFolderId);
        
        if (PEAR::isError($oFolder)) {
            return _kt('Original folder no longer exists.  Document will be restored in the root folder.'); 
        } else {
            $aCrumbs = KTBrowseUtil::breadcrumbsForFolder($oFolder);
            $aParts = array();
            foreach ($aCrumbs as $aInfo) {
                $aParts[] = $aInfo['name'];
            }
            return implode(' &raquo; ', $aParts);
        }
    }
}

?>
