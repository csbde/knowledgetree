<?php

/*
 * Document Checkout Administration
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
 * @package documentmanagement
 */

/* boilerplate */


require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');

class KTCheckoutAdminDispatcher extends KTAdminDispatcher {

    function check() {
        return true;
    }

    function do_main() {
        $this->aBreadcrumbs[] = array('name' => _('Document Checkout'));
        $this->oPage->setBreadcrumbDetails(_("list checked out documents"));
        
        $aDocuments = Document::getList("is_checked_out = 1");
    
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/checkoutlisting');       
        $oTemplate->setData(array(
            "context" => $this,
            "documents" => $aDocuments,
        ));
        return $oTemplate;        
    }
    

    function do_confirm() {
        $this->aBreadcrumbs[] = array('name' => _('Document Checkout'));
        $this->oPage->setBreadcrumbDetails(_("confirm forced check-in"));
        
        $document_id = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
        if (empty($document_id)) {
            return $this->errorRedirectToMain(_('You must select a document to check in first.'));
        }
    
        $oDocument = Document::get($document_id);
        if (PEAR::isError($oDocument)) {
            return $this->errorRedirectToMain(_('The document you specified appears to be invalid.'));
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
            "context" => $this,
            "document" => $oDocument,
            "checkout_user" => $oUser,
        ));
        return $oTemplate;                
        
    }	

    function do_checkin() {
        global $default;
        
        $document_id = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
        if (empty($document_id)) {
            return $this->errorRedirectToMain(_('You must select a document to check in first.'));
        }
    
        $oDocument = Document::get($document_id);
        if (PEAR::isError($oDocument)) {
            return $this->errorRedirectToMain(_('The document you specified appears to be invalid.'));
        }
        
        $this->startTransaction();
        // actually do the checkin.
        $oDocument->setIsCheckedOut(0);
        $oDocument->setCheckedOutUserID(-1);
        if (!$oDocument->update()) {
            $this->rollbackTransaction();
            return $this->errorRedirectToMain(_("Failed to force the document's checkin."));
        }
        
        // checkout cancelled transaction
        $oDocumentTransaction = & new DocumentTransaction($oDocument, "Document checked out cancelled", 'ktcore.transactions.force_checkin');
        $res = $oDocumentTransaction->create();
        if (PEAR::isError($res) || ($res == false)) {
            $this->rollbackTransaction();
            return $this->errorRedirectToMain(_("Failed to force the document's checkin."));
        }
        $this->commitTransaction(); // FIXME do we want to do this if we can't created the document-transaction?
        return $this->successRedirectToMain(sprintf(_('Successfully forced "%s" to be checked in.'), $oDocument->getName()));
    }


}

// use the new admin framework.
//$d = new KTCheckoutAdminDispatcher();
//$d->dispatch();

?>
