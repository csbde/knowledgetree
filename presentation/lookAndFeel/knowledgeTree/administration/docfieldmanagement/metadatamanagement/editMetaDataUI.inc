<?php

// if its the manual edit page..then display normally
function getSelectFieldPage($iDocFieldID) {	
	global $default;
	$oDocField = null;
	if (isset($iDocFieldID)) {
		$oDocField = DocumentField::get($iDocFieldID);
		
	}
	
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Edit MetaData:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr>Please Select a Field</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Document Field: </td><td>" . getDocFieldDisplay($oDocField) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// if its the manual edit page..then display normally
function getSelectMetaDataPage($iDocFieldID, $iMetaDataID) {	
	global $default;
	$oDocField = null;
	$oMetaData = null;
	

	if (isset($iDocFieldID)) {
		$oDocField = DocumentField::get($iDocFieldID);
		
	}
	
	if (isset($iMetaDataID)) {
		$oMetaData = MetaData::get($iMetaDataID);
		
	}
	
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Edit MetaData:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr>Please Select the Metadata you wish to Edit</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Document Field: </td><td>" . getDocFieldDisplay($oDocField) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>MetaData: </td><td>" . getMetaDataDisplay($oMetaData,$iDocFieldID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// if its the manual edit page..then display normally
function getEditMetaDataPage($iDocFieldID, $iMetaDataID) {	
	global $default;
	$oDocField = null;
	$oMetaData = null;
	

	if (isset($iDocFieldID)) {
		$oDocField = DocumentField::get($iDocFieldID);
		
	}
	
	if (isset($iMetaDataID)) {
		$oMetaData = MetaData::get($iMetaDataID);
		
	}
	
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Edit MetaData:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr>Enter a Different Value</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Document Field: </td><td>" . getDocFieldDisplay($oDocField) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>MetaData: </td><td>" . getMetaDataDisplay($oMetaData,$iDocFieldID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td>" . getUpdateButton($oDocField);	
	$sToRender .= getCancelButton($oDocField);	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}



// if edition is successful print otu success page
function getSuccessPage() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Success!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The metadata was successfully edited</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editMetaData\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// if edition is successful print otu success page
function getFailPage() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>FAILURE!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The metadata was not updated</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editMetaData\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// if edition is successful print otu success page
function getTextPage() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>FAILURE!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>Please Enter a Value in the Text Box</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editMetaData\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
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
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=editMetaData\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}

// show the list box when no DocField selected
// then shows a text box with the DocField's name
function getDocFieldDisplay($oDocField) {
	global $default;
	if (!isset($oDocField)) {
		
		$sWhereClause = " ST.has_lookup = 1" ;
		$oPatternListBox = & new PatternListBox($default->owl_fields_table, "name", "id", "fDocFieldID");
		//$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setWhereClause($sWhereClause);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fDocFieldID\" value=\"" . $oDocField->iId . "\">\n" .
		$oDocField->getName();
	}
	
}

// show the list box when no DocField selected
// then shows a text box with the DocField's name
function getMetaDataDisplay($oMetaData,$iDocFieldID) {
	global $default;
	if (!isset($oMetaData)) {
		
		
		$sWhereClause = " ST.document_field_id = " . $iDocFieldID;
		$oPatternListBox = & new PatternListBox($default->owl_metadata_table, "name", "id", "fMetaDataID");
		//$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setWhereClause($sWhereClause);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fMetaDataID\" value=\"" . $oMetaData->iId . "\">\n" .
		"<input type=\"text\" name=\"fMetaDataName\" value=\"" . $oMetaData->getName() . "\">\n" .
		"<input type=\"hidden\" name=\"fOldMetaDataName\" value=\"" . $oMetaData->getName() . "\">\n" ;
	}
}


?>