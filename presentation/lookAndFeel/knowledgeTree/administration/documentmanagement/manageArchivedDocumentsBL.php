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
 * @package administration.documentmanagement
 */

if (checkSession()) {	
	global $default;
			
    // instantiate my content pattern
    $oContent = new PatternCustom();

	if (strlen($fSearchString) > 0) {
		// perform the search and display the results
		$sMetaTagIDs = getChosenMetaDataTags($_GET);				
		if (strlen($sMetaTagIDs) > 0) {
			$sSQLSearchString = getSQLSearchString($fSearchString);
			$aDocuments = searchForDocuments($sMetaTagIDs, $sSQLSearchString, "Archived");
			if (count($aDocuments) > 0) {
				// display the documents
				$oContent->setHtml(renderArchivedDocumentsResultsPage($aDocuments));
			} else {
				$oContent->setHtml(getSearchPage($fSearchString, explode(",",$sMetaTagIDs), _("Archived Documents Search"), true));				
				$sErrorMessage = _("No documents matched your search criteria");                              
			}				
		} else {
			$oContent->setHtml(getSearchPage($fSearchString, array(), _("Archived Documents Search"), true));
			$sErrorMessage = _("Please select at least one criteria to search by");
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
	        		$aDocuments[$i]->setStatusID(LIVE);
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
							if ($oEmail->send($oRequestUser->getEmail(), _("Archived Document Restored"), $sBody)) {
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
		$oContent->setHtml(getSearchPage("", array(), _("Archived Documents Search"), true));
	}

	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	if (isset($sErrorMessage)) {
		$main->setErrorMessage($sErrorMessage);
	}    
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);	
	$main->setHasRequiredFields(true);
	$main->setSubmitMethod("GET");
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
				"FROM $default->documents_table AS D INNER JOIN document_fields_link AS DFL ON DFL.document_id = D.id " .
				"INNER JOIN $default->document_fields_table AS DF ON DF.id = DFL.document_field_id " .
				"INNER JOIN $default->search_permissions_table AS SDUL ON SDUL.document_id = D.ID " .
				"INNER JOIN $default->status_table AS SL on D.status_id=SL.id " .			
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
