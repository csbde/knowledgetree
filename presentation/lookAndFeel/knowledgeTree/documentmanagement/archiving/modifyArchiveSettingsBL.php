<?php
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
require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");

require_once("$default->fileSystemRoot/lib/archiving/DocumentArchiveSettingsFactory.inc");
require_once("$default->fileSystemRoot/lib/archiving/ArchivingSettings.inc");

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");
require_once("$default->uiDirectory/documentmanagement/archiving/archiveSettingsUI.inc");

if (checkSession()) {	
	global $default;
			
    // instantiate my content pattern
    $oContent = new PatternCustom();
    
    if ($fDocumentID) {
    	// retrieve the appropriate settings given the document id
    	$oDocumentArchiving = DocumentArchiving::getFromDocumentID($fDocumentID);
		// retrieve the settings
		$oArchiveSettings = ArchivingSettings::get($oDocumentArchiving->getArchivingSettingsID());    	    	
		if ($oDocumentArchiving && $oArchiveSettings) {
		    if ($fStore) {
		    	$oDASFactory = new DocumentArchiveSettingsFactory();
		    	if ($oDASFactory->validateDate($fExpirationDate)) {
			    	if ($oDASFactory->update($oDocumentArchiving, $fExpirationDate, $fDocumentTransactionID, $fTimeUnitID, $fUnits)) {
			    		$default->log->info("modifyArchiveSettingsBL.php successfully updated archive settings (documentID=$fDocumentID)");
						// created, redirect to view page
						controllerRedirect("viewDocument", "fDocumentID=$fDocumentID&fShowSection=archiveSettings");
			    	} else {
	    				$default->log->error("modifyArchiveSettingsBL.php error updating archive settings (documentID=$fDocumentID)");		    		
			    	}
		    	} else {
		    		$oContent->setHtml(renderEditArchiveSettingsPage($fDocumentID, $oArchiveSettings, "You cannot select an expiration date in the past. Please try again."));
		    	}	    	
		    } elseif ($fDelete) {
		    	if ($oDocumentArchiving->delete()) {
		    		$default->log->info("modifyArchiveSettingsBL.php successfully deleted archive settings (documentID=$fDocumentID)");
					controllerRedirect("viewDocument", "fDocumentID=$fDocumentID&fShowSection=archiveSettings");		    		
		    	} else {
		    		$default->log->error("modifyArchiveSettingsBL.php error deleting archive settings (documentID=$fDocumentID)");
		    	}
		    } else {
				// display the edit page
				$oContent->setHtml(renderEditArchiveSettingsPage($fDocumentID, $oArchiveSettings));    	
		    }
		} else {
			// no archiving settings for this document
			$oContent->setHtml(renderEditArchiveSettingsPage(null, null, "No document has been selected."));
		}
    } else {
    	// document id missing  	
    	$oContent->setHtml(renderEditArchiveSettingsPage(null, null, "No document has been selected."));
    }
             
	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);	
	$main->setHasRequiredFields(true);			
	$main->render();
} 
?>