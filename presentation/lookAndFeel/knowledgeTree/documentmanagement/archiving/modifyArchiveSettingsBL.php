<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/ArchiveSettings.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
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
    if ($fDocumentID) {
    	$oArchiveSettings = ArchiveSettings::getFromDocumentID($fDocumentID);
	    if ($fStore) {
			// we're updating the settings- check the parameters
			if (isset($fExpirationDate) || isset($fExpirationUnits) && isset($fExpirationDatePart)) {
				// setting archiving by date			
		    	// update the object		    	
	    		if ($fExpirationDate) {
	    			$oArchiveSettings->setExpirationDate($fExpirationDate);
	    		} else if ($fExpirationUnits && $fExpirationDatePart) {
	    			$oArchiveSettings->setExpirationDate(time() + $fExpirationUnits*$oArchiveSettings->aDateUnits[$fExpirationDatePart]);
	    		}
			} else if (isset($fDocumentTransactionID) && isset($fUtilisationUnits) && isset($fUtilisationDatePart)) {
				// setting by utilisation

	    		// update the object
	    		$oArchiveSettings->setDocumentTransactionID($fDocumentTransactionID);
	    		$oArchiveSettings->setUtilisationThreshold($fUtilisationUnits*$oArchiveSettings->aDateUnits[$fUtilisationDatePart]);
		    } else {
		    	// all params not present, so display an error message
		    	$oContent->setHtml(renderEditArchiveSettingsPage($oArchiveSettings, "Please complete the form before submitting."));
		    }    		    	
	    } else {   	
			// display the edit page
			$oContent->setHtml(renderEditArchiveSettingsPage($oArchiveSettings));    	
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