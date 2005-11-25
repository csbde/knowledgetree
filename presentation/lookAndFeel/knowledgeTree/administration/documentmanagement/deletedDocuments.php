<?php

//require_once('../../../../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');

require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

class DeletedDocumentsDispatcher extends KTAdminDispatcher {

   // Breadcrumbs base - added to in methods
    var $aBreadcrumbs = array(
        array('action' => 'administration', 'name' => 'Administration'),
    );

    function do_main () {
        $this->aBreadcrumbs[] = array('action' => 'deletedDocuments', 'name' => 'Deleted Documents');
        
        $this->oPage->setBreadcrumbDetails('view');
    
        $aDocuments =& Document::getList("status_id=" . DELETED);
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/deletedlist');
        $oTemplate->setData(array(
            'context' => $this,
            'documents' => $aDocuments,
        ));
        return $oTemplate;
    }
    
    function do_confirm_expunge() {
        $this->aBreadcrumbs[] = array('action' => 'deletedDocuments', 'name' => 'Deleted Documents');
        
        $selected_docs = KTUtil::arrayGet($_REQUEST, 'selected_docs', array()); 
        
        $this->oPage->setTitle('Confirm Expunge of ' . count($selected_docs) . ' documents');
        
        $this->oPage->setBreadcrumbDetails('confirm expunge of ' . count($selected_docs) . ' documents');
    
        $aDocuments = array();
        foreach ($selected_docs as $doc_id) {
            $oDoc =& Document::get($doc_id);
            if (PEAR::isError($oDoc) || ($oDoc === false)) { 
                $this->errorRedirectToMain('Invalid document id specified. Aborting expunge');
            } else if ($oDoc->getStatusId() != DELETED) {
                $this->errorRedirectToMain($oDoc->getName() . ' is not a deleted document. Aborting expunge');
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
                $this->errorRedirectToMain('Invalid document id specified. Aborting expunge');
            } else if ($oDoc->getStatusId() != DELETED) {
                $this->errorRedirectToMain($oDoc->getName() . ' is not a deleted document. Aborting expunge');
            }
            $aDocuments[] = $oDoc;
        }
     
        $this->startTransaction();
        $aErrorDocuments = array();
        $aSuccessDocuments = array();			

        foreach ($aDocuments as $oDoc) {
            if (!PhysicalDocumentManager::expunge($oDoc)) { $aErrorDocuments[] = $oDoc->getDisplayPath(); }
            else {
                $oDocumentTransaction = & new DocumentTransaction($oDoc->getId(), "Document expunged", EXPUNGE);
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
        $msg = count($aSuccessDocuments) . ' documents expunged.';
        if (count($aErrorDocuments) != 0) { $msg .= 'Failed to expunge: ' . join(', ', $aErrorDocuments); }
        $this->successRedirectToMain($msg);
    }
}

//$d =& new DeletedDocumentsDispatcher;
//$d->dispatch();

?>
