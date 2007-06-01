<?php
/*
 * Document Checkout Administration
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
