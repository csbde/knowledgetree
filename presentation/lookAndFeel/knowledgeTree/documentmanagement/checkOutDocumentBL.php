<?php
/**
 * $Id$
 *
 * Business Logic to check out a document
 *
 * Expected form variable:
 * o $fDocumentID - primary key of document user is checking out
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
 * @package documentmanagement
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
    
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/checkOutDocumentUI.inc");    
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
            // user has permission to check the document out
            if (Permission::userHasDocumentWritePermission($fDocumentID)) {
                // and its not checked out already
                if (!$oDocument->getIsCheckedOut()) {
                    // if we're ready to perform the updates
                    if ($fForStore) {
                        // flip the checkout status
                        $oDocument->setIsCheckedOut(true);
                        // set the user checking the document out
                        $oDocument->setCheckedOutUserID($_SESSION["userID"]);
                        // update modification time
                        $oDocument->setLastModifiedDate(getCurrentDateTime());
                        // update it
                        if ($oDocument->update()) {
                            
                            //create the document transaction record
                            $oDocumentTransaction = & new DocumentTransaction($oDocument->getID(), $fCheckOutComment, CHECKOUT);
                            // TODO: check transaction creation status?
                            $oDocumentTransaction->create();
                            
                            // fire subscription alerts for the checked out document
                            $count = SubscriptionEngine::fireSubscription($fDocumentID, SubscriptionConstants::subscriptionAlertType("CheckOutDocument"),
                                     SubscriptionConstants::subscriptionType("DocumentSubscription"),
                                     array( "folderID" => $oDocument->getFolderID(),
                                            "modifiedDocumentName" => $oDocument->getName() ));
                            $default->log->info("checkOutDocumentBL.php fired $count subscription alerts for checked out document " . $oDocument->getName());

                            // display checkout success message in the document view page
                            controllerRedirect("viewDocument", "fDocumentID=$fDocumentID&fCheckedOut=1");
                            
                        } else {
                            // document update failed
                            $oPatternCustom->setHtml(renderErrorPage("An error occurred while storing this document in the database"));
                        }
                    } else {
                        // prompt the user for a checkout comment
                        $oPatternCustom->setHtml(getCheckOutPage($oDocument));
                    }
                } else {
                    // this document is already checked out
                    // TODO: for extra credit, tell the user who has this document checked out
                    //       but we don't display the check out button unless they have the document checked out already
                    //       so we should ever get here.
                    $oPatternCustom->setHtml(renderErrorPage("This document is already checked out", $fDocumentID));                    
                }
            } else {
                // no permission to checkout the document
                $oPatternCustom->setHtml(renderErrorPage("You don't have permission to check out this document", $fDocumentID));
            }
        } else {
            // couldn't instantiate the document
            $oPatternCustom->setHtml(renderErrorPage("Could not check out this document", $fDocumentID));
        }
    } else {
        // no document id was set when coming to this page,
        $oPatternCustom->setHtml(renderErrorPage("No document is currently selected for check out"));
    }

    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $main->setCentralPayload($oPatternCustom);
    $main->setFormAction($_SERVER["PHP_SELF"]);
    $main->render();
}
?>