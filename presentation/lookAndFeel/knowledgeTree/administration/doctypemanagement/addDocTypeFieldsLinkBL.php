<?php
/**
* BL information for adding a Org
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
require_once("../../../../../config/dmsDefaults.php");

global $default;

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
	require_once("addDocTypeFieldsLinkUI.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTypeFieldLink.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
			
	$oPatternCustom = & new PatternCustom();
		
	if (isset($fAdd)){
		if (isset($fDocTypeID)){			
			
			if ($bMandatory){ $bMandatory = true; } 
			else { $bMandatory = false; }
			
			if ($fFieldID > 0){	// Use Existing Non-generic field				
				
				$oDocTypeFieldLink = new DocumentTypeFieldLink($fDocTypeID,$fFieldID,$bMandatory);
								
				if ($oDocTypeFieldLink->create()){
					$oPatternCustom->setHtml(addSuccessPage($fDocTypeID));
				}else {
					$oPatternCustom->setHtml(addFailPage($fDocTypeID));;
				}				
			
			} else if (strlen($fNewField) > 0){  // Create a New Field
						
				if ($bHasLookup){ $bHasLookup = true; }
				else { $bHasLookup = false;	}
				
				$oDocField = new DocumentField($fNewField, $fDataType, false, $bHasLookup);
				if ($oDocField->create()) {
					$fFieldID = $oDocField->getID();
					$oDocTypeFieldLink = new DocumentTypeFieldLink($fDocTypeID,$fFieldID,$bMandatory);
										
					if ($oDocTypeFieldLink->create()){
						$oPatternCustom->setHtml(getCreateNewSuccess($fDocTypeID));
					}else {
						$oPatternCustom->setHtml(getCreateNewFail($fDocTypeID));
					}					
				} else {
					$oPatternCustom->setHtml(getCreateNewFail($fDocTypeID));					
				}					
			} else {
				$oPatternCustom->setHtml(getFail_NoFieldID($fDocTypeID));
			}		
		}else{ //error
			$oPatternCustom->setHtml(getMissingDocTypeIDPage($fDocTypeID));
		}
	
	} else if (isset($fDocTypeID)){	     
	    if (isset($fFromList)){
		    $sNewTableName = $default->owl_fields_table;
		    $sNewDisplayColumn = "name";
		    $sNewValueColumn = "id";
		    $sNewSelectName = "fFieldID";
		    $sNewWhereClause = "is_generic != 1";
		    $bNewOrderAsc = true;
		   
		    $oSelectBox = & new PatternListBox($sNewTableName, $sNewDisplayColumn, $sNewValueColumn, $sNewSelectName, $sNewWhereClause , $bNewOrderAsc );	    
			if (count($oSelectBox->getEntries()) > 0) {			
		    
			    $main->setFormAction($_SERVER['PHP_SELF'] . "?fAdd=1&fDocTypeID=$fDocTypeID");
			    
			    $htmlListBox = $oSelectBox->render();
			    $oPatternCustom->addHtml(getFirstPage($htmlListBox, $fDocTypeID));	
			
			} else { //Go to -> no Non-generic fields exist
				$oPatternCustom->addHtml(getListFailPage($fDocTypeID));
			}
		} else if(isset($fNewField)){//A new Field Entry
			$main->setFormAction($_SERVER['PHP_SELF'] . "?fAdd=1&fDocTypeID=$fDocTypeID");
			$oPatternCustom->addHtml(getFirstPage($Nothing, $fDocTypeID, true));
	    } else { // get OptionPage
			$oPatternCustom->addHtml(getOptionPage($fDocTypeID));			
		}
	} else {
		$oPatternCustom->setHtml(getMissingDocTypeIDPage());
	}
			
	$main->setCentralPayload($oPatternCustom);	
    $main->setHasRequiredFields(true);
	$main->render();
}
?>
