<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");

require_once("$default->fileSystemRoot/lib/archiving/DocumentArchiveSettingsFactory.inc");

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");
require_once("$default->uiDirectory/foldermanagement/folderUI.inc");
require_once("$default->uiDirectory/documentmanagement/archiving/archiveSettingsUI.inc");

/**
 * $Id$
 *  
 * Business logic for archiving a document.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement.archiving
 */

if (checkSession()) {	
	global $default;
			
    // instantiate my content pattern
    $oContent = new PatternCustom();

	if ($fDocumentID) {
	    if ($fConfirmed) {
	    	$oDocument = Document::get($fDocumentID);
	    	if ($oDocument) {
	    		// change the document status to archived
		    	$oDocument->setStatusID(lookupStatusID("Archived"));
		    	
		    	if ($oDocument->update()) {
					// redirect to folder browse
					$default->log->info("archiveDocumentBL.php successfully archived document id $fDocumentID");
					redirect("$default->rootUrl/control.php?action=browse&fBrowseType=folder&fFolderID=" . $oDocument->getFolderID());
		    	} else {
		    		// error
		    		$default->log->error("archiveDocumentBL.php error archiving document id $fDocumentID");
		    		// display form with error
					$oContent->setHtml(renderArchiveDocumentPage(null, "The archive settings for this document could not be updated"));   
		    	}
	    	} else {
	    		$default->log->error("archiveDocumentBL.php couldn't retrieve document id $fDocumentID from the db");
	    		// display page with error?
	    	}
	    } else {	    	
	    	// display the confirmation form   	
			$oContent->setHtml(renderArchiveConfirmationPage($fDocumentID));
	    }    	
    } else {
		// error- no document reference
		$oContent->setHtml(renderArchiveConfirmationPage(null, "No document supplied for archiving"));    	
    }    	
             
	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);	
	$main->setHasRequiredFields(true);			
	$main->render();
} 
?>