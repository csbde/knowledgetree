<?php
require_once("$default->fileSystemRoot/lib/archiving/DocumentArchiving.inc");
require_once("$default->fileSystemRoot/lib/archiving/ArchivingSettings.inc");
require_once("$default->fileSystemRoot/lib/archiving/TimePeriod.inc");

/**
 * $Id$
 *  
 * Business logic for setting document archive settings
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.archiving
 */

class DocumentArchiveSettingsFactory {
	
	function handleTimePeriod($iTimeUnitID, $iUnits) {
		if ($iTimeUnitID && $iUnits) {
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
		} else {
			$iTimePeriodID = -1;
		}
		return $iTimePeriodID;
	}
	
	function validateDate($dExpirationDate) {
		if ($dExpirationDate) {
			$sTime = strtotime($dExpirationDate);
			// check that its later than today	
			if ($sTime <= time()) {
				return false;
			}		
		}
		return true;
	}
		
	function create($iArchivingTypeID, $iDocumentID, $dExpirationDate, $iDocumentTransactionID, $iTimeUnitID, $iUnits) {
		global $default;
		
		$sArchivingType = lookupName($default->archiving_type_lookup_table, $iArchivingTypeID);
				
		// process time period (if any)
		$iTimePeriodID = $this->handleTimePeriod($iTimeUnitID, $iUnits);
		
		// construction strings
		switch ($sArchivingType) {
			case "Date" : 			$sSearchConstruction = "\$aArchiveSettings = ArchivingSettings::getList(\"";
									$sSearchConstruction .= (isset($dExpirationDate) ? "expiration_date='$dExpirationDate'" :  "time_period_id=$iTimePeriodID") . "\");";
									$sConstruction  = "\$oArchiveSettings = new ArchivingSettings($iArchivingTypeID, \$dExpirationDate, -1, $iTimePeriodID);";
									break;
			case "Utilisation" : 	$sSearchConstruction = "\$aArchiveSettings = ArchivingSettings::getList(\"document_transaction_id=$iDocumentTransactionID AND time_period_id=$iTimePeriodID\");";
									$sConstruction  = "\$oArchiveSettings = new ArchivingSettings($iArchivingTypeID, \"NULL\", $iDocumentTransactionID, $iTimePeriodID);";
									break;
		}
				
		// search for the settings first
		eval($sSearchConstruction);
		if (count($aArchiveSettings) > 0) {
			$iArchiveSettingsID = $aArchiveSettings[0]->getID();
			$default->log->info("looked up archive settings id=$iArchiveSettingsID");
		} else {
			// create them			
			eval($sConstruction);				
			if ($oArchiveSettings->create()) {
				$iArchiveSettingsID = $oArchiveSettings->getID();
			} else {
				$default->log->error("couldn't create archive settings- " . arrayToString($oArchiveSettings));
				return false;
			}
		}
			
		// now link to the documents
		$oDocumentArchiving = new DocumentArchiving($iDocumentID, $iArchiveSettingsID);
		if ($oDocumentArchiving->create()) {
			return true;
		} else {
			$default->log->error("couldn't create document archiving - " . arrayToString($oDocumentArchiving));
			return false;
		}
	}

	function update($oDocumentArchiving, $dExpirationDate, $iDocumentTransactionID, $iTimeUnitID, $iUnits) {
		global $default;
				
		// retrieve the settings
		$oArchiveSettings = ArchivingSettings::get($oDocumentArchiving->getArchivingSettingsID());
		if ($oArchiveSettings) {
					
			// process time period (if any)
			$iTimePeriodID = $this->handleTimePeriod($iTimeUnitID, $iUnits);
			$oArchiveSettings->setTimePeriodID($iTimePeriodID);
								
			$sArchivingType = lookupName($default->archiving_type_lookup_table, $oArchiveSettings->getArchivingTypeID());
			// update it based on the type
			switch ($sArchivingType) {
				case "Date" : 			$oArchiveSettings->setExpirationDate($dExpirationDate);										
										break;
				case "Utilisation" : 	$oArchiveSettings->setDocumentTransactionID($iDocumentTransactionID);
										break;
			}
			if ($oArchiveSettings->update()) {
				return true;
			} else {
				$default->log->error("error updating archive settings for documentID=" . $oArchiveSettings->getDocumentID());
			} 
		} else {
			$default->log->error("couldn't retrieve archive settings");
			return false;
		}
	}
}
?>