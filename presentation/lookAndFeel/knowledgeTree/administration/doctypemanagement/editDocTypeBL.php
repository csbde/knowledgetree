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
	require_once("editDocTypeUI.inc");
    require_once("../adminUI.inc");
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
	
	// if a new DocType has been added
	// coming from manual edit page	
	if (isset($fUpdate)) {
		
		$oDocType = DocumentType::get($fDocTypeID);
		$oDocType->setName($fDocTypeName);
		
		// store type specific field id's in an array
		//$aGenericDocFields  = DocumentField::getGenericFields();
		//$aSpecificDocFields = DocumentTypeFieldLink::getSpecificFields($fDocTypeID);
		
		// echo "<li><pre>" . arrayToString($aSpecificDocFields ) . "</pre></li></ul>";
		
		if ($oDocType->update()) {
				// if successfull print out success message
				$oPatternCustom->setHtml(getEditPageSuccess());
				
		} else {
				// if fail print out fail message
				$oPatternCustom->setHtml(getEditPageFail());
		}
	} else if (isset($fDocTypeSelected)){		
		// post back on DocType select from manual edit page	
		// store type generic field id's in an array
		$aGenericFields  = DocumentField::getGenericFields();
		
		// get all specific fields
		$aAllSpecificFields  = DocumentField::getAllSpecificFields();
		
		
		// store type specific field id's and names in an array for specifc doctype
		$aSpecificFields = DocumentTypeFieldLink::getSpecificFields($fDocTypeID);
		
		
		
		$oPatternCustom->setHtml(getDetailsPage($fDocTypeID, $aGenericFields, $aSpecificFields,  $aAllSpecificFields));
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fShowDetails=1");
		
		
	}else if(isset($fAdd))
		{	
			if($fIsMandatory == 1){
				$iMandatory = 1;
			}else{
				$iMandatory = 0;
			}
	
			$oDocTypeField = new DocumentTypeFieldLink($fDocTypeID, $fDocFieldTypeID, $iMandatory);
			
			$oDocTypeField->create();
			
							
			// post back on DocType select from manual edit page	
			// store type generic field id's in an array
			//$oDocFieldType = new DocumentTypeFieldLink(
			$aGenericFields  = DocumentField::getGenericFields();
		
			// get all specific fields
			$aAllSpecificFields  = DocumentField::getAllSpecificFields();
		
		
			// store type specific field id's and names in an array for specifc doctype
			$aSpecificFields = DocumentTypeFieldLink::getSpecificFields($fDocTypeID);
			
			$oPatternCustom->setHtml(getDetailsPage($fDocTypeID, $aGenericFields, $aSpecificFields,  $aAllSpecificFields));

			$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocTypeSelected=1");					
			//$oPatternCustom->setHtml(getEditPageSuccess());
			
		
	}else if(isset($fRemove)){
			
			$fFieldID = $fRemove;
			
			// does'nt matter wot ismandatory is..not checking for it when deleting
			$iMandatory = 0;
			//create new object
			$oDocTypeField = new DocumentTypeFieldLink($fDocTypeID, $fFieldID, $iMandatory);
			
			//delete it by first getting hte corresponding id
			$oDocTypeField ->setDocTypeFieldID($fDocTypeID, $fFieldID);
			$oDocTypeField->delete();
			
			// post back on DocType select from manual edit page	
			// store type generic field id's in an array
			//$oDocFieldType = new DocumentTypeFieldLink(
			$aGenericFields  = DocumentField::getGenericFields();
		
			// get all specific fields
			$aAllSpecificFields  = DocumentField::getAllSpecificFields();
		
		
			// store type specific field id's and names in an array for specifc doctype
			$aSpecificFields = DocumentTypeFieldLink::getSpecificFields($fDocTypeID);
			
			$oPatternCustom->setHtml(getDetailsPage($fDocTypeID, $aGenericFields, $aSpecificFields,  $aAllSpecificFields));
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocTypeSelected=1");
	
	}else if(isset($fEdit)){ 
		$fFieldID = $fEdit;
		
		 $iMandatory = 0;
		 
		$oDocTypeField = new DocumentTypeFieldLink($fDocTypeID, $fFieldID, $iMandatory);
			
			//delete it by first getting hte corresponding id
		$oDocTypeField ->setDocTypeFieldID($fDocTypeID, $fFieldID);
		
		$iDocTypeFieldID = $oDocTypeField->getID();
				
		$oPatternCustom->setHtml(getMandatoryPage($fFieldID,$iDocTypeFieldID));
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fUpdateMandatory=1");
		
		
	
	
	}else if(isset($fUpdateMandatory)){ 
			
		
		
					 
		$oDocTypeField = new DocumentTypeFieldLink($fDocTypeID, $fFieldID, $fMandatory);
			
			//delete it by first getting hte corresponding id
		$oDocTypeField->setDocTypeFieldID($fDocTypeID, $fFieldID);
		
		if(isset($fIsMandatory)){
			$oDocTypeField->setIsMandatory(true);
		}else{
			$oDocTypeField->setIsMandatory(false);
		}
		
		$oDocTypeField->update();
		
		
						
			// post back on DocType select from manual edit page	
			// store type generic field id's in an array
			//$oDocFieldType = new DocumentTypeFieldLink(
			$aGenericFields  = DocumentField::getGenericFields();
		
			// get all specific fields
			$aAllSpecificFields  = DocumentField::getAllSpecificFields();
		
		
			// store type specific field id's and names in an array for specifc doctype
			$aSpecificFields = DocumentTypeFieldLink::getSpecificFields($fDocTypeID);
			
			$oPatternCustom->setHtml(getDetailsPage($fDocTypeID, $aGenericFields, $aSpecificFields,  $aAllSpecificFields));
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocTypeSelected=1");
		
		
	
	
	}else {
		// if nothing happens...just reload edit page
		$oPatternCustom->setHtml(getEditPage(null));
		$main->setFormAction($_SERVER["PHP_SELF"] ."?fDocTypeSelected=1");
			
	
	}
	

		
		
	
	
	//render the page
	$main->setCentralPayload($oPatternCustom);
	$main->render();	
}
?>
