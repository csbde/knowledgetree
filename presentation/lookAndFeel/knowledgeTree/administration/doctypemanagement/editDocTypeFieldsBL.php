<?php
/**
* BL information for editing a documentType
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
	require_once("editDocTypeFieldsUI.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTypeFieldLink.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	$oPatternCustom = & new PatternCustom();
	if ($fDocTypeID) {
		if ($fDocFieldID) {
			$oDocTypeFieldLink = DocumentTypeFieldLink::getByFieldAndTypeIDs($fDocTypeID, $fDocFieldID);			
			if (isset($fRemove)) {
				if ($fConfirm) {
					$oDocTypeFieldLink = DocumentTypeFieldLink::getByFieldAndTypeIDs($fDocTypeID, $fDocFieldID);
					if ($oDocTypeFieldLink->delete()) {
						// success
						$oPatternCustom->setHtml(getSuccessPage("Document field successfully deleted.", $fDocTypeID));
					} else {
						// failure
						$oPatternCustom->setHtml(getSuccessPage("Error deleting document field.", $fDocTypeID));
					}
				} else {
					// ask for confirmation
					$oPatternCustom->setHtml(getDeleteConfirmationPage($fDocTypeID, $fDocFieldID));
				}
			} else if(isset($fUpdateMandatory)) {
				if ($fConfirm) {
					if (isset($fIsMandatory)) {
						$oDocTypeFieldLink->setIsMandatory(true);
					} else {
						$oDocTypeFieldLink->setIsMandatory(false);
					}
					$default->log->info("dfl=" . arrayToString($oDocTypeFieldLink));
					$oDocTypeFieldLink->update();
					$oPatternCustom->setHtml(getDetailsPage($fDocTypeID));
				} else {
					// display edit form
					$oPatternCustom->setHtml(getEditDocumentFieldLinkPage($oDocTypeFieldLink));
					$main->setFormAction($_SERVER["PHP_SELF"] . "?fUpdateMandatory=1");
				}
			}
		} else {
			$oPatternCustom->setHtml(getDetailsPage($fDocTypeID));
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fUpdateMandatory=1");
		}
	} else {
		// no document type selected to edit
		// FIXME
	}
	
	//render the page
	$main->setCentralPayload($oPatternCustom);
	$main->render();	
}
?>