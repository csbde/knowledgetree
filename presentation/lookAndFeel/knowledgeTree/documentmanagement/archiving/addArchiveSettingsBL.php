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
	$default->log->info(arrayToString($_POST));
    if ($fStore) {
		// setting archiving by date	
		if (isset($fExpirationDate) || isset($fExpirationUnits) && isset($fExpirationDatePart)) {	    	
			if ($fExpirationDate) {
				$dExpirationDate = $fExpirationDate;
			} else if ($fExpirationUnits && $fExpirationDatePart) {
				$dExpirationDate = date() + $fExpirationUnits*$fExpirationDatePart;
			}
			$oArchiveSettings = new ArchiveSettings($fDocumentID, $fExpirationDate, 0, 0);
			if ($oArchiveSettings->create()) {
				// created, redirect to view page
			} else {
				$default->log->error("addArchiveSettingsBL.php error adding archive settings:" . arrayToString($oArchiveSettings));
			}			
		// setting by utilisation
		} else if (isset($fDocumentTransactionID) && isset($fUtilisationUnits) && isset($fUtilisationDatePart)) {
			$iUtilisationThreshold = $fUtilisationUnits*$oArchiveSettings->aDateUnits[$fUtilisationDatePart];
			$oArchiveSettings = new ArchiveSettings($fDocumentID, "", $iUtilisationThreshold, $fDocumentTransactionID);
			if ($oArchiveSettings->create()) {
				// created, redirect to view page
				redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID");
			} else {
				$default->log->error("addArchiveSettingsBL.php error adding archive settings:" . arrayToString($oArchiveSettings));
			}			
	    } else {
	    	// all params not present, so display an error message
	    	$oContent->setHtml(renderAddArchiveSettingsPage(null, "Please complete the form before submitting."));
	    }    		    	
    } else {   	
		// display the edit/add page
		$oContent->setHtml(renderEditArchiveSettingsPage(null));    	
    }
             
	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);	
	$main->setHasRequiredFields(true);			
	$main->render();
} 
?>