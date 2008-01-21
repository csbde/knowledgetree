<?php
/**
 * $Id$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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
