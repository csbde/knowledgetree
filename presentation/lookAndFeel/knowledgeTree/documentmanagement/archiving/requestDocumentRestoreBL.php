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
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement.archiving
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