<?php
/**
 * $Id$
 *
 * Contains the business logic required to build the document view page.
 * Will use documentViewUI.php for HTML
 *
 * Expected form varaibles:
 *   o $fDocumentID - Primary key of document to view
 *
 * Optional form variables:
 *   o fCollaborationEdit - the user attempted to edit a collaboration step that is currently active 
 *   o fForDownload - the user is attempting to download the document
 *   o fBeginCollaboration - the user selected the 'Begin Collaboration' button
 *   o fFireSubscription - the document has been modified, and a subscription alert must be fired
 *
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */

require_once("../../../../config/dmsDefaults.php");

require_once("$default->fileSystemRoot/lib/security/Permission.inc");

require_once("$default->fileSystemRoot/lib/email/Email.inc");

require_once("$default->fileSystemRoot/lib/users/User.inc");

require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DependantDocumentInstance.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentCollaboration.inc");

require_once("$default->fileSystemRoot/lib/archiving/ArchivingSettings.inc");
require_once("$default->fileSystemRoot/lib/archiving/DocumentArchiving.inc");
require_once("$default->fileSystemRoot/lib/archiving/TimePeriod.inc");

require_once("$default->fileSystemRoot/lib/foldermanagement/FolderCollaboration.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/FolderUserRole.inc");
require_once("$default->fileSystemRoot/lib/roles/Role.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");

require_once("$default->fileSystemRoot/lib/web/WebDocument.inc");

require_once("$default->fileSystemRoot/lib/subscriptions/Subscription.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");

require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/archiving/restoreArchivedDocumentUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");

