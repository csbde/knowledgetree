<?php

/**
* Business logic concerned with the deletion of a document.  
* Will use documentDeleteUI for presentation information
*
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 19 February 2003 
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*/

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/FolderUserRole.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");

require_once("$default->fileSystemRoot/presentation/Html.inc");

require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");

require_once("deleteDocumentUI.inc");

if (checkSession()) {
	if (isset($fDocumentID)) {
		if (Permission::userHasDocumentWritePermission($fDocumentID)) {
            // check if there is collaboration for this document
            $aFolderUserRoles = FolderUserRole::getList("document_id = $fDocumentID");
            // check if any of them are active
            $bActive = false;
            for ($i=0; $i<count($aFolderUserRoles); $i++) {
                $default->log->info("delDoc bActive=" . ($bActive ? "1" : "0") . ";folderUserRoleID=" . $aFolderUserRoles[$i]->getGroupFolderApprovalID() . "; active=" . ($aFolderUserRoles[$i]->getActive() ? "1" : "0"));
                $bActive = $bActive || $aFolderUserRoles[$i]->getActive();
            }
            if (!$bActive) {
                // there aren't any active roles for this doc
                if (isset($fDeleteConfirmed)) {
                    //deletion of document is confirmed
                    $oDocument = Document::get($fDocumentID);
                    if (isset($oDocument)) {
                        $sDocumentPath = Folder::getFolderPath($oDocument->getFolderID()) . $oDocument->getFileName();					
                        $oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document deleted", DELETE);
                        $oDocumentTransaction->create();
                        // flip the status id
                        $oDocument->setStatusID(DELETED);
                        // store
                        if ($oDocument->update()) {
                        	// now move the document to the delete folder
                            if (PhysicalDocumentManager::delete($oDocument)) {
                                // successfully deleted the document
                                $default->log->info("deleteDocumentBL.php successfully deleted document " . $oDocument->getFileName() . " from folder " . Folder::getFolderPath($oDocument->getFolderID()) . " id=" . $oDocument->getFolderID());
                                
                                // delete all collaboration roles
                                for ($i=0; $i<count($aFolderUserRoles); $i++) {
                                    $default->log->info("delDoc deleting folderuserroleID=" . $aFolderUserRoles[$i]->getGroupFolderApprovalID());
                                    $aFolderUserRoles[$i]->delete();
                                }
                                
                                // fire subscription alerts for the deleted document
                                $count = SubscriptionEngine::fireSubscription($fDocumentID, SubscriptionConstants::subscriptionAlertType("RemoveSubscribedDocument"),
                                         SubscriptionConstants::subscriptionType("DocumentSubscription"),
                                         array( "folderID" => $oDocument->getFolderID(),
                                                "removedDocumentName" => $oDocument->getName(),
                                                "folderName" => Folder::getFolderDisplayPath($oDocument->getFolderID())));
                                $default->log->info("deleteDocumentBL.php fired $count subscription alerts for removed document " . $oDocument->getName());
                                
                                // remove all document subscriptions for this document
                                if (SubscriptionManager::removeSubscriptions($fDocumentID, SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
                                    $default->log->info("deleteDocumentBL.php removed all subscriptions for this document");
                                } else {
                                    $default->log->error("deleteDocumentBL.php couldn't remove document subscriptions");
                                }
                                
                                // redirect to the browse folder page							
                                redirect("$default->rootUrl/control.php?action=browse&fFolderID=" . $oDocument->getFolderID());
                            } else {
                                //could not delete the document from the file system
                                $default->log->error("deleteDocumentBL.php Filesystem error deleting document " . $oDocument->getFileName() . " from folder " . Folder::getFolderPath($oDocument->getFolderID()) . " id=" . $oDocument->getFolderID());
                                //reverse the document deletion
                                $oDocument->setStatusID(LIVE);
                                $oDocument->update();
                                //get rid of the document transaction
                                $oDocumentTransaction->delete();
                                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
                                $oPatternCustom = & new PatternCustom();							
                                $oPatternCustom->setHtml(renderErrorPage("The document could not be deleted from the file system", $fDocumentID));
                                $main->setCentralPayload($oPatternCustom);
                                $main->render();
                            }
                        } else {
                            //could not update the documents status in the db
                            $default->log->error("deleteDocumentBL.php DB error deleting document " . $oDocument->getFileName() . " from folder " . Folder::getFolderPath($oDocument->getFolderID()) . " id=" . $oDocument->getFolderID());
                            
							//get rid of the document transaction
                            $oDocumentTransaction->delete();
                            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
                            $oPatternCustom = & new PatternCustom();							
                            $oPatternCustom->setHtml(renderErrorPage("The document could not be deleted from the database", $fDocumentID));
                            $main->setCentralPayload($oPatternCustom);
                            $main->render();
                        }
                    } else {
                        //could not load document object
                        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
                        $oPatternCustom = & new PatternCustom();							
                        $oPatternCustom->setHtml(renderErrorPage("An error occured whilst retrieving the document from the database", $fDocumentID));
                        $main->setCentralPayload($oPatternCustom);
                        $main->render();
                    }
                } else {
                    //get confirmation first				
                    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
                    $oPatternCustom = & new PatternCustom();
                    $oDocument = Document::get($fDocumentID);
                    $oPatternCustom->setHtml(getPage($fDocumentID, $oDocument->getFolderID(), $oDocument->getName()));				
                    $main->setCentralPayload($oPatternCustom);				
                    $main->render();
                }
            } else {
                // there are active collaboration roles for this doc
                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
                $oPatternCustom = & new PatternCustom();							
                $oPatternCustom->setHtml(renderErrorPage("You can't delete this document because it's still in collaboration", $fDocumentID));
                $main->setCentralPayload($oPatternCustom);
                $main->render();
            }
		} else {
			//user does not have permission to delete the document
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
			$oPatternCustom = & new PatternCustom();							
			$oPatternCustom->setHtml(renderErrorPage("You do not have permission to delete this document", $fDocumentID));
			$main->setCentralPayload($oPatternCustom);
			$main->render();
		}
	} else {
		//no document selected for deletion
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();							
		$oPatternCustom->setHtml(renderErrorPage("No document currently selected"));
		$main->setCentralPayload($oPatternCustom);
		$main->render();
	}
}
