<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/archiving/ArchiveRestorationRequest.inc");
require_once("$default->fileSystemRoot/lib/email/Email.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("restoreArchivedDocumentUI.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
/**
 * $Id$
 *
 * Business logic for requesting the restoration of an archived document.
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
	
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	
    // instantiate my content pattern
    $oContent = new PatternCustom();   	    

    if ($fDocumentID) {
    	// instantiate the document
    	$oDocument = Document::get($fDocumentID); 
    	if ($oDocument) {

		    // lookup the unit admin
		    $oUnitAdminUser = User::getUnitAdminUser(); 
    		
    		// FIXME: what if i can't find a unit administrator??
    		// presumably find a system administrator then?
    		
	    	// create the request
	    	$oRestoreRequest = new ArchiveRestorationRequest($fDocumentID, $_SESSION["userID"], $oUnitAdminUser->getID());
	    	if ($oRestoreRequest->create()) {
	    		// FIXME: refactor notification
		  		// send the email requesting the restoration of an archived document
		  		$oUser = User::get($_SESSION["userID"]);
		  		
				$sBody = $oUnitAdmin->getName() . ",<br><br> The user " . $oUser->getName() . " has requested that document ";
				$sBody .= "'" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID(), $oDocument->getName()) . "'"; 
				$sBody .= " be restored from the archive.";								
				$oEmail = & new Email();
				$oEmail->send($oUnitAdmin->getEmail(), "Archived Document Restoration Request", $sBody);
		  			  		
		  		// display a confirmation message
		  		$oContent->setHtml(renderRequestSuccessPage($oDocument));	    		
	    	} else {
	    		// error creating the request
	    		$oContent->setHtml(renderRequestFailurePage($oDocument));
	    	}
    	} else {
    		// error retrieving document
    		$default->log->error("requestDocumentRestoreBL.php there was an error retrieving document id=$fDocumentID from the db");
    		// TODO: generic error page
    	}
  		
    } else {
		// display the select archiving type page
		$oContent->setHtml(renderAddArchiveSettingsPage(null));    	
    }    	
             
	// build the page   
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);	
	$main->setHasRequiredFields(true);			
	$main->render();
} 
?>