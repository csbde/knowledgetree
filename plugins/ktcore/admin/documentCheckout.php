<?php
/*
 * Document Checkout Administration
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 */

/* boilerplate */


require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');

class KTCheckoutAdminDispatcher extends KTAdminDispatcher {
    var $sHelpPage = 'ktcore/admin/document checkout.html';
    function check() {
        return true;
    }

    function do_main() {
        $this->aBreadcrumbs[] = array('name' => _kt('Document Checkout'));
        $this->oPage->setBreadcrumbDetails(_kt('list checked out documents'));
        
        $aDocuments = Document::getList('is_checked_out = 1');
    
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/checkoutlisting');       
        $oTemplate->setData(array(
            'context' => $this,
            'documents' => $aDocuments,
        ));
        return $oTemplate;        
    }
    

    function do_confirm() {
        $this->aBreadcrumbs[] = array('name' => _kt('Document Checkout'));
        $this->oPage->setBreadcrumbDetails(_kt('confirm forced check-in'));
        
        $document_id = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
        if (empty($document_id)) {
            return $this->errorRedirectToMain(_kt('You must select a document to check in first.'));
        }
    
        $oDocument = Document::get($document_id);
        if (PEAR::isError($oDocument)) {
            return $this->errorRedirectToMain(_kt('The document you specified appears to be invalid.'));
        }
        
        $oUser = User::get($oDocument->getCheckedOutUserID());
        // unusually, we could well have an error here:  the user may have checked out and then
        // been deleted.
        if (PEAR::isError($oUser) || ($oUser === false)) { 
            $oUser = null; 
        }
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/force_checkin_confirm');       
        $oTemplate->setData(array(
            'context' => $this,
            'document' => $oDocument,
            'checkout_user' => $oUser,
        ));
        return $oTemplate;                
        
    }	

    function do_checkin() {
        global $default;
        
        $document_id = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
        if (empty($document_id)) {
            return $this->errorRedirectToMain(_kt('You must select a document to check in first.'));
        }
    
        $oDocument = Document::get($document_id);
        if (PEAR::isError($oDocument)) {
            return $this->errorRedirectToMain(_kt('The document you specified appears to be invalid.'));
        }
        
        $this->startTransaction();
        // actually do the checkin.
        $oDocument->setIsCheckedOut(0);
        $oDocument->setCheckedOutUserID(-1);
        if (!$oDocument->update()) {
            $this->rollbackTransaction();
            return $this->errorRedirectToMain(_kt("Failed to force the document's checkin."));
        }
        
        // checkout cancelled transaction
        $oDocumentTransaction = & new DocumentTransaction($oDocument, _kt('Document checked out cancelled'), 'ktcore.transactions.force_checkin');
        $res = $oDocumentTransaction->create();
        if (PEAR::isError($res) || ($res == false)) {
            $this->rollbackTransaction();
            return $this->errorRedirectToMain(_kt("Failed to force the document's checkin."));
        }
        $this->commitTransaction(); // FIXME do we want to do this if we can't created the document-transaction?
        return $this->successRedirectToMain(sprintf(_kt('Successfully forced "%s" to be checked in.'), $oDocument->getName()));
    }


}

// use the new admin framework.
//$d = new KTCheckoutAdminDispatcher();
//$d->dispatch();

?>
