<?php
/**
* Presentation information for adding a DocType
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

// get page for removal
function getPage($iDocTypeID, $iDocFieldID) {	
	global $default;
	$oDocType = null;
	$oDocField = null;
	
	// change headings if DocType selected
	if (isset($iDocTypeID)) {
		$oDocType = DocumentType::get($iDocTypeID);
		$heading = "<td>Are you sure you wish to remove the Document Type from the Document Field?</tr>\n";
	}else{
		$heading = "<tr>Please select a Document Type: </tr>\n";
	}
	
	// when DocFieldid is gotten show the name
	if (isset($iDocFieldID)) {
		$oDocField = DocumentField::get($iDocFieldID);
		$DocFielddisplay = "<td>Document Field Name: " . getDocFieldDisplay($oDocField) . "</td>\n";
		
	}
	else{
		$DocFielddisplay = "";
	}
		
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Select a Document Type to Remove from its Document Field:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= $heading;
	$sToRender .= "<br>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td>DocType Name: " . getDocTypeDisplay($oDocType) . "</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= $DocFielddisplay;
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= getIsMandatoryCheckBox($oDocField) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td><td>" . getDeleteButton($oDocType);	
	$sToRender .= "<td></td><td>" . getCancelButton($oDocType);
	$sToRender .= "</td></table>\n";
	
	
	return $sToRender;
}

// show paig if deletion fails
function getPageFail($iDocTypeID) {	
	global $default;
	$oDocType = null;
	$oDocField = null;
		
	if (isset($iDocTypeID)) {
		$oDocType = DocumentType::get($iDocTypeID);
	}
	
	$iDocFieldID = DocumentTypeFieldLink::docTypeBelongsToField($iDocTypeID);
	
	$oDocField= DocumentField::get($iDocFieldID);
		
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Sorry, Removal Failed</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeDocTypeFromField\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// if no DocType selected show selection fail page
function getPageNotSelected() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Sorry, Selection Failed</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>Please select a Document Type</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeDocTypeFromField\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// show success page if removed from DocField successfully
function getPageSuccess() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Success!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The DocType was successfully removed from the DocField</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeDocTypeFromField\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// show listbox of DocTypes belonging to DocTypes_DocFields_link table
function getDocTypeDisplay($oDocType) {
	global $default;
	// added clauses to get those DocTypes belonging to the DocumentTypes_Fields_link table
	$from = "," . $default->owl_document_type_fields_table . " AS GU";
	$where = "(ST.id = GU.document_type_id)";
	
	
	if (!isset($oDocType)) {
		$oPatternListBox = & new PatternListBox($default->owl_document_types_table, "name", "id", "fDocTypeID");
		$oPatternListBox->setWhereClause($where);
		$oPatternListBox->setFromClause($from);
		//$oPatternListBox->setIncludeDefaultValue(true);
		//$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fDocTypeID\" value=\"" . $oDocType->iId . "\">\n" .
		 $oDocType->getName();
	}
	
}

// show DocField details on confirm deletion page
function getDocFieldDisplay($oDocField) {
	global $default;
	if (!isset($oDocField)) {
		//$oPatternListBox = & new PatternListBox($default->owl_DocFields_table, "name", "id", "fDocFieldID");
		//$oPatternLiListBoxstBox->setPostBackOnChange(true);
		//return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fDocFieldID\" value=\"" . $oDocField->iId . "\">\n" .
		$oDocField->getName();
	}
		
}

// returns the checkbox ticked or unticked
function getIsMandatoryCheckBox($oDocField) {
	if (!isset($oDocField)) {
		return "";
	} else {
		return "<td>Is Mandatory: <input type=\"checkbox\" name=\"fbIsMandatory\">";
	}
}

// show delete button or select button
function getDeleteButton($oDocType) {
	global $default;
	if (!isset($oDocType)) {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" border=\"0\"/></td>\n";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" border=\"0\"/></td>\n";
	}
}

// show cancel button
function getCancelButton($oDocType) {
	global $default;
	if (!isset($oDocType)) {
		//return "<td align = right><a href=\"$default->rootUrl/control.php?action=DocTypeManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=removeDocTypeFromDocField\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}


?>