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

KTUtil::extractGPC('fArchivingTypeID', 'fDocumentID', 'fDocumentTransactionID', 'fExpirationDate', 'fStore', 'fTimeUnitID', 'fUnits'); 

require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");

require_once("$default->fileSystemRoot/lib/archiving/DocumentArchiveSettingsFactory.inc");

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");
require_once("$default->uiDirectory/documentmanagement/archiving/archiveSettingsUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");

if (checkSession()) {	
	global $default;
			
    // instantiate my content pattern
    $oContent = new PatternCustom();

    if ($fStore) {
    	$oDASFactory = new DocumentArchiveSettingsFactory($fArchivingTypeID);
    	if ($oDASFactory->validateDate($fExpirationDate)) {
	    	if ($oDASFactory->create($fArchivingTypeID, $fDocumentID, $fExpirationDate, $fDocumentTransactionID, $fTimeUnitID, $fUnits)) {
				// created, redirect to view page
				controllerRedirect("viewDocument", "fDocumentID=$fDocumentID&fShowSection=archiveSettings");
	    	} else {
	    		// error
	    		$default->log->error("addArchiveSettingsBL.php error adding archive settings");
	    		// display form with error
				$oContent->setHtml(renderAddArchiveSettingsPage(null, _("The archive settings for this document could not be added")));   
	    	}
    	} else {
    		$oContent->setHtml(renderAddArchiveSettingsPage($fDocumentID, $fArchivingTypeID, _("You cannot select an expiration date in the past. Please try again.")));
    	}    	
    } elseif (isset($fArchivingTypeID)) {
    	// the archiving type has been chosen, so display the correct form   	
		$oContent->setHtml(renderAddArchiveSettingsPage($fDocumentID, $fArchivingTypeID));    	
    } else {
		// display the select archiving type page
		$oContent->setHtml(renderAddArchiveSettingsPage($fDocumentID));    	
    }    	
             
	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);
	$main->setSubmitMethod("GET");
	$main->setHasRequiredFields(true);			
	$main->render();
} 
?>
