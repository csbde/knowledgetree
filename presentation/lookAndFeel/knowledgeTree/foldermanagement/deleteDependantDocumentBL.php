<?php

/**
* Business logic for deleting a dependant document
*
* Expected variables:
*	$fFolderCollaborationID: primary key of folder collaboration to check
*	$fFolderID: folder we are currently editing
*	$fDependantDocumentTemplateID: primary key of dependant document to be deleted
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 14 May 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {	
	if (isset($fFolderID) && isset($fFolderCollaborationID)) {
		require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
		require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
		require_once("$default->fileSystemRoot/lib/users/User.inc");
		require_once("$default->fileSystemRoot/lib/documentmanagement/DependantDocumentTemplate.inc");
		require_once("$default->fileSystemRoot/presentation/Html.inc");		
		require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
		require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
		require_once("deleteDependantDocumentUI.inc");	
	
	
		if (Permission::userHasFolderWritePermission($fFolderID)) {
			//user has permission to alter folder contents
			if (isset($fForDelete)) {
				$oDependantDocumentTemplate = DependantDocumentTemplate::get($fDependantDocumentTemplateID);
				if ($oDependantDocumentTemplate->delete()) {
					controllerRedirect("viewDependantDocument", "fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID");				
				} else {
					$oDependantDocumentTemplate = DependantDocumentTemplate::get($fDependantDocumentTemplateID);
					$oUser = User::get($oDependantDocumentTemplate->getDefaultUserId());
					$oTemplateDocument = Document::get($oDependantDocumentTemplate->getTemplateDocumentID());
				
					$oPatternCustom = & new PatternCustom();				
					$oPatternCustom->setHtml(getPage($fFolderID, $fFolderCollaborationID, $oDependantDocumentTemplate->getDocumentTitle(), $oUser->getName(), (!($oTemplateDocument->getName() === false)) ? $oTemplateDocument->getName() : ""));
	    			$main->setCentralPayload($oPatternCustom);
	    	    	$main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fDependantDocumentTemplateID=$fDependantDocumentTemplateID&fForDelete=1");
	    	    	$main->setErrorMessage("An error occured while attempting to delete the dependant document");	    	        		
	    			$main->render();						
				}
				
			} else {				
				include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				
				$oDependantDocumentTemplate = DependantDocumentTemplate::get($fDependantDocumentTemplateID);
				$oUser = User::get($oDependantDocumentTemplate->getDefaultUserId());
				$oTemplateDocument = Document::get($oDependantDocumentTemplate->getTemplateDocumentID());
				
				$oPatternCustom = & new PatternCustom();				
				$oPatternCustom->setHtml(getPage($fFolderID, $fFolderCollaborationID, $oDependantDocumentTemplate->getDocumentTitle(), $oUser->getName(), (!($oTemplateDocument->getName() === false)) ? $oTemplateDocument->getName() : ""));
	    		$main->setCentralPayload($oPatternCustom);
	    	    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fDependantDocumentTemplateID=$fDependantDocumentTemplateID&fForDelete=1");	    	        		
	    		$main->render();	
				
			}

		
		}
		
	}
}

?>
		