<?php
/**
 * $Id$
 *
 * Business Logic to check in a document
 *
 * Expected form variable:
 * o $fDocumentID - primary key of document user is checking out
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/email/Email.inc");
    
    require_once("$default->fileSystemRoot/lib/users/User.inc");
    
    require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentCollaboration.inc");    
    
    require_once("$default->fileSystemRoot/lib/foldermanagement/FolderCollaboration.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/FolderUserRole.inc");
    require_once("$default->fileSystemRoot/lib/roles/Role.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
    
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/checkInDocumentUI.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentUI.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewUI.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");
    
    require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");

    $oPatternCustom = & new PatternCustom();

    if (isset($fDocumentID)) {
        // instantiate the document
        $oDocument = & Document::get($fDocumentID);
        if ($oDocument) {
            // user has permission to check the document in
            if (Permission::userHasDocumentWritePermission($fDocumentID)) {
                // and the document is checked out
                if ($oDocument->getIsCheckedOut()) {
                    // by you
                    if ($oDocument->getCheckedOutUserID() == $_SESSION["userID"]) {
                        // if we're ready to perform the updates
                        if ($fForStore) {
                            // make sure the user actually selected a file first
                            if (strlen($_FILES['fFile']['name']) > 0) {
                            	// and that the filename matches
                            	$default->log->info("checkInDocumentBL.php uploaded filename=" . $_FILES['fFile']['name'] . "; current filename=" . $oDocument->getFileName());
    							if ($oDocument->getFileName() == $_FILES['fFile']['name']) {
	                                // save the original document
	                                $sBackupPath = $oDocument->getPath() . "-" . $oDocument->getMajorVersionNumber() . "." . $oDocument->getMinorVersionNumber();
	                                copy($oDocument->getPath(), $sBackupPath);
	                                
	                                // update the document with the uploaded one
	                                if (PhysicalDocumentManager::uploadPhysicalDocument($oDocument, $fFolderID, "", $_FILES['fFile']['tmp_name'])) {
	                                    // now update the database                                
	                                    // overwrite size
	                                    $oDocument->setFileSize($_FILES['fFile']['size']);
	                                    // update modified date
	                                    $oDocument->setLastModifiedDate(getCurrentDateTime());
	                                    // flip the check out status
	                                    $oDocument->setIsCheckedOut(false);
	                                    // clear the checked in user id
	                                    $oDocument->setCheckedOutUserID(-1);
	                                    // bump the version numbers
	                                    if ($fCheckInType == "major") {
	                                        // major version number rollover
	                                        $oDocument->setMajorVersionNumber($oDocument->getMajorVersionNumber()+1);
	                                        // reset minor version number
	                                        $oDocument->setMinorVersionNumber(0);
	                                    } else if ($fCheckInType == "minor") {
	                                        $oDocument->setMinorVersionNumber($oDocument->getMinorVersionNumber()+1);
	                                    }
	    
	                                    // update it
	                                    if ($oDocument->update()) {
	    
	                                        // create the document transaction record
	                                        $oDocumentTransaction = & new DocumentTransaction($oDocument->getID(), $fCheckInComment, CHECKIN);
	                                        // TODO: check transaction creation status?
	                                        $oDocumentTransaction->create();
	                                        
	                                        // fire subscription alerts for the checked in document
	                                        $count = SubscriptionEngine::fireSubscription($fDocumentID, SubscriptionConstants::subscriptionAlertType("CheckInDocument"),
	                                                 SubscriptionConstants::subscriptionType("DocumentSubscription"),
	                                                 array( "folderID" => $oDocument->getFolderID(),
	                                                        "modifiedDocumentName" => $oDocument->getName() ));
	                                        $default->log->info("checkInDocumentBL.php fired $count subscription alerts for checked out document " . $oDocument->getName());
	    
	                                        //redirect to the document view page
	                                        redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID());
	                                    } else {
	                                        // document update failed
	                                        $oPatternCustom->setHtml(renderErrorPage("An error occurred while storing this document in the database"));
	                                    }
	                                } else {
	                                    // reinstate the backup
	                                    copy($sBackupPath, $oDocument->getPath());
	                                    // remove the backup
	                                    unlink($sBackupPath);                                    
	                                    $oPatternCustom->setHtml(renderErrorPage("An error occurred while storing the new file on the filesystem"));
	                                }
    							} else {
	                                $sErrorMessage = "The file you selected does not match the current filename in the DMS.  Please try again.";
	                                $oPatternCustom->setHtml(getCheckInPage($oDocument));
    							}
                            } else {
                                $sErrorMessage = "Please select a document by first clicking on 'Browse'.  Then click 'Check-In'";
                                $oPatternCustom->setHtml(getCheckInPage($oDocument));
                            }
                        } else {
                            // prompt the user for a check in comment and the file
                            $oPatternCustom->setHtml(getCheckInPage($oDocument));
                        }
                    } else {
                        // you don't have this doc checked out
                        $oUser = User::get($oDocument->getCheckedOutUserID()); 
                        $oPatternCustom->setHtml(renderErrorPage("You can't check in this document because its checked out by " . $oUser->getName()));
                    }
                } else {
                    // this document isn't checked out
                    $oPatternCustom->setHtml(renderErrorPage("You can't check in this document because its not checked out"));
                }
            } else {
                // no permission to checkout the document
                $oPatternCustom->setHtml(renderErrorPage("You do not have permission to check in this document"));
            }
        } else {
            // couldn't instantiate the document
            $oPatternCustom->setHtml(renderErrorPage("Could not check in this document"));
        }
    } else {
        // no document id was set when coming to this page,
        $oPatternCustom->setHtml(renderErrorPage("No document is currently selected for check in"));
    }

    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $main->setCentralPayload($oPatternCustom);
    $main->setFormAction($_SERVER["PHP_SELF"]);
    $main->setFormEncType("multipart/form-data");
    if (isset($sErrorMessage)) {
        $main->setErrorMessage($sErrorMessage);
    }
    $main->render();
}
?>
