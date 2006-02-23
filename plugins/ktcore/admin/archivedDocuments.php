<?php

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');

require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");
require_once(KT_LIB_DIR . "/browse/browseutil.inc.php");

require_once(KT_LIB_DIR . "/documentmanagement/PhysicalDocumentManager.inc");

// FIXME chain in a notification alert for un-archival requests.
class KTArchiveTitle extends TitleColumn {
    
    function renderDocumentLink($aDataRow) {
        $outStr .= $aDataRow["document"]->getName();
        return $outStr;
    }    
       
    function buildFolderLink($aDataRow) {
        return KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf('fFolderId=%d', $aDataRow["folder"]->getId()));
    }
}

class ArchivedDocumentsDispatcher extends KTAdminDispatcher {

    function do_main () {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Archived Documents'));
        
        $this->oPage->setBreadcrumbDetails(_('browse'));
            
        $oFolder = Folder::get(KTUtil::arrayGet($_REQUEST, 'fFolderId', 1));
        if (PEAR::isError($oFolder)) { 
            $this->errorRedirectToMain(_('Invalid folder selected.'));
            exit(0);
        }
        
        // Setup the collection for move display.
        
        $collection = new DocumentCollection();
        
        $collection->addColumn(new SelectionColumn("Select","selected_docs[]", false, true));
        $collection->addColumn(new KTArchiveTitle("Archive Documents","title"));        
        
        $qObj = new ArchivedBrowseQuery($oFolder->getId());
        $collection->setQueryObject($qObj);

        $batchPage = (int) KTUtil::arrayGet($_REQUEST, "page", 0);
        $batchSize = 20;

        $resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("fFolderId=%d&action=browse", $sMoveCode, $oFolder->getId()));
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
            $url = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("fFolderId=%d", $sMoveCode, $id));
            $aBreadcrumbs[] = array("url" => $url, "name" => $folder_path_names[$index]);
        }
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/document/admin/archivebrowse");
        $aTemplateData = array(
              "context" => $this,
              'folder' => $oFolder,
              'breadcrumbs' => $aBreadcrumbs,
              'collection' => $collection,
              'collection_breadcrumbs' => $aBreadcrumbs,
        );
        
        return $oTemplate->render($aTemplateData);                  
    }
    
    /*
     * Provide for "archived" browsing.
     */
    function do_browse() {
    
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
