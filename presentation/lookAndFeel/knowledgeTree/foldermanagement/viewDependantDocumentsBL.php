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
	echo $fTemplateDocument;
	echo $fDocumentID;	
	if (isset($fFolderID) && isset($fFolderCollaborationID)) {	
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/FolderCollaboration.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DependantDocumentTemplate.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("viewDependantDocumentsUI.inc");	
	
	//folder and collaboration are selected
		if (isset($fForStore)) {
			$oDependantDocumentTemplate;			
			if ($fTemplateDocumentID == "-1") {
				$oDependantDocumentTemplate = & new DependantDocumentTemplate($fDocumentTitle, $fUserID, $fFolderCollaborationID);
			} else {
				$oDependantDocumentTemplate = & new DependantDocumentTemplate($fDocumentTitle, $fUserID, $fFolderCollaborationID, $fTemplateDocumentID);
			}
			if (!($oDependantDocumentTemplate->create())) {
				include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getViewPage($fFolderCollaborationID, $fFolderID));
    			$main->setCentralPayload($oPatternCustom);
    	    	$main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForAdd=1");
    	    	$main->setErrorMessage("An error occured attempting to store the depedant document");    		
    			$main->render();								 
			} else {				
				redirect("$default->rootUrl/control.php?action=viewDependantDocument&fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID");
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
	    	    $main->setErrorMessage("You cannot add a new depedant document as there is currently a document in this folder undergoing collaboration");    		
    			$main->render();
				
			} else {						
				include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");		
				
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getAddPage($fFolderCollaborationID, $fFolderID, (isset($fUnitID) ? $fUnitID : -1), (isset($fDocumentTitle) ? $fDocumentTitle : ""), (isset($fTemplateDocument) ? $fTemplateDocument : ""), (isset($fDocumentID) ? $fDocumentID : "") ));
	    		$main->setCentralPayload($oPatternCustom);
	    	    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForStore=1");
	    	    $main->setHasRequiredFields(true);    		
	    		$main->render();
			}
		} else if (isset($fForEdit)) {
			$oFolderCollaboration = FolderCollaboration::get($fFolderCollaborationID);
			if ($oFolderCollaboration->hasDocumentInProcess()) {
				include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getViewPage($fFolderCollaborationID, $fFolderID));
    			$main->setCentralPayload($oPatternCustom);
	    	    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForAdd=1");
	    	    $main->setErrorMessage("You cannot add a new depedant document as there is currently a document in this folder undergoing collaboration");    		
    			$main->render();
				
			} else {						
				include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");		
				
				/*$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getAddPage($fFolderCollaborationID, $fFolderID, (isset($fUnitID) ? $fUnitID : -1), (isset($fDocumentTitle) ? $fDocumentTitle : ""), (isset($fTemplateDocument) ? $fTemplateDocument : ""), (isset($fDocumentID) ? $fDocumentID : "") ));
	    		$main->setCentralPayload($oPatternCustom);
	    	    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForStore=1");
	    	    $main->setHasRequiredFields(true);    		
	    		$main->render();*/
			}			
		} else {
			include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getViewPage($fFolderCollaborationID, $fFolderID));
    		$main->setCentralPayload($oPatternCustom);
    	    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForAdd=1");    		
    		$main->render();   			
		}	
	
	}

}

?>