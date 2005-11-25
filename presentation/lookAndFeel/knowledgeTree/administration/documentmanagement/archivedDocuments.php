<?php

//require_once('../../../../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');

require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

// FIXME chain in a notification alert for un-archival requests.

class ArchivedDocumentsDispatcher extends KTAdminDispatcher {

   // Breadcrumbs base - added to in methods
    var $aBreadcrumbs = array(
        array('action' => 'administration', 'name' => 'Administration'),
    );

    function do_main () {
        $this->aBreadcrumbs[] = array('action' => 'archivedDocuments', 'name' => 'Archived Documents');
        
        $this->oPage->setBreadcrumbDetails('list');
        
        $aDocuments =& Document::getList("status_id=" . ARCHIVED);
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/archivedlist');
        $oTemplate->setData(array(
            'context' => $this,
            'documents' => $aDocuments,
        ));
        return $oTemplate;
    }
    
    function do_confirm_restore() {
        $this->aBreadcrumbs[] = array('action' => 'archivedDocuments', 'name' => 'Archived Documents');
        
        $selected_docs = KTUtil::arrayGet($_REQUEST, 'selected_docs', array()); 
        
        $this->oPage->setTitle('Confirm Restore of ' . count($selected_docs) . ' documents');
        
        $this->oPage->setBreadcrumbDetails('confirm restore of ' . count($selected_docs) . ' documents');
    
        $aDocuments = array();
        foreach ($selected_docs as $doc_id) {
            $oDoc =& Document::get($doc_id);
            if (PEAR::isError($oDoc) || ($oDoc === false)) { 
                $this->errorRedirectToMain('Invalid document id specified. Aborting restore.');
            } else if ($oDoc->getStatusId() != ARCHIVED) {
                $this->errorRedirectToMain($oDoc->getName() . ' is not an archived document. Aborting restore.');
            }
            $aDocuments[] = $oDoc;
        }
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/dearchiveconfirmlist');
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
                $this->errorRedirectToMain('Invalid document id specified. Aborting restore.');
            } else if ($oDoc->getStatusId() != ARCHIVED) {
                $this->errorRedirectToMain($oDoc->getName() . ' is not an archived document. Aborting restore.');
            }
            $aDocuments[] = $oDoc;
        }
     
        $this->startTransaction();
        
        foreach ($aDocuments as $oDoc) {
            // FIXME find de-archival source.
            // FIXME purge old notifications.
            // FIXME create de-archival notices to those who sent in old notifications.
            $oDoc->setStatusId(LIVE);
            $res = $oDoc->update();
            if (PEAR::isError($res) || ($res == false)) {
                $this->errorRedirectToMain($oDoc->getName . ' could not be made "live".');
            }
        }
        $this->commitTransaction();
        $msg = count($aDocuments) . ' documents made active.';
        $this->successRedirectToMain($msg);
    }
}

//$d =& new DeletedDocumentsDispatcher;
//$d->dispatch();

?>
