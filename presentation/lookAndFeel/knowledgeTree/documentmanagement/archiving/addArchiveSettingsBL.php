<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");

require_once("$default->fileSystemRoot/lib/archiving/DocumentArchiveSettingsFactory.inc");

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

    if ($fStore) {
    	$oDASFactory = new DocumentArchiveSettingsFactory($fArchivingTypeID);
    	
    	if ($oDASFactory->create($fDocumentID, $fExpirationDate, $fDocumentTransactionID, $fTimeUnitID, $fUnits)) {
			// created, redirect to view page
			redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID");
    	} else {
    		// error
    		$default->log->error("addArchiveSettingsBL.php error adding archive settings");
    		// display form with error
			$oContent->setHtml(renderAddArchiveSettingsPage(null, "The archive settings for this document could not be added"));   
    	}
  		    	
    } elseif (isset($fArchivingTypeID)) {
    	// the archiving type has been chosen, so display the correct form   	
		$oContent->setHtml(renderAddArchiveSettingsPage($fArchivingTypeID));    	
    } else {
		// display the select archiving type page
		$oContent->setHtml(renderAddArchiveSettingsPage(null));    	
    }    	
             
	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);	
	$main->setHasRequiredFields(true);			
	$main->render();
} 
?>