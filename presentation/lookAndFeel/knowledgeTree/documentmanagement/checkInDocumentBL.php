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
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
    require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");    
    require_once("documentUI.inc");

    $oPatternCustom = & new PatternCustom();

    if (isset($fDocumentID)) {
        // instantiate the document
        $oDocument = & Document::get($fDocumentID);
        if ($oDocument) {
            // user has permission to check the document in
            if (Permission::userHasDocumentWritePermission($fDocumentID)) {
                // and the document is checked out
                if ($oDocument->getIsCheckedOut()) {
                    // if we're ready to perform the updates
                    if ($fForStore) {
                        // make sure the user actually selected a file first
                        if (strlen($_FILES['fFile']['name']) > 0) {

                            // backup the original document
                            $sBackupPath = $oDocument->getPath() . ".bk";
                            copy($oDocument->getPath(), $sBackupPath);
                            
                            // update the document with the uploaded one
                            if (PhysicalDocumentManager::uploadPhysicalDocument($oDocument, $fFolderID, "", $_FILES['fFile']['tmp_name'])) {
                                // remove the backup
                                unlink($sBackupPath);
                                
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
                                $default->log->error("fCheckInType=$fCheckInType");
                                if ($fCheckInType == "major") {
                                    // major version number rollover
                                    $oDocument->setMajorVersionNumber($oDocument->getMajorVersionNumber()+1);
                                    // reset minor version number
                                    $oDocument->setMinorVersionNumber(0);
                                } else if ($fCheckInType == "minor") {
                                    $oDocument->setMinorVersionNumber($oDocument->getMinorVersionNumber()+1);
                                }
                                $default->log->error("major=" . $oDocument->getMajorVersionNumber() . ";minor=" . $oDocument->getMinorVersionNumber());
                                
                                // update it
                                if ($oDocument->update()) {
                                    
                                    // create the document transaction record
                                    $oDocumentTransaction = & new DocumentTransaction($oDocument->getID(), $fCheckInComment, CHECKIN);
                                    // TODO: check transaction creation status?
                                    $oDocumentTransaction->create();
                                    
                                    // fire subscription alerts for the checked in document
                                    $count = SubscriptionEngine::fireSubscription($fDocumentID, SubscriptionConstants::subscriptionAlertType("CheckInDocument"),
                                             SubscriptionConstants::subscriptionType("DocumentSubscription"),
                                             array( "modifiedDocumentName" => $oDocument->getName() ));
                                    $default->log->info("checkInDocumentBL.php fired $count subscription alerts for checked out document " . $oDocument->getName());
        
                                    //redirect to the document view page
                                    redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID());                        
                                    
                                } else {
                                    // document update failed
                                    $sErrorMessage = "An error occurred while storing this document in the database";
                                }
                            } else {
                                // reinstate the backup
                                copy($sBackupPath, $oDocument->getPath());
                                $sErrorMessage = "An error occurred while storing the new file on the filesystem";
                            }
                        } else {
                            $sErrorMessage = "Please select a document by first clicking on 'Browse'.  Then click 'Check-In'";                        
                        }
                    } else {
                        // prompt the user for a check in comment and the file
                        $oPatternCustom->setHtml(renderCheckInPage($oDocument));
                    }
                } else {
                    // this document isn't checked out
                    $oPatternCustom->setHtml("<p class=\"errorText\">You can't check in this document because its not checked out</p>\n");                    
                }
            } else {
                // no permission to checkout the document
                $oPatternCustom->setHtml("<p class=\"errorText\">Could not check in this document</p>\n");
            }
        } else {
            // couldn't instantiate the document
            $oPatternCustom->setHtml("<p class=\"errorText\">Could not check in this document</p>\n");
        }
    } else {
        // no document id was set when coming to this page,
        $oPatternCustom->setHtml("<p class=\"errorText\">No document is currently selected for check in</p>\n");
    }

    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $main->setCentralPayload($oPatternCustom);
    $main->setFormAction($_SERVER["PHP_SELF"]);
    $main->setFormEncType("multipart/form-data");
    if (isset($sErrorMessage)) {
        $main->setErrorMessage($sErrorMessage);
    }
    $oPatternCustom->setHtml(renderCheckInPage($oDocument));
    $main->render();
}
?>
