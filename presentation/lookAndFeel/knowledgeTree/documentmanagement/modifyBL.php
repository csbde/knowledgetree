<?php
/**
* Business logic data used to modify documents (will use modifyUI.inc)
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 24 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");						
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableTableSqlQuery.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/modifyUI.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");				
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	if (Permission::userHasDocumentWritePermission($fDocumentID)) {
		//if the user has write permission
		$oDocument = & Document::get($fDocumentID);
		if (isset($fForUpdate)) {
			//if the user is updating the values
			$oDocument->setName($fDocumentName);
			
			if ($oDocument->getDocumentTypeID() != $fDocumentTypeID) {
				//the user has changed the document type
				//get rid of all the old document type entries
				$oDocument->removeInvalidDocumentTypeEntries();
				$oDocument->setDocumentTypeID($fDocumentTypeID);
			}
			
			if ($oDocument->update()) {
				//on successful update, redirect to the view page
				redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID());
			} else {				
				//display the update page with an error message
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oPatternCustom = & new PatternCustom();				
				$oPatternCustom->setHtml(renderPage($oDocument, $oDocument->getDocumentTypeID()));
				$main->setCentralPayload($oPatternCustom);
				$main->setHasRequiredFields(true);	
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fForUpdate=1");	
				$main->setHasRequiredFields(true);
				$main->setErrorMessage("An error occured while attempting to update the document");
				$main->render();
			}
			
		} else {
			//display the update page
			$oDocument = & Document::get($fDocumentID);
			$oPatternCustom = & new PatternCustom();
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			$oPatternCustom->setHtml(renderPage($oDocument, $oDocument->getDocumentTypeID()));
			$main->setCentralPayload($oPatternCustom);
			$main->setHasRequiredFields(true);	
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fForUpdate=1");	
			$main->setHasRequiredFields(true);
			$main->render();
		}
	} else {
		//user doesn't have permission to edit this page
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml("");
		$main->setCentralPayload($oPatternCustom);		
		$main->setErrorMessage("You do not have permission to edit this document");
		$main->render();
	}
}

?>
