<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/archiving/ArchiveRestorationRequest.inc");
require_once("$default->fileSystemRoot/lib/email/Email.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");
require_once("$default->uiDirectory/search/advancedSearchUI.inc");
require_once("$default->uiDirectory/search/advancedSearchUtil.inc");
require_once("archivedDocumentsUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");

/**
 * $Id$
 *  
 * Business logic for searching archived documents
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.administration.documentmanagement
 */

if (checkSession()) {	
	global $default;
			
    // instantiate my content pattern
    $oContent = new PatternCustom();

	if (strlen($fSearchString) > 0) {
		// perform the search and display the results
		$sMetaTagIDs = getChosenMetaDataTags();				
		if (strlen($sMetaTagIDs) > 0) {
			$sSQLSearchString = getSQLSearchString($fSearchString);
			$aDocuments = searchForDocuments($sMetaTagIDs, $sSQLSearchString, "Archived");
			if (count($aDocuments) > 0) {
				// display the documents
				$oContent->setHtml(renderArchivedDocumentsResultsPage($aDocuments));
			} else {
				$oContent->setHtml(getSearchPage($fSearchString, explode(",",$sMetaTagIDs), "Archived Documents Search", true));				
				$sErrorMessage = "No documents matched your search criteria";                              
			}				
		} else {
			$oContent->setHtml(getSearchPage($fSearchString, array(), "Archived Documents Search", true));
			$sErrorMessage = "Please select at least one criteria to search by";
		}
	} else if ($fDocumentIDs) {
		// got some documents to restore

		// instantiate document objects
		$aDocuments = array();
        for ($i = 0; $i < count($fDocumentIDs); $i++) {
        	$aDocuments[] = & Document::get($fDocumentIDs[$i]);
        }
        		
		if ($fConfirm) {
			// restore the specified documents
			
			$aErrorDocuments = array();
			$aSuccessDocuments = array();
	        for ($i = 0; $i < count($aDocuments); $i++) {
	        	if ($aDocuments[$i]) {        		
	        		
	        		// set the status to live
	        		$aDocuments[$i]->setStatusID(lookupStatusID("Live"));
	        		if ($aDocuments[$i]->update()) {
	        			// success
	        			$default->log->info("manageArchivedDocumentsBL.php set status for document id=" . $fDocumentIDs[$i]);
	        			$aSuccessDocuments[] = $aDocuments[$i];
	        			
		        		// check if there are requests for this document to be archived
		        		$aRequests = ArchiveRestorationRequest::getList("document_id=" . $aDocuments[$i]->getID());
		        		$default->log->info("manageArchivedDocumentsBL.php about to send notification for " . count($aRequests) . " restoration requests for document id " . $aDocuments[$i]->getID());
		        		for ($j=0; $j<count($aRequests); $j++) {
			        		// email the users
			        		// FIXME: refactor notification
			        		// TODO: check email notification and valid email address
		        			$oRequestUser = User::get($aRequests[$j]->getRequestUserID());
							$sBody = "The document '" . generateControllerLink("viewDocument", "fDocumentID=" . $aDocuments[$i]->getID(), $aDocuments[$i]->getName()) . "'"; 
							$sBody .= " has been restored from the archive.";								
							$oEmail = & new Email();
							if ($oEmail->send($oRequestUser->getEmail(), "Archived Document Restored", $sBody)) {
		        				$default->log->info("manageArchivedDocumentsBL.php sent email to " . $oRequestUser->getEmail());
		        				// now delete the request
		        				$iRequestID = $aRequests[$j]->getID();
		        				if ($aRequests[$j]->delete()) {
		        					$default->log->info("manageArchivedDocumentsBL.php removing restoration request $iRequestID");
		        				} else {
		        					$default->log->error("manageArchivedDocumentsBL.php error removing request $iRequestID");
		        				}
							} else {
								$default->log->error("manageArchivedDocumentsBL.php error notifying " . arrayToString($oEmail) . " for document id " . $aDocuments[$i]->getID() . " restoration");
							}								
		        		}
	        		} else{
	                    // error updating status change
	                    $default->log->error("manageArchivedDocumentsBL.php couldn't retrieve document id=" . $fDocumentIDs[$i]);	                    
	                    $aErrorDocuments[] = $aDocuments[$i];                            			
	        		}
                } else {
                    // error retrieving document object
                    $default->log->error("manageArchivedDocumentsBL.php couldn't retrieve document id=" . $fDocumentIDs[$i]);
	        	}
	        }			
            // display status page.
			$oContent->setHtml(renderStatusPage($aSuccessDocuments, $aErrorDocuments));
		} else {
			// ask for confirmation before restoring the documents
			$oContent->setHtml(renderRestoreConfirmationPage($aDocuments));
		}
	} else {	
		// display the advanced search form, but specify that only archived documents must be returned
		$oContent->setHtml(getSearchPage("", array(), "Archived Documents Search", true));
	}

	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	if (isset($sErrorMessage)) {
		$main->setErrorMessage($sErrorMessage);
	}    
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);	
	$main->setHasRequiredFields(true);			
	$main->render();
}


/**
* Generate a string consisting of all documents that match the search criteria
* and that the user is allowed to see
*/
function searchForDocuments($sMetaTagIDs, $sSQLSearchString, $sStatus = "Live") {	
	global $default;
	$aDocuments = array();
	$sQuery = "SELECT DISTINCT D.id " .
				"FROM documents AS D INNER JOIN document_fields_link AS DFL ON DFL.document_id = D.id " .
				"INNER JOIN document_fields AS DF ON DF.id = DFL.document_field_id " .
				"INNER JOIN search_document_user_link AS SDUL ON SDUL.document_id = D.ID " .
				"INNER JOIN status_lookup AS SL on D.status_id=SL.id " .			
				"WHERE DF.ID IN ($sMetaTagIDs) " .
				"AND (" . $sSQLSearchString . ") " .
				"AND SL.name='$sStatus' " .
				"AND SDUL.user_id = " . $_SESSION["userID"];
				$default->log->info("searchForDocuments $sQuery");
	$sql = $default->db;
	$sql->query($sQuery);
	while ($sql->next_record()) {
		$aDocuments[] = & Document::get($sql->f("id"));
	}

	return $aDocuments;
}
?>