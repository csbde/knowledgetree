<?php
/**
 * $Id$
 *
 * Business logic for setting document archive settings.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement.archiving
 */

require_once("../../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fDelete', 'fDocumentID', 'fDocumentTransactionID', 'fExpirationDate', 'fStore', 'fTimeUnitID', 'fUnits');

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
		    		$oContent->setHtml(renderEditArchiveSettingsPage($fDocumentID, $oArchiveSettings, _("You cannot select an expiration date in the past. Please try again.")));
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
			$oContent->setHtml(renderEditArchiveSettingsPage(null, null, _("No document has been selected.")));
		}
    } else {
    	// document id missing  	
    	$oContent->setHtml(renderEditArchiveSettingsPage(null, null, _("No document has been selected.")));
    }
             
	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);	
	$main->setHasRequiredFields(true);			
	$main->render();
} 
?>
