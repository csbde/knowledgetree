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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @date 21 January 2003
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */

require_once("../../../../config/dmsDefaults.php");

require_once("$default->fileSystemRoot/lib/security/permission.inc");

require_once("$default->fileSystemRoot/lib/email/Email.inc");

require_once("$default->fileSystemRoot/lib/users/User.inc");

require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DependantDocumentInstance.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentCollaboration.inc");

require_once("$default->fileSystemRoot/lib/archiving/DocumentArchiving.inc");

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
            $main->setFormAction("$default->rootUrl/control.php?action=modifyDocument&fDocumentID=" . $oDocument->getID());
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
					//not all the roles have actual users assigned to them, so we must assign the
					//default users and then proceed										
					
					FolderUserRole::createDefaultFolderUserRoles($oDocument);
					$oDocument->beginCollaborationProcess();
					$sStatusMessage = "Document collaboration successfully started";					
					$oPatternCustom->setHtml(getStatusPage($oDocument, $sStatusMessage));
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
				
				//possibly set the document up for web publishing????
				$sStatusMessage = "Document collaboration complete.  The document initiator has been notified";					
				$oPatternCustom->setHtml(getStatusPage($oDocument, $sStatusMessage));					
			} else {
				//start the next steps if all criteria are met					
				DocumentCollaboration::beginNextStepInCollaborationProcess($fDocumentID, $_SESSION["userID"]);
				$sStatusMessage = "The next steps in the collaboration process have been started";					
				$oPatternCustom->setHtml(getStatusPage($oDocument, $sStatusMessage));
			}
            $main->setDHTMLScrolling(false);
			
		} else if ((isset($fForPublish)) && (!DocumentCollaboration::documentIsPendingWebPublishing($fDocumentID))) {
			if ($fSubmit) {
	            // user wishes to publish document
	            $aWebDocument = WebDocument::getList("document_id = $fDocumentID");
	            $oWebDocument = $aWebDocument[0];

	            if (strlen($fWebSiteID) > 0) {
		            $oWebDocument->setStatusID(PENDING);
		            $oWebDocument->setWebSiteID($fWebSiteID);
		            $oWebDocument->setDateTime(getCurrentDateTime());
	            } else {	            
		            $oWebDocument->setStatusID(PUBLISHED);
		            $oWebDocument->setWebSiteID(-1);
	                $oWebDocument->setDateTime(getCurrentDateTime());
	            }
	            
                if ($oWebDocument->update()) {
                    $oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document sent for web publishing", UPDATE);
                    $oDocumentTransaction->create();
                    if ((strlen($fWebSiteID) > 0) && (strlen($fComment) > 0)) {
                    	DocumentCollaboration::notifyWebMaster($fDocumentID, $fComment);
                    }
                    if ((strlen($fWebSiteID) > 0) && (strlen($fComment) > 0)) {
                    	$sStatusMessage = "The document has been marked as pending publishing and the web publisher has been notified";
                    } else {
                    	$sStatusMessage = "The document has been published";                    	
                    }
                    $oPatternCustom->setHtml(getPage($oDocument, true, $sStatusMessage));                    
                } else {
                    $sStatusMessage = "An error occured while attempting to update the document for publishing";                	
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
	            $main->setOnLoadJavaScript("switchDiv('" . (isset($fShowSection) ? $fShowSection : "documentData") . "', 'document')");
	            
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