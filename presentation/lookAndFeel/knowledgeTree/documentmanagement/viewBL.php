<?php
/**
* documentViewUI.php
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


require_once("../../../../config/dmsDefaults.php");

require_once("$default->fileSystemRoot/lib/security/permission.inc");

require_once("$default->fileSystemRoot/lib/email/Email.inc");

require_once("$default->fileSystemRoot/lib/users/User.inc");

require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");

require_once("$default->fileSystemRoot/lib/foldermanagement/FolderCollaboration.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/FolderUserRole.inc");
require_once("$default->fileSystemRoot/lib/roles/Role.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");

require_once("$default->fileSystemRoot/lib/subscriptions/Subscription.inc");

require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");

if (checkSession()) {
    if (isset($fDocumentID)) {
        if (isset($fCollaborationEdit) && Permission::userHasDocumentWritePermission($fDocumentID)) {
            //return value from collaborationBL.php.  User attempted to edt
            //a step in the document collaboration process that is currently being
            //executed
            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

            $oDocument = & Document::get($fDocumentID);
            $oPatternCustom = & new PatternCustom();
            $oPatternCustom->setHtml(getEditPage($oDocument));
            $main->setCentralPayload($oPatternCustom);
            $main->setErrorMessage("You cannot edit a document collaboration step that is completed or currently underway");
            $main->setFormAction("$default->rootUrl/control.php?action=modifyDocument&fDocumentID=" . $oDocument->getID());
            $main->render();
        } else if (isset($fForInlineView) && Permission::userHasDocumentReadPermission($fDocumentID)) {
			$oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Inline view", DOWNLOAD);
            $oDocumentTransaction->create();
            PhysicalDocumentManager::inlineViewPhysicalDocument($fDocumentID);			
		} else if (isset($fForDownload) && Permission::userHasDocumentReadPermission($fDocumentID)) {
            //if the user has document read permission, perform the download
            $oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document downloaded", DOWNLOAD);
            $oDocumentTransaction->create();
            PhysicalDocumentManager::downloadPhysicalDocument($fDocumentID);
        } else if (isset($fBeginCollaboration) && Permission::userHasDocumentWritePermission($fDocumentID)) {
            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
            //begin the collaboration process
            //first ensure that all steps in the collaboration process are assigned
            $oDocument = Document::get($fDocumentID);
            $aFolderCollaboration = FolderCollaboration::getList("WHERE folder_id = " . $oDocument->getFolderID());
            $aFolderUserRoles = FolderUserRole::getList("WHERE document_id = " . $fDocumentID);
            if (count($aFolderCollaboration) == count($aFolderUserRoles)) {
                //if all the roles have been assigned we can start the collaboration process
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

        } else if (Permission::userHasDocumentWritePermission($fDocumentID) || Permission::userHasDocumentReadPermission($fDocumentID)) {
            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
            require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");

            $oDocument = & Document::get($fDocumentID);
            
            // check subscription flag
            if (isset($fFireSubscription)) {
                // fire subscription alerts for the modified document
                $count = SubscriptionEngine::fireSubscription($fDocumentID, SubscriptionConstants::subscriptionAlertType("ModifyDocument"),
                         SubscriptionConstants::subscriptionType("DocumentSubscription"),
                         array( "modifiedDocumentName" => $oDocument->getName()));
                $default->log->info("viewBL.php fired $count subscription alerts for modified document $fFolderName");                
            }
            
            $oPatternCustom = & new PatternCustom();
            if (Permission::userHasDocumentWritePermission($fDocumentID)) {
                $oPatternCustom->setHtml(getEditPage($oDocument));
            } else if (Permission::userHasDocumentReadPermission($fDocumentID)) {
                $oPatternCustom->setHtml(getViewPage($oDocument));
            }
            $main->setCentralPayload($oPatternCustom);
            $main->setFormAction("$default->rootUrl/control.php?action=modifyDocument&fDocumentID=" . $oDocument->getID());
            $main->render();
        } else {
            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

            $oPatternCustom = & new PatternCustom();
            $oPatternCustom->setHtml("");
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