if (checkSession()) {
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	$oPatternCustom = & new PatternCustom();
    if (isset($fDocumentID)) {
    	$oDocument = & Document::get($fDocumentID);
        if (isset($fCollaborationEdit) && Permission::userHasDocumentWritePermission($fDocumentID)) {
            //return value from collaborationBL.php.  User attempted to edit
            //a step in the document collaboration process that is currently being
            //executed
            $sStatusMessage = "You cannot edit a document collaboration step that is completed or currently underway";
            $oPatternCustom->setHtml(getStatusPage($oDocument, $sStatusMessage));
            $main->setDHTMLScrolling(false);
        } else if (isset($fBeginCollaboration) && Permission::userHasDocumentWritePermission($fDocumentID)) {
            //begin the collaboration process
            //first ensure that all steps in the collaboration process are assigned
            $aFolderCollaboration = FolderCollaboration::getList("WHERE folder_id = " . $oDocument->getFolderID());
			if (count($aFolderCollaboration) > 0) {
				//if the the folder has collaboration steps set up
				$aFolderUserRoles = FolderUserRole::getList("document_id = " . $fDocumentID);
				if (count($aFolderCollaboration) == count($aFolderUserRoles)) {
					//if all the roles have been assigned we can start the collaboration process
                    
					//TODO: check if this collaboration has already occured, and then reset all the steps before beginning it again
					//DocumentCollaboration::resetDocumentCollaborationSteps($fDocumentID);
                    
					$oDocument->beginCollaborationProcess();
					$sStatusMessage = "Document collaboration successfully started";
					$oPatternCustom->setHtml(getStatusPage($oDocument, $sStatusMessage));
				} else {
					// check that default users have been assigned to the routing steps before using them
					
					if (FolderCollaboration::defaultUsersAssigned($aFolderCollaboration)) {
						//not all the roles have actual users assigned to them, so we must assign the
						//default users and then proceed										
						
						FolderUserRole::createDefaultFolderUserRoles($oDocument);
						$oDocument->beginCollaborationProcess();
						$sStatusMessage = "Document collaboration successfully started";					
						$oPatternCustom->setHtml(getStatusPage($oDocument, $sStatusMessage));
					} else {
						// the folder does not have default users assigned for the routing steps
						$sStatusMessage = "Default users have not been assigned at the folder level.  Please set these up, or choose specific users for this document before attempting to start collaboration.";				
						$oPatternCustom->setHtml(getStatusPage($oDocument, $sStatusMessage));						
					}
				}
			} else {
				//the folder has no collaboration set up yet, so we can't start document collaboration
				$sStatusMessage = "The collaboration steps for the folder must be set up before collaboration can begin";				
				$oPatternCustom->setHtml(getStatusPage($oDocument, $sStatusMessage));
            }
            $main->setDHTMLScrolling(false);
            
		} else if ((isset($fCollaborationStepComplete)) && (DocumentCollaboration::userIsPerformingCurrentCollaborationStep($fDocumentID))) {				
			//the user has signled that they have completed their step in the collaboration process
			if (DocumentCollaboration::isLastStepInCollaborationProcess($fDocumentID)) {				
				//the last step in the collaboration process has been performed- email the document creator
                $oDocument->endCollaborationProcess();
                
                // on the last collaboration step- trigger a major revision
                // major version number rollover
                $oDocument->setMajorVersionNumber($oDocument->getMajorVersionNumber()+1);
                // reset minor version number
                $oDocument->setMinorVersionNumber(0);
                $oDocument->update();
                // TODO: create a transaction?
                
				$oUser = User::get($oDocument->getCreatorID());
				$sBody = $oUser->getName() . ", the collaboration process for the document, '" . generateLink("/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php", "fDocumentID=" . $oDocument->getID(), $oDocument->getName()) . "', has been completed. ";								
				$oEmail = & new Email();
				$oEmail->send($oUser->getEmail(), "Document collaboration complete", $sBody);
				
                // collaboration accepted transaction
                $oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document collaboration step accepted", COLLAB_ACCEPT);
                if ($oDocumentTransaction->create()) {
                	$default->log->debug("viewBL.php created collaboration accepted document transaction for document ID=$fDocumentID");                                    	
                } else {
                	$default->log->error("viewBL.php couldn't create collaboration accepted  document transaction for document ID=$fDocumentID");
                }
                				
				//possibly set the document up for web publishing????
				$sStatusMessage = "Document collaboration complete.  The document initiator has been notified";					
				$oPatternCustom->setHtml(getStatusPage($oDocument, $sStatusMessage));					
			} else {
				//start the next steps if all criteria are met					
				DocumentCollaboration::beginNextStepInCollaborationProcess($fDocumentID, $_SESSION["userID"]);
                // collaboration accepted transaction
                $oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document collaboration step accepted", COLLAB_ACCEPT);
                if ($oDocumentTransaction->create()) {
                	$default->log->debug("viewBL.php created collaboration accepted document transaction for document ID=$fDocumentID");                                    	
                } else {
                	$default->log->error("viewBL.php couldn't create collaboration accepted  document transaction for document ID=$fDocumentID");
                }				
				$sStatusMessage = "The next steps in the collaboration process have been started";					
				$oPatternCustom->setHtml(getStatusPage($oDocument, $sStatusMessage));
			}
            $main->setDHTMLScrolling(false);
			
		} else if (isset($fForPublish) && 
				   !DocumentCollaboration::documentIsPublished($fDocumentID) &&
				   !DocumentCollaboration::documentIsPendingWebPublishing($fDocumentID)) {
				   	
			if ($fSubmit) {
	            // user wishes to publish document
	            $oWebDocument = WebDocument::get(lookupID($default->web_documents_table, "document_id", $fDocumentID));
	            $default->log->info("retrieved web document=" . arrayToString($oWebDocument));
				if ($oWebDocument) {
		            if ($fWebSiteID) {
			            $oWebDocument->setStatusID(PENDING);
			            $oWebDocument->setWebSiteID($fWebSiteID);
			            $oWebDocument->setDateTime(getCurrentDateTime());
		            } else {	            
			            $oWebDocument->setStatusID(PUBLISHED);
			            $oWebDocument->setWebSiteID(-1);
		                $oWebDocument->setDateTime(getCurrentDateTime());
		            }
		            
	                if ($oWebDocument->update()) {
	                	$default->log->info("updated status=" . arrayToString($oWebDocument));
	                    $oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document sent for web publishing", UPDATE);
	                    $oDocumentTransaction->create();
	                    if ((strlen($fWebSiteID) > 0)) {
	                    	DocumentCollaboration::notifyWebMaster($fDocumentID, $fComment);
	                    }
	                    if ($fWebSiteID) {
	                    	$sStatusMessage = "The document has been marked as pending publishing and the web publisher has been notified";
	                    } else {
	                    	$sStatusMessage = "The document has been published";                    	
	                    }
	                    $default->log->info("printing page");
	                    $oPatternCustom->setHtml(getStatusPage($oDocument, $sStatusMessage));                    
	                } else {
	                    $sStatusMessage = "An error occured while attempting to update the document for publishing.  Please try again later.";                	
	                    $oPatternCustom->setHtml(getStatusPage($oDocument, $sStatusMessage));
	                }
				} else {
					$sStatusMessage = "An error occured while attempting to update the document for publishing.  Please try again later.";                	
					$oPatternCustom->setHtml(getStatusPage($oDocument, $sStatusMessage));
				}
            } else {
                // prompt for the website to publish to
                $oPatternCustom->setHtml(getWebPublishPage($oDocument));
                $main->setFormAction($_SERVER['PHP_SELF']);
            }
            $main->setDHTMLScrolling(false);
			
		} else if (Permission::userHasDocumentWritePermission($fDocumentID) || Permission::userHasDocumentReadPermission($fDocumentID)) {
          
            // check subscription flag
            // ??
            if (isset($fFireSubscription)) {
                // fire subscription alerts for the modified document
                $count = SubscriptionEngine::fireSubscription($fDocumentID, SubscriptionConstants::subscriptionAlertType("ModifyDocument"),
                         SubscriptionConstants::subscriptionType("DocumentSubscription"),
                         array( "folderID" => $oDocument->getFolderID(),
                                "modifiedDocumentName" => $oDocument->getName()));
                $default->log->info("viewBL.php fired $count subscription alerts for modified document $fFolderName");                
            }
            
            if ($oDocument->isLive()) {
	            if (Permission::userHasDocumentWritePermission($fDocumentID)) {
	                $oPatternCustom->setHtml(getPage($oDocument, true));
	            } else if (Permission::userHasDocumentReadPermission($fDocumentID)) {
	                $oPatternCustom->setHtml(getPage($oDocument, false));
	            }
	            $main->setDHTMLScrolling(false);

	            $sJavaScript = "switchDiv('" . (isset($fShowSection) ? $fShowSection : "documentData") . "', 'document');";
	            if ($fCheckedOut) {
	            	$sCheckOutMessage = "You have now checked out this document. No one else can make updates to the document while you have it checked out. Save the document, make your changes and check it back in as soon as you finish working on it.";	            	
	            	$sJavaScript .= "redirectLink('$sCheckOutMessage', '" . generateControllerUrl("downloadDocument", "fDocumentID=$fDocumentID") . "')"; 
	            } 	            
	            $main->setOnLoadJavaScript($sJavaScript);
            } else if ($oDocument->isArchived()) {
	                        	
            	// allow admins to restore the document
            	if (Permission::userIsSystemAdministrator() || Permission::userIsUnitAdministrator()) {
					$oPatternCustom->setHtml(getRestoreArchivedDocumentPage($oDocument));
            	} else {
            		// and ordinary users to request that the document be restored
					$oPatternCustom->setHtml(getRequestRestoreDocumentPage($oDocument));
            	}            
            } else {
	            $oPatternCustom->setHtml("<a href=\"" . generateControllerLink("browse", "fFolderID=" . $oDocument->getFolderID()) . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
	            $main->setErrorMessage("The document you have chosen no longer exists in the DMS.");
            }
            $main->setFormAction("$default->rootUrl/control.php?action=modifyDocument&fDocumentID=" . $oDocument->getID());
        } else {
        	if ($oDocument) {
            	$oPatternCustom->setHtml("<a href=\"" . generateControllerLink("browse", "fFolderID=" . $oDocument->getFolderID()) . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
        	} else {
        		$oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
        	}
            $main->setErrorMessage("Either you do not have permission to view this document, or the document you have chosen no longer exists on the file system.");
        }
        
    } else {
        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
        $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
        $main->setErrorMessage("You have not chosen a document to view");
    }
    $main->setCentralPayload($oPatternCustom);            
    $main->render();
}
?>