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
	echo $fUserID;
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/FolderCollaboration.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("viewDependantDocumentsUI.inc");	
	
	//folder and collaboration are selected
		$oFolderCollaboration = FolderCollaboration::get($fFolderCollaborationID);
		if ($oFolderCollaboration->hasDocumentInProcess()) {
			//can't add document links if a document is currently undergoing the
			//collaboration process			
			
		} if (isset($fForAdd)) {
			//we are adding a new dependant document			
			include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");		
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getAddPage($fFolderCollaborationID, $fFolderID, (isset($fUnitID) ? $fUnitID : -1), (isset($fDocumentTitle) ? $fDocumentTitle : "")));
    		$main->setCentralPayload($oPatternCustom);
    	    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForStore=1");
    	    $main->setHasRequiredFields(true);    		
    		$main->render();
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