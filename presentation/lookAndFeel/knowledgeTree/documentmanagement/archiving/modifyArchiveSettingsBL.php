<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");

require_once("$default->fileSystemRoot/lib/archiving/DocumentArchiveSettingsFactory.inc");

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");
require_once("$default->uiDirectory/documentmanagement/archiving/archiveSettingsUI.inc");

/**
 * $Id$
 *  
 * Business logic for setting document archive settings
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */

if (checkSession()) {	
	global $default;
			
    // instantiate my content pattern
    $oContent = new PatternCustom();
	$default->log->info(arrayToString($_REQUEST));    
    if ($fDocumentID) {
    	// retrieve the appropriate settings given the document id
    	$oDocumentArchiving = DocumentArchiving::getFromDocumentID($fDocumentID);    	
		if ($oDocumentArchiving) {
		    if ($fStore) {
		    	$oDASFactory = new DocumentArchiveSettingsFactory($oDocumentArchiving->getArchivingTypeID());
		    	
		    	if ($oDASFactory->update($oDocumentArchiving, $fExpirationDate, $fDocumentTransactionID, $fTimeUnitID, $fUnits)) {
		    		$default->log->info("modifyArchiveSettingsBL.php successfully updated archive settings (documentID=$fDocumentID)");
					// created, redirect to view page
					redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID");
		    	} else {
    				$default->log->error("modifyArchiveSettingsBL.php error updating archive settings (documentID=$fDocumentID)");		    		
		    	}	    	
		    } elseif ($fDelete) {
		    	if ($oDocumentArchiving->delete()) {
		    		$default->log->info("modifyArchiveSettingsBL.php successfully deleted archive settings (documentID=$fDocumentID)");
					redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID");		    		
		    	} else {
		    		$default->log->error("modifyArchiveSettingsBL.php error deleting archive settings (documentID=$fDocumentID)");
		    	}
		    } else {   	
				// display the edit page
				$oContent->setHtml(renderEditArchiveSettingsPage($oDocumentArchiving));    	
		    }
		} else {
			// no archiving settings for this document
			$oContent->setHtml(renderEditArchiveSettingsPage(null, "No document has been selected."));
		}
    } else {
    	// document id missing  	
    	$oContent->setHtml(renderEditArchiveSettingsPage(null, "No document has been selected."));
    }
             
	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);	
	$main->setHasRequiredFields(true);			
	$main->render();
} 
?>