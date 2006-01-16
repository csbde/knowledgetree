<?php

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');

require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

require_once(KT_LIB_DIR . "/documentmanagement/PhysicalDocumentManager.inc");

// FIXME chain in a notification alert for un-archival requests.

class ArchivedDocumentsDispatcher extends KTAdminDispatcher {

    function do_main () {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Archived Documents'));
        
        $this->oPage->setBreadcrumbDetails(_('list'));
        
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
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Archived Documents'));
        
        $selected_docs = KTUtil::arrayGet($_REQUEST, 'selected_docs', array()); 
        
        $this->oPage->setTitle(sprintf(_('Confirm Restore of %d documents'), count($selected_docs)));
        
        $this->oPage->setBreadcrumbDetails(sprintf(_('confirm restore of %d documents'), count($selected_docs)));
    
        $aDocuments = array();
        foreach ($selected_docs as $doc_id) {
            $oDoc =& Document::get($doc_id);
            if (PEAR::isError($oDoc) || ($oDoc === false)) { 
                $this->errorRedirectToMain(_('Invalid document id specified. Aborting restore.'));
            } else if ($oDoc->getStatusId() != ARCHIVED) {
                $this->errorRedirectToMain(sprintf(_('%s is not an archived document. Aborting restore.'), $oDoc->getName()));
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
                $this->errorRedirectToMain(_('Invalid document id specified. Aborting restore.'));
            } else if ($oDoc->getStatusId() != ARCHIVED) {
                $this->errorRedirectToMain(sprintf(_('%s is not an archived document. Aborting restore.'), $oDoc->getName()));
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
                $this->errorRedirectToMain(sprintf(_('%s could not be made "live".'), $oDoc->getName));
            }
        }
        $this->commitTransaction();
        $msg = sprintf(_('%d documents made active.'), count($aDocuments));
        $this->successRedirectToMain($msg);
    }
}

?>
