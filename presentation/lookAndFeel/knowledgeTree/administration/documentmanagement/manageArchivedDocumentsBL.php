<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");
require_once("archivedDocumentsUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");

/**
 * $Id$
 *  
 * Business logic for searching archived documents
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.administration.documentmanagement
 */

if (checkSession()) {	
	global $default;
			
    // instantiate my content pattern
    $oContent = new PatternCustom();

	if (strlen($fSearchString) > 0) {
		// perform the search and display the results
		$fStartIndex = isset($fStartIndex) ? $fStartIndex : 0;		
		$oContent->setHtml(renderArchivedDocumentsResultsPage($fSearchString, $fStartIndex));
	} else if ($fDocumentIDs) {
		// got some documents to restore

		// instantiate document objects
		$aDocuments = array();
        for ($i = 0; $i < count($fDocumentIDs); $i++) {
        	$aDocuments[] = & Document::get($fDocumentIDs[$i]);
        }
        		
		if ($fConfirm) {
			// restore the specified documents
			
			$aErrorDocuments = array();
			$aSuccessDocuments = array();
	        for ($i = 0; $i < count($aDocuments); $i++) {
	        	if ($aDocuments[$i]) {
	        		// set the status to live
	        		$aDocuments[$i]->setStatusID(lookupStatusID("Live"));
	        		if ($aDocuments[$i]->update()) {
	        			// success
	        			$default->log->info("manageArchivedDocumentsBL.php set status for document id=" . $fDocumentIDs[$i]);
	        			$aSuccessDocuments[] = $aDocuments[$i];
	        		} else{
	                    // error updating status change
	                    $default->log->error("manageArchivedDocumentsBL.php couldn't retrieve document id=" . $fDocumentIDs[$i]);	                    
	                    $aErrorDocuments[] = $aDocuments[$i];                            			
	        		}
                } else {
                    // error retrieving document object
                    $default->log->error("manageArchivedDocumentsBL.php couldn't retrieve document id=" . $fDocumentIDs[$i]);
	        	}
	        }			
            // display status page.
			$oContent->setHtml(renderStatusPage($aSuccessDocuments, $aErrorDocuments));
		} else {
			// ask for confirmation before restoring the documents
			$oContent->setHtml(renderRestoreConfirmationPage($aDocuments));
		}
	} else {	
		// display the search form
		$oContent->setHtml(renderSearchPage());
	}

	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);	
	$main->setHasRequiredFields(true);			
	$main->render();
} 
?>