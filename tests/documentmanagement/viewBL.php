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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @date 21 January 2003
 * @package presentation.lookAndFeel.knowledgeTree.documentManager
 */

require_once("../../config/dmsDefaults.php");

require_once("$default->fileSystemRoot/lib/security/Permission.inc");

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

require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentUI.inc");
require_once("viewUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");

if (checkSession()) {
    if (isset($fDocumentID)) {
    	$oDocument = & Document::get($fDocumentID);
        if (isset($fCollaborationEdit) && Permission::userHasDocumentWritePermission($oDocument)) {
            //return value from collaborationBL.php.  User attempted to edt
            //a step in the document collaboration process that is currently being
            //executed
            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

            
            $oPatternCustom = & new PatternCustom();
            $oPatternCustom->setHtml(getEditPage($oDocument));
            $main->setCentralPayload($oPatternCustom);
            $main->setErrorMessage("You cannot edit a document collaboration step that is completed or currently underway");
            $main->setFormAction("$default->rootUrl/control.php?action=modifyDocument&fDocumentID=" . $oDocument->getID());
            $main->render();
        } else if (isset($fForInlineView) && Permission::userHasDocumentReadPermission($oDocument)) {
			$oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Inline view", VIEW);
            $oDocumentTransaction->create();
            PhysicalDocumentManager::inlineViewPhysicalDocument($fDocumentID);			
		} else if (isset($fForDownload) && Permission::userHasDocumentReadPermission($oDocument)) {
            //if the user has document read permission, perform the download
            if (isset($fVersion)) {
                // we're downloading an old version of the document
                $oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document version $fVersion downloaded", DOWNLOAD);
                $oDocumentTransaction->create();
                PhysicalDocumentManager::downloadVersionedPhysicalDocument($fDocumentID, $fVersion);
            } else {
                // download the current version
                $oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document downloaded", DOWNLOAD);
                $oDocumentTransaction->create();
                PhysicalDocumentManager::downloadPhysicalDocument($fDocumentID);
            }
        } else if (isset($fBeginCollaboration) && Permission::userHasDocumentWritePermission($oDocument)) {
            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
            //begin the collaboration process
            //first ensure that all steps in the collaboration process are assigned
            $oDocument = Document::get($fDocumentID);
            $aFolderCollaboration = FolderCollaboration::getList("WHERE folder_id = " . $oDocument->getFolderID());
			if (count($aFolderCollaboration) > 0) {
				//if the the folder has collaboration steps set up
				$aFolderUserRoles = FolderUserRole::getList("document_id = " . $fDocumentID);
				if (count($aFolderCollaboration) == count($aFolderUserRoles)) {
					//if all the roles have been assigned we can start the collaboration process
                    
					//TODO: check if this collaboration has already occured, and then reset all the steps before beginning it again
					//DocumentCollaboration::resetDocumentCollaborationSteps($fDocumentID);
                    
					$oDocument->beginCollaborationProcess();
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getEditPage($oDocument));
					$main->setCentralPayload($oPatternCustom);
					$main->setErrorMessage("Document collaboration successfully started");
					$main->render();
				} else {				
					//not all the roles have users assigned to them, so display an error message
					require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getEditPage($oDocument));
					$main->setCentralPayload($oPatternCustom);
					$main->setErrorMessage("Document collaboration not started.  Not all steps in the process have been assigned");
					$main->render();					
				}
			} else {
				//the folder has no collaboration set up yet, so we can't start document collaboration
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getEditPage($oDocument));
				$main->setCentralPayload($oPatternCustom);
				$main->setErrorMessage("The collaboration steps for the folder must be set up before collaboration can begin");
				$main->render();                
            }
		} else if ((isset($fCollaborationStepComplete)) && (DocumentCollaboration::userIsPerformingCurrentCollaborationStep($fDocumentID))) {				
				//the user has signled that they have completed their step in the collaboration process
				if (DocumentCollaboration::isLastStepInCollaborationProcess($fDocumentID)) {				
					require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
					//the last step in the collaboration process has been performed- email the document creator
                    
					$oDocument = Document::get($fDocumentID);                    
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
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getEditPage($oDocument));
					$main->setCentralPayload($oPatternCustom);
					$main->setErrorMessage("Document collaboration complete.  The document initiator has been notified");
					$main->render();
					
				} else {
					require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
					//start the next steps if all criteria are met					
					DocumentCollaboration::beginNextStepInCollaborationProcess($fDocumentID, $_SESSION["userID"]);
					$oDocument = Document::get($fDocumentID);
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getEditPage($oDocument));
					$main->setCentralPayload($oPatternCustom);
					$main->setErrorMessage("The next steps in the collaboration process have been started");
					$main->render();
				}
		} else if ((isset($fForPublish)) && (!DocumentCollaboration::documentIsPendingWebPublishing($fDocumentID))) {
            if (isset($fWebSiteID)) {
                // user wishes to publish document
                $oDocument = Document::get($fDocumentID);
                $aWebDocument = WebDocument::getList("document_id = $fDocumentID");
                $oWebDocument = $aWebDocument[0];
                $oWebDocument->setStatusID(PENDING);
                $oWebDocument->setWebSiteID($fWebSiteID);
                if ($oWebDocument->update()) {
                    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                    $oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document sent for web publishing", UPDATE);
                    $oDocumentTransaction->create();
                    $oDocument = Document::get($fDocumentID);
                    DocumentCollaboration::notifyWebMaster($fDocumentID, $fComment);
                    $oPatternCustom = & new PatternCustom();
                    $oPatternCustom->setHtml(getEditPage($oDocument));
                    $main->setCentralPayload($oPatternCustom);
                    $main->setErrorMessage("The document has been marked as pending publishing and the web publisher has been notified");
                    $main->render();
                    
                } else {
                    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");					
                    $oDocument = Document::get($fDocumentID);
                    $oPatternCustom = & new PatternCustom();
                    $oPatternCustom->setHtml(getEditPage($oDocument));
                    $main->setCentralPayload($oPatternCustom);
                    $main->setErrorMessage("An error occured while attempting to update the document for publishing");
                    $main->render();					
                }
            } else {
                // prompt for the website to publish to
                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");					
                $oDocument = Document::get($fDocumentID);
                $oPatternCustom = & new PatternCustom();
                $oPatternCustom->setHtml(getWebPublishPage($oDocument));
                $main->setCentralPayload($oPatternCustom);
                $main->setFormAction($_SERVER['PHP_SELF']);
                $main->setHasRequiredFields(true);
                $main->render();
            }
			
		} else if (Permission::userHasDocumentWritePermission($oDocument) || Permission::userHasDocumentReadPermission($oDocument)) {
            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
            require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");

            $oDocument = & Document::get($fDocumentID);
            
            // check subscription flag
            if (isset($fFireSubscription)) {
                // fire subscription alerts for the modified document
                $count = SubscriptionEngine::fireSubscription($fDocumentID, SubscriptionConstants::subscriptionAlertType("ModifyDocument"),
                         SubscriptionConstants::subscriptionType("DocumentSubscription"),
                         array( "folderID" => $oDocument->getFolderID(),
                                "modifiedDocumentName" => $oDocument->getName()));
                $default->log->info("viewBL.php fired $count subscription alerts for modified document $fFolderName");                
            }
            
            $oPatternCustom = & new PatternCustom();
            if ($oDocument->isLive()) {
	            if (Permission::userHasDocumentWritePermission($oDocument)) {
	                $oPatternCustom->setHtml(getPage($oDocument, true));
	            } else if (Permission::userHasDocumentReadPermission($oDocument)) {
	                $oPatternCustom->setHtml(getPage($oDocument, false));
	            }
            } else if ($oDocument->isArchived()) {
            	// cancel
	            $oPatternCustom->setHtml("<a href=\"" . generateControllerLink("browse", "fFolderID=" . $oDocument->getFolderID()) . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
	            $main->setErrorMessage("This document has been archived.");
            }
            $main->setCentralPayload($oPatternCustom);
            $main->setDHTMLScrolling(false);
            $main->setOnLoadJavaScript("switchDiv('documentData')");
            $main->setFormAction("$default->rootUrl/control.php?action=modifyDocument&fDocumentID=" . $oDocument->getID());
            $main->render();
        } else {
            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			$oDocument = & Document::get($fDocumentID);
            $oPatternCustom = & new PatternCustom();
            $oPatternCustom->setHtml("<a href=\"" . generateControllerLink("browse", "fFolderID=" . $oDocument->getFolderID()) . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
            $main->setErrorMessage("Either you do not have permission to view this document, or the document you have chosen no longer exists on the file system.");
            $main->setCentralPayload($oPatternCustom);
            $main->render();
        }
    } else {
        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

        $oPatternCustom = & new PatternCustom();
        $oPatternCustom->setHtml("");
        $main->setErrorMessage("You have not chosen a document to view");
        $main->setCentralPayload($oPatternCustom);
        $main->render();
    }
}

?>


