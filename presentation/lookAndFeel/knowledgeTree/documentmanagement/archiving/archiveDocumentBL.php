<?php

require_once("../../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fConfirmed', 'fDocumentID');

require_once("$default->fileSystemRoot/lib/archiving/DocumentArchiveSettingsFactory.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");
require_once("$default->uiDirectory/foldermanagement/folderUI.inc");
require_once("$default->uiDirectory/documentmanagement/archiving/archiveSettingsUI.inc");
/**
 * $Id$
 *
 * Business logic for archiving a document.
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

if (checkSession()) {	
	global $default;
			
    // instantiate my content pattern
    $oContent = new PatternCustom();

	if ($fDocumentID) {
	    if ($fConfirmed) {
	    	$oDocument = Document::get($fDocumentID);
	    	if ($oDocument) {
	    		// change the document status to archived
		    	$oDocument->setStatusID(ARCHIVED);
		    	
		    	if ($oDocument->update()) {
					$default->log->info("archiveDocumentBL.php successfully archived document id $fDocumentID");
							    		
                    // fire subscription alerts for the archived document
                    $count = SubscriptionEngine::fireSubscription($fDocumentID, SubscriptionConstants::subscriptionAlertType("ArchivedDocument"),
                             									  SubscriptionConstants::subscriptionType("DocumentSubscription"),
                             									  array( "folderID" => $oDocument->getFolderID(),
                             									         "modifiedDocumentName" => $oDocument->getName()));
                    $default->log->info("archiveDocumentBL.php fired $count subscription alerts for archived document " . $oDocument->getName());
		    		
					// redirect to folder browse
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
		$oContent->setHtml(renderArchiveConfirmationPage(null, _("No document supplied for archiving")));
    }    	
             
	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);	
	$main->setHasRequiredFields(true);			
	$main->render();
} 
?>
