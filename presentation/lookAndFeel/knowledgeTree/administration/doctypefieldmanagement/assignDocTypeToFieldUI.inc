<?php
/**
* Presentation information for adding a DocType
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

// gets main page
function getPage($iDocTypeID, $iDocFieldID) {	
	global $default;
	$oDocType = null;
	$oDocField = null;
	
	if (isset($iDocTypeID)) {
		$oDocType = DocumentType::get($iDocTypeID);
		$heading = "<tr>Are you Sure you wish to assign the Document Field to the Document Type? </tr>\n";
	}else{
		$heading = "<tr>Please Assign a Document Field to a Document Type: </tr>\n";
	}
	
	
	if (isset($iDocFieldID)) {
		$oDocField = DocumentField::get($iDocFieldID);
		
	}
		
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Assign Document Field to Document Type:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= $heading;
	$sToRender .= "<br>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td>Document Type Name: </td><td>" . getDocTypeDisplay($oDocType) . "</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Document Field Name: </td><td>" . getDocFieldDisplay($oDocField) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .=  getIsMandatoryCheckBox($oDocField) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td><td>" . getAssignButton($oDocType);	
	$sToRender .= getCancelButton($oDocType);	
	$sToRender .= "</td></table>\n";
	
	
	return $sToRender;
}


// page that comes up when there is nothing selected
function getPageNotSelected() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Sorry, Selection Failed</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>Please select BOTH a Document Type AND a Document Field</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=assignDocTypeToField\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

//if edition successful
function getPageSuccess() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Success!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The Document Field was successfully assigned to the specified Document Type</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=assignDocTypeToField\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

//if edition successful
function getPageFail() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Failure!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The Document Field already belongs to that Document Type</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=assignDocTypeToField\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// display the listbox initially ..then just display the text
function getDocTypeDisplay($oDocType) {
	global $default;
	//$from = "," . $default->owl_DocTypes_DocFields_table . " AS GU";
	//$where = "(ST.id = GU.DocType_id)";
	
	
	if (!isset($oDocType)) {
		$oPatternListBox = & new PatternListBox($default->owl_document_types_table, "name", "id", "fDocTypeID");
		//$oPatternListBox->setWhereClause($where);
		//$oPatternListBox->setFromClause($from);
		//$oPatternListBox->setIncludeDefaultValue(true);
		//$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fDocTypeID\" value=\"" . $oDocType->iId . "\">\n" .
		 $oDocType->getName();
	}
	
}

// display the listbox initially ..then just display the text
function getDocFieldDisplay($oDocField) {
	global $default;
	if (!isset($oDocField)) {
		$oPatternListBox = & new PatternListBox($default->owl_fields_table, "name", "id", "fDocFieldID");
		//$oPatternLiListBoxstBox->setPostBackOnChange(true);
	return $oPatternListBox->render();
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
		return "<td>Is it Mandatory?: </td><td> <input type=\"checkbox\" name=\"fbIsMandatory\">";
	}
}


// show button after DocType selected
function getAssignButton($oDocType) {
	global $default;
	if (!isset($oDocType)) {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" border=\"0\"/></td>\n";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/assign.gif\" value=\"submit\" border=\"0\"/></td>\n";
	}
}
// show button after DocType selected
function getCancelButton($oDocType) {
	global $default;
	if (!isset($oDocType)) {
		//return "<td align = right><a href=\"$default->rootUrl/control.php?action=DocTypeManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=assignDocTypeToField\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}


?>