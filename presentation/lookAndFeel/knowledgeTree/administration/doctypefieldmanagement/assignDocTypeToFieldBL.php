<?php
/**
* BL information for adding a group
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
	require_once("assignDocTypeToFieldUI.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
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
		if($fDocTypeID == -1 Or $fDocTypeID == -1){
	
			$oPatternCustom->setHtml(getPageNotSelected());
			
					
		}else{ //check if it belongs to a unit
			//$fieldLink = DocumentTypeFieldLink::doctypeBelongsToField($fDocTypeID);
		
			// if it does'nt ..then go to normal page
			//if($fieldLink == false){
				
				$oPatternCustom->setHtml(getPage($fDocTypeID,$fDocFieldID));
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocTypeSet=1&fDocTypeAssign=1");
			
		//	}else{
			//if it does...then go to failure page
			//	$oPatternCustom->setHtml(getPageFail($fDocTypeID));
				
			//}
		}
	}
	
	if (isset($fDocTypeAssign)){
		
					
		// else add to db and then goto page succes
		$oDocTypeField = new DocumentTypeFieldLink($fDocTypeID,$fDocFieldID, $fbIsMandatory);
		
		//check if checkbox checked
		if (isset($fbIsMandatory)) {
			$oDocTypeField->setIsMandatory(true);
		} else {
			$oDocTypeField->setIsMandatory(false);
		}
		
		if($oDocTypeField->create()){
			$oPatternCustom->setHtml(getPageSuccess());
		}else{
			$oPatternCustom->setHtml(getPageFail());
		}
		
	}
	
	// render page
	$main->setCentralPayload($oPatternCustom);
	$main->render();
	
}
?>
