<?php

/**
* Business logic for linking document creation to a folder collaboration step
*
* Expected variables:
*	$fFolderCollaborationID: primary key of folder collaboration to check
*	$fFolderID: folder we are currently editing
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 14 May 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {	
	if (isset($fFolderID) && isset($fFolderCollaborationID)) {
		
		//folder and collaboration are selected	
		require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
		require_once("$default->fileSystemRoot/lib/foldermanagement/FolderCollaboration.inc");
		require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
		require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
		require_once("$default->fileSystemRoot/lib/users/User.inc");	
		require_once("$default->fileSystemRoot/lib/documentmanagement/DependantDocumentTemplate.inc");
		require_once("$default->fileSystemRoot/presentation/Html.inc");
		require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
		require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
		require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
		require_once("viewDependantDocumentsUI.inc");	
	
	
		if (Permission::userHasFolderWritePermission($fFolderID)) {
			//user has folder write permission
			if (isset($fForStore)) {				
				$oDependantDocumentTemplate;			
				if ($fTargetDocumentID == "-1") {				
					$oDependantDocumentTemplate = & new DependantDocumentTemplate($fDocumentTitle, $fUserID, $fFolderCollaborationID);
				} else {
					$oDependantDocumentTemplate = & new DependantDocumentTemplate($fDocumentTitle, $fUserID, $fFolderCollaborationID, $fTargetDocumentID);
				}
				if (!($oDependantDocumentTemplate->create())) {
					include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getViewPage($fFolderCollaborationID, $fFolderID));
	    			$main->setCentralPayload($oPatternCustom);
	    	    	$main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForAdd=1");
	    	    	$main->setErrorMessage("An error occured attempting to store the dependant document");    		
	    			$main->render();								 
				} else {				
					controllerRedirect("viewDependantDocument", "fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID");
				}			
			} else if (isset($fForAdd)) {
				//we are adding a new dependant document
				$oFolderCollaboration = FolderCollaboration::get($fFolderCollaborationID);
				if ($oFolderCollaboration->hasDocumentInProcess()) {
					include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getViewPage($fFolderCollaborationID, $fFolderID));
	    			$main->setCentralPayload($oPatternCustom);
		    	    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForAdd=1");
		    	    $main->setErrorMessage("You cannot add a new dependant document as there is currently a document in this folder undergoing collaboration");    		
	    			$main->render();
					
				} else {						
					include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");		
					
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getAddPage($fFolderCollaborationID, $fFolderID, (isset($fUnitID) ? $fUnitID : -1), (isset($fDocumentTitle) ? $fDocumentTitle : ""), (isset($fDocument) ? $fDocument : ""), (isset($fTargetDocumentID) ? $fTargetDocumentID : "") ));
					$main->setOnLoadJavaScript("disable(document.MainForm.fTargetDocument)");
		    		$main->setCentralPayload($oPatternCustom);
		    	    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForStore=1");
		    	    $main->setHasRequiredFields(true);    		
		    		$main->render();
				}
			} else if (isset($fForEdit)) {
				$oFolderCollaboration = FolderCollaboration::get($fFolderCollaborationID);
				if ($oFolderCollaboration->hasDocumentInProcess()) {
					//can't edit if there is a document currently undergoing collaboration
					include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getViewPage($fFolderCollaborationID, $fFolderID));
	    			$main->setCentralPayload($oPatternCustom);
		    	    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForUpdate=1");
		    	    $main->setErrorMessage("You cannot add a new dependant document as there is currently a document in this folder undergoing collaboration");    		
	    			$main->render();
					
				} else {						
					include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
					
					$oDependantDocumentTemplate = DependantDocumentTemplate::get($fDependantDocumentTemplateID);				
					if ($oDependantDocumentTemplate->getTemplateDocumentID() >= 1) {
						$oDocument = Document::get($oDependantDocumentTemplate->getTemplateDocumentID());
					}							
					
					$oPatternCustom = & new PatternCustom();				
					$oPatternCustom->setHtml(getEditPage($fFolderID, $fDependantDocumentTemplateID, $fFolderCollaborationID, $oDependantDocumentTemplate->getDocumentTitle(), (isset($oDocument) ? $oDocument->getName() : ""), (isset($oDocument) ? $oDependantDocumentTemplate->getTemplateDocumentID() : null), $oDependantDocumentTemplate->getDefaultUserID()));
					$main->setOnLoadJavaScript("disable(document.MainForm.fTargetDocument)");
		    		$main->setCentralPayload($oPatternCustom);
		    	    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fDependantDocumentTemplateID=$fDependantDocumentTemplateID&fForUpdate=1");
		    	    $main->setHasRequiredFields(true);    		
		    		$main->render();
				}
			} else if (isset($fForUpdate)) {
				$oDependantDocumentTemplate = DependantDocumentTemplate::get($fDependantDocumentTemplateID);
				$oDependantDocumentTemplate->setDefaultUserID($fUserID);
				$oDependantDocumentTemplate->setDocumentTitle($fDocumentTitle);
				$oDependantDocumentTemplate->setTemplateDocumentID((isset($fTargetDocumentID) ? $fDocument : null));
				$oDependantDocumentTemplate->update();			
				
				redirect("$default->rootUrl/control.php?action=viewDependantDocument&fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID");
				
			} else {
				include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getViewPage($fFolderCollaborationID, $fFolderID));
	    		$main->setCentralPayload($oPatternCustom);
	    	    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForAdd=1");    		
	    		$main->render();   			
			}
		} else {
			//redirect the user back to their start page if they somehow
			//got here without the relevant permission
			redirect($default->root_url . "/control.php");			
		}
	}
}
?>