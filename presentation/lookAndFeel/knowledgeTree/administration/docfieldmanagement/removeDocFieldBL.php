<?php
/**
* BL information for adding a DocField
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("removeDocFieldUI.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	$oPatternCustom = & new PatternCustom();	
	
	if (isset($fDocFieldID)) {
		$oDocField = DocumentField::get($fDocFieldID);
		if ($oDocField) {
			// check if we're trying to delete the category field
			if ($oDocField->getName() != "Category") {	
				// check if the document field is mapped to a document type first
				$aDocumentTypes = $oDocField->getDocumentTypes();
				if (count($aDocumentTypes) > 0) {
					// display status message- can't delete
					$oPatternCustom->setHtml(getFieldMappedPage($oDocField->getName(), $aDocumentTypes));
				} else {
					// perform the deletion
					if (isset($fForDelete)) {
						if ($oDocField->delete()) {
							$oPatternCustom->setHtml(getDeleteSuccessPage());
						} else {
							$oPatternCustom->setHtml(getDeleteFailPage());
						}
					} else {
						// delete confirmation page
						$oPatternCustom->setHtml(getDeletePage($fDocFieldID));
						$main->setFormAction($_SERVER["PHP_SELF"] . "?fForDelete=1");
					}
				}
			} else {
				// couldn't retrieve document field from db
				$oPatternCustom->setHtml(getStatusPage("Read-only document field", "The 'Category' document field cannot be deleted."));
			}
		} else {
			// couldn't retrieve document field from db
			$oPatternCustom->setHtml(getStatusPage("Non-existent document field", "This document field does not exist in the database"));
		}
	} else {
		// prompt for a field to delete
		$oPatternCustom->setHtml(getDeletePage(null));
		$main->setFormAction($_SERVER["PHP_SELF"] );
	}
	
	$main->setCentralPayload($oPatternCustom);				
	$main->render();		
}
?>