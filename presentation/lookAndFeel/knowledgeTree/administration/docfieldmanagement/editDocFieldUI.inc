<?php
/**
* Presentation information for adding a DocField
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// get hte edit page if it comes from the add page



// if its the manual edit page..then display normally
function getEditPage($iDocFieldID) {	
	global $default;
	$oDocField = null;
	if (isset($iDocFieldID)) {
		$oDocField = DocumentField::get($iDocFieldID);
		$checkBox = $oDocField->isFieldLinkedToType();
		$aDataTypes =  $oDocField->getAllDataTypes();
		$currentDataType = $oDocField->getDataType();
		
	}
		
	
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Edit Document Field Properties:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Document Field Name: </td><td>" . getDocFieldDisplay($oDocField) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	
	// if a DocField selected...show checkboxes
	if (isset($iDocFieldID)) {
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td>Data Type: </td><td>" . getDataTypesList($aDataTypes, $currentDataType ) . "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		
		if($checkBox == false)
		{	$sToRender .= "<td>Is Generic: </td><td>" . getIsGenericCheckBox($oDocField) . "</td>\n";
		}else{
			$sToRender .= "<input type=\"hidden\" name=\"fDocFieldIsGeneric\" value=\"" . $oDocField->getIsGeneric() . "\">\n";
		}
		
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td>Has MetaData Lookup: </td><td>" . getHasLookupCheckBox($oDocField) . "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "</tr>\n";
	}


	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td>" . getUpdateButton($oDocField);	
	$sToRender .= getCancelButton($oDocField);	
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// if edition is successful print otu success page
function getEditPageSuccess() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Success!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The Document Field was successfully modified</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editDocField\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// gets the page if edition fails
function getEditPageFail() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>FAILURE!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The Document Field modification Failed!</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editDocField\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// show the list box when no DocField selected
// then shows a text box with the DocField's name
function getDocFieldDisplay($oDocField) {
	global $default;
	if (!isset($oDocField)) {
		$oPatternListBox = & new PatternListBox($default->owl_fields_table, "name", "id", "fDocFieldID");
		//$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fDocFieldID\" value=\"" . $oDocField->iId . "\">\n" .
		"<input size = \"40\" type=\"text\" name=\"fDocFieldName\" value=\"" . $oDocField->getName() . "\">";
	}
	
}

// show Group details on confirm deletion page
function getDataTypesList($aDataTypes, $sCurrent) {
	global $default;

		/*"select UGL.group_id, GL.name from users_groups_link as UGL
		 inner join groups_lookup AS GL on UGL.group_id = GL.id
		 where UGL.user_id=1"'*/

		
		$sDataype = "<Select Name=\"fDocFieldDataType\">";
		 
		 for ($i=0;$i < count($aDataTypes);$i++){
		 	
		 	if($aDataTypes[$i]["name"] == $sCurrent){
		 		 $Options .=  "<OPTION value= \"". $aDataTypes[$i]["name"]. "\" SELECTED>". $aDataTypes[$i]["name"] ."</OPTION>\n";
		 	}else{
		 	  // $oDocField= DocField::get($aAllSpecific[$i]["id"]);
		 	    $Options .=  "<OPTION value= \"". $aDataTypes[$i]["name"] . "\">". $aDataTypes[$i]["name"] ."</OPTION>\n";
		 	}
		 	
		 }
		
		$sDataype .= $Options . "</SELECT>";
		
		return $sDataype;
	
}


// returns the checkbox ticked or unticked
function getIsGenericCheckBox($oDocField) {
	if (!isset($oDocField)) {
		return "";
	} else {
		return "<input type=\"checkbox\" name=\"fDocFieldIsGeneric\" " . ($oDocField->getIsGeneric() ? "CHECKED " : " ") . " />";
	}
}

// returns the checkbox ticked or unticked
function getHasLookupCheckBox($oDocField) {
	if (!isset($oDocField)) {
		return "";
	} else {
		return "<input type=\"checkbox\" name=\"fDocFieldHasLookup\" " . ($oDocField->getHasLookup() ? "CHECKED " : " ") . " />";
	}
	
}

// gets the updatebutton when a DocField is selected
function getUpdateButton($oDocField) {
	global $default;
	if (!isset($oDocField)) {
		return "Please select a Document Field: ";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"submit\" /></td>\n";
	}
}

// gets the cancel button when a DocField is selected
function getCancelButton($oDocField) {
	global $default;
	if (!isset($oDocField)) {
		//return "<td align = right><a href=\"$default->rootUrl/control.php?action=DocFieldManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=editDocField\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}
?>