<?php	

/**
* Business logic used for the rollback of a collaboration step
* 
* 
*/

require_once("../../../../config/dmsDefaults.php");

require_once("$default->fileSystemRoot/lib/security/permission.inc");

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

require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/collaborationRollbackUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");

if (checkSession()) {
    if (isset($fDocumentID)) {
		if (DocumentCollaboration::userIsPerformingCurrentCollaborationStep($fDocumentID)) {	
			if (isset($fForStore)) {
				//user has entered a comment
				//create the transaction and rollback the step
				$oDocumentTransaction = & new DocumentTransaction($fDocumentID, $fComment, COLLAB_ROLLBACK);
				if ($oDocumentTransaction->create()) {					
					DocumentCollaboration::rollbackCollaborationStep($fDocumentID, $fComment);					
					redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID");
				} else {
					$oDocument = Document::get($fDocumentID);
					require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getPage($oDocument->getFolderID(), $oDocument->getID(), $oDocument->getName()));
					$main->setCentralPayload($oPatternCustom);
					$main->setHasRequiredFields(true);
					$main->setErrorMessage("An error occured while creating the document transaction");
					$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fForStore=1");
					$main->render();
				}				
			} else {
				$oDocument = Document::get($fDocumentID);
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getPage($oDocument->getFolderID(), $oDocument->getID(), $oDocument->getName()));
				$main->setCentralPayload($oPatternCustom);
				$main->setHasRequiredFields(true);
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fForStore=1");
				$main->render();
			}		
		} else { 
			redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID");
		}
	}
}


?>
