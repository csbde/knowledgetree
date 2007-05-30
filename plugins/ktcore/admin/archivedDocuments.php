<?php
/**
 * $Id$
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
 *
 */

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
    var $sHelpPage = 'ktcore/admin/archived documents.html';
    function do_main () {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Archived Documents'));
        
        $this->oPage->setBreadcrumbDetails(_kt('browse'));
            
        $oFolder = Folder::get(KTUtil::arrayGet($_REQUEST, 'fFolderId', 1));
        if (PEAR::isError($oFolder)) { 
            $this->errorRedirectToMain(_kt('Invalid folder selected.'));
            exit(0);
        }
        
        // Setup the collection for move display.
        
        $aBaseParams = array();

        $collection = new AdvancedCollection();

        $oCR =& KTColumnRegistry::getSingleton();

        // selection col
        $col = $oCR->getColumn('ktcore.columns.selection');
        $col->setOptions(array('show_folders' => false, 'rangename' => '_d[]'));
        $collection->addColumn($col);
        
        // title col
        $col = $oCR->getColumn('ktcore.columns.title');
        $col->setOptions(array('link_documents' => false));
        
        $collection->addColumn($col);

        $qObj = new ArchivedBrowseQuery($oFolder->getId());
        $collection->setQueryObject($qObj);

        $aOptions = $collection->getEnvironOptions();
        $aOptions['result_url'] = KTUtil::addQueryString($_SERVER['PHP_SELF'], 
                                                         array(kt_array_merge($aBaseParams, array('fFolderId' => $oFolder->getId()))));

        $collection->setOptions($aOptions);

	$oWF =& KTWidgetFactory::getSingleton();
	$oWidget = $oWF->get('ktcore.widgets.collection', 
			     array('label' => _kt('Target Documents'),
				   'description' => _kt('Use the folder collection and path below to browse to the folder containing the documents you wish to restore.'),
				   'required' => true,
				   'name' => 'browse',
                                   'folder_id' => $oFolder->getId(),
                                   'bcurl_params' => $aBaseParams,
				   'collection' => $collection));


        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/document/admin/archivebrowse");
        $aTemplateData = array(
              "context" => $this,
              'folder' => $oFolder,
              'breadcrumbs' => $aBreadcrumbs,
              'collection' => $oWidget,
        );
        
        return $oTemplate->render($aTemplateData);                  
    }
    
    /*
     * Provide for "archived" browsing.
     */
    function do_browse() {
    
    }
    
    function do_confirm_restore() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Archived Documents'));
        
        $selected_docs = KTUtil::arrayGet($_REQUEST, '_d', array()); 
        
        $this->oPage->setTitle(sprintf(_kt('Confirm Restore of %d documents'), count($selected_docs)));
        
        $this->oPage->setBreadcrumbDetails(sprintf(_kt('confirm restore of %d documents'), count($selected_docs)));
    
        $aDocuments = array();
        foreach ($selected_docs as $doc_id) {
            $oDoc =& Document::get($doc_id);
            if (PEAR::isError($oDoc) || ($oDoc === false)) { 
                $this->errorRedirectToMain(_kt('Invalid document id specified. Aborting restore.'));
            } else if ($oDoc->getStatusId() != ARCHIVED) {
                $this->errorRedirectToMain(sprintf(_kt('%s is not an archived document. Aborting restore.'), $oDoc->getName()));
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
                $this->errorRedirectToMain(_kt('Invalid document id specified. Aborting restore.'));
            } else if ($oDoc->getStatusId() != ARCHIVED) {
                $this->errorRedirectToMain(sprintf(_kt('%s is not an archived document. Aborting restore.'), $oDoc->getName()));
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
                $this->errorRedirectToMain(sprintf(_kt('%s could not be made "live".'), $oDoc->getName));
            }
        }
        $this->commitTransaction();
        $msg = sprintf(_kt('%d documents made active.'), count($aDocuments));
        $this->successRedirectToMain($msg);
    }
}

?>
