<?php
/**
* BL information for adding a DocType
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
	require_once("removeDocTypeFromFieldUI.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTypeFieldLink.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	
	$oPatternCustom = & new PatternCustom();
		
	if(!isset($fDocTypeSet)){
		// build first page
		
		$oPatternCustom->setHtml(getPage(null,null));
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocTypeSet=1");
	
	}else{
		// do a check to see both drop downs selected
		if($fDocTypeID == -1){
			$oPatternCustom->setHtml(getPageNotSelected());
					
		}else{ 		$fDocFieldID = DocumentTypeFieldLink::docTypeBelongsToField($fDocTypeID);	
				$oPatternCustom->setHtml(getPage($fDocTypeID,$fDocFieldID));
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocTypeSet=1&fDeleteConfirmed=1");
		}
		
	}
	
		
	if (isset($fDeleteConfirmed)){
				
		// else add to db and then goto page succes
		$oDocTypeField = new DocumentTypeFieldLink($fDocTypeID,$fDocFieldID, $fbIsMandatory);
		
		//check if checkbox checked
		if (isset($fbIsMandatory)) {
			$oDocTypeField->setIsMandatory(true);
		} else {
			$oDocTypeField->setIsMandatory(false);
		}
		
		
		$oDocTypeField->setDocTypeFieldID($fDocTypeID);
		$oDocTypeField->delete();
		$oPatternCustom->setHtml(getPageSuccess());
		
	}
	
	// render page
	$main->setCentralPayload($oPatternCustom);
	$main->render();
	
}
?>
