<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");

require_once("$default->fileSystemRoot/lib/archiving/DocumentArchiving.inc");
require_once("$default->fileSystemRoot/lib/archiving/ArchivingUtilisationSettings.inc");
require_once("$default->fileSystemRoot/lib/archiving/ArchivingDateSettings.inc");
require_once("$default->fileSystemRoot/lib/archiving/TimePeriod.inc");

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

class DocumentArchiveSettingsFactory {
	function create($iDocumentID, $iArchivingTypeID, $dExpirationDate, $iDocumentTransactionID, $iTimeUnitID, $iUnits) {
		global $default;		
		$sArchivingType = lookupName($default->owl_archiving_type_lookup_table, $iArchivingTypeID);
				
		// search for an existing time period id		
		$aTimePeriod = TimePeriod::getList("time_unit_id=$iTimeUnitID AND units=$iUnits");
		if (count($aTimePeriod) > 0) {
			$iTimePeriodID = $aTimePeriod[0]->getID();
		} else {
			// create it
			$oTimePeriod = new TimePeriod($iTimeUnitID, $iUnits);
			if ($oTimePeriod->create()) {
				$iTimePeriodID = $oTimePeriod->getID();
			} else {
				$default->log->error("couldn't create time period- " . arrayToString($oTimePeriod));
				return false;
			}
		}
		
		// construction strings
		switch ($sArchivingType) {
			case "Date" : 			$sSearchConstruction = "\$aArchiveSettings = ArchivingDateSettings::getList(\"expiration_date='$dExpirationDate' AND time_period_id=$iTimePeriodID\");";
									$sConstruction  = "\$oArchiveSettings = new ArchivingDateSettings($dExpirationDate, $iTimePeriodID);";
									break;
			case "Utilisation" : 	$sSearchConstruction = "\$aArchiveSettings = ArchivingUtilisationSettings::getList(\"document_transaction_id=$iDocumentTransactionID AND time_period_id=$iTimePeriodID\");";
									$sConstruction  = "\$oArchiveSettings = new ArchivingUtilisationSettings($iDocumentTransactionID, $iTimePeriodID);";
									break;
		}
		
		// search for the settings first
		eval($sSearchConstruction);
		if (count($aArchiveSettings) > 0) {
			$iArchiveSettingsID = $aArchiveSettings[0]->getID();
		} else {
			// create them			
			eval($sConstruction);				
			if ($oArchiveSettings->create()) {
				$iArchiveSettingsID = $oArchiveSettings->getID();
			} else {
				$default->log->error("couldn't create archive settings- " . arrayToString($oArchiveSettings));
				return false;
			}
			
			// now link to the documents
			$oDocumentArchiving = new DocumentArchiving($iDocumentID, $iArchivingTypeID, $iArchiveSettingsID);
			if ($oDocumentArchiving->create()) {
				return true;
			} else {
				$default->log->error("couldn't create document archiving - " . arrayToString($oDocumentArchiving));
				return false;
			}				
		}
	}
}

if (checkSession()) {	
	global $default;
			
    // instantiate my content pattern
    $oContent = new PatternCustom();
	$default->log->info(arrayToString($_REQUEST));
    if ($fStore) {
    	if (DocumentArchiveSettingsFactory::create($fDocumentID, $fArchivingTypeID, $fExpirationDate, $fDocumentTransactionID, $fTimeUnitID, $fUnits)) {
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
		// display the edit/add page
		$oContent->setHtml(renderAddArchiveSettingsPage($fArchivingTypeID));    	
    } else {
		// display the choose archiving type page
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