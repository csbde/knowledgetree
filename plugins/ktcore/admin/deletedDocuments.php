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
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');

require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

class DeletedDocumentsDispatcher extends KTAdminDispatcher {
    function do_main () {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Deleted Documents'));
        
        $this->oPage->setBreadcrumbDetails(_('view'));
    
        $aDocuments =& Document::getList("status_id=" . DELETED);
        
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/deletedlist');
        $oTemplate->setData(array(
            'context' => $this,
            'documents' => $aDocuments,
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
        $this->errorRedirectToMain(_('No action specified.'));
    }
    
    function do_confirm_expunge() {
        $this->aBreadcrumbs[] = array('url' =>  $_SERVER['PHP_SELF'], 'name' => _('Deleted Documents'));
        
        $selected_docs = KTUtil::arrayGet($_REQUEST, 'selected_docs', array()); 
        
        $this->oPage->setTitle(sprintf(_('Confirm Expunge of %d documents'), count($selected_docs)));
        
        $this->oPage->setBreadcrumbDetails(sprintf(_('confirm expunge of %d documents'), count($selected_docs)));
    
        $aDocuments = array();
        foreach ($selected_docs as $doc_id) {
            $oDoc =& Document::get($doc_id);
            if (PEAR::isError($oDoc) || ($oDoc === false)) { 
                $this->errorRedirectToMain(_('Invalid document id specified. Aborting expunge'));
            } else if ($oDoc->getStatusId() != DELETED) {
                $this->errorRedirectToMain(sprintf(_('%s is not a deleted document. Aborting expunge'), $oDoc->getName()));
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
                $this->errorRedirectToMain(_('Invalid document id specified. Aborting expunge'));
            } else if ($oDoc->getStatusId() != DELETED) {
                $this->errorRedirectToMain(sprintf(_('%s is not a deleted document. Aborting expunge'), $oDoc->getName()));
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
            if (PEAR::isError($oFolder)) { $oDoc->setFolderId(1); }
        
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
        $msg = sprintf(_('%d documents expunged.'), count($aSuccessDocuments));
        if (count($aErrorDocuments) != 0) { $msg .= _('Failed to expunge') . ': ' . join(', ', $aErrorDocuments); }
        $this->successRedirectToMain($msg);
    }
    
    function do_confirm_restore() {
        $this->aBreadcrumbs[] = array('url' =>  $_SERVER['PHP_SELF'], 'name' => _('Deleted Documents'));
        
        $selected_docs = KTUtil::arrayGet($_REQUEST, 'selected_docs', array()); 
        
        $this->oPage->setTitle(sprintf(_('Confirm Restore of %d documents'), count($selected_docs)));
        
        $this->oPage->setBreadcrumbDetails(sprintf(_('Confirm Restore of %d documents'), count($selected_docs)));
    
        $aDocuments = array();
        foreach ($selected_docs as $doc_id) {
            $oDoc =& Document::get($doc_id);
            if (PEAR::isError($oDoc) || ($oDoc === false)) { 
                $this->errorRedirectToMain(_('Invalid document id specified. Aborting expunge'));
            } else if ($oDoc->getStatusId() != DELETED) {
                $this->errorRedirectToMain(sprintf(_('%s is not a deleted document. Aborting expunge'), $oDoc->getName()));
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
                $this->errorRedirectToMain(_('Invalid document id specified. Aborting restore'));
            } else if ($oDoc->getStatusId() != DELETED) {
                $this->errorRedirectToMain(sprintf(_('%s is not a deleted document. Aborting restore'), $oDoc->getName()));
            }
            $aDocuments[] = $oDoc;
        }
     
        $this->startTransaction();
        $aErrorDocuments = array();
        $aSuccessDocuments = array();			

        $oStorage =& KTStorageManagerUtil::getSingleton();

        foreach ($aDocuments as $oDoc) {
            $oFolder = Folder::get($oDoc->getFolderID());
            if (PEAR::isError($oFolder)) { $oDoc->setFolderId(1); } // move to root if parent no longer exists.
            if ($oStorage->restore($oDoc)) {
                $oDoc->setStatusId(LIVE);
                $res = $oDoc->update();
                if (PEAR::isError($res) || ($res == false)) {
                    $aErrorDocuments[] = $oDoc->getName;
                    continue; // skip transactions, etc.
                }
                
                $res = KTPermissionUtil::updatePermissionLookup($oDoc);
                
                if (PEAR::isError($res)) {
                    $aErrorDocuments[] = $oDoc->getName;
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
        $msg = sprintf(_('%d documents restored.'), count($aSuccessDocuments));
        if (count($aErrorDocuments) != 0) { $msg .= _('Failed to restore') . ': ' . join(', ', $aErrorDocuments); }
        $this->successRedirectToMain($msg);
    }    
    
}

?>
