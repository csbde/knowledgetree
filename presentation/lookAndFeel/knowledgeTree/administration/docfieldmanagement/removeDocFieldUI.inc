<?php

/**
* Presentation information for Deleting a DocField 
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// gets the delete stuff
function getDeletePage($fDocFieldID) {	
	global $default;
	$oDocField = null;
	if (!isset($fDocFieldID)) {
		$sToRender .= "<br></br>\n";		
		$sToRender .= "<b>Remove Document Field From System:</b>\n";
	
	}
	else{
		
		$oDocField = DocumentField::get($fDocFieldID);
		$sToRender .= "<br></br>\n";
		$sToRender .= "<b>Are you sure you wish to Remove this Document Field?:</b>\n";
	}
	
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Document Field Name: </td><td>" . getDocFieldDisplay($oDocField) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getDocFieldDataType($oDocField) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getIsGenericCheckBox($oDocField) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getHasLookupCheckBox($oDocField) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td>" . getDeleteButton($oDocField);	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {	
	
global $default;

$sToRender  = "<br></br>\n" ;
$sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
$sToRender .= "<tr>\n";
$sToRender .= "<td><b>Document Field SuccessFully Removed!</b></td>\n";
$sToRender .= "</tr>\n";	
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr>\n";
$sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=removeDocField\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
$sToRender .= "</tr>\n";
$sToRender .= "</table>\n";
		
return $sToRender;
}

// get deletion failed page
function getDeleteFailPage() {	

global $default;	

$sToRender  = "<br></br>\n" ;
$sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
$sToRender .= "<tr>\n";
$sToRender .= "<td><b>Deletion Failed!</b></td>\n";
$sToRender .= "</tr>\n";	
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr><td>Please Ensure that The Document Field has not been assigned</td></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "</tr>\n";
$sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=removeDocField\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td></tr>\n";
$sToRender .= "</table>\n";
		
return $sToRender;
}

// show listbox...or the text name
function getDocFieldDisplay($oDocField) {
	global $default;
	if (!isset($oDocField)) {
		$oPatternListBox = & new PatternListBox($default->owl_fields_table, "name", "id", "fDocFieldID");
		//$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);	
		return $oPatternListBox->render();
	} else {
		
		return "<input type=\"hidden\" name=\"fDocFieldID\" value=\"" . $oDocField->iId . "\">\n" .
		"<b>" . $oDocField->getName() . "</b>";
	}
	
}


// show listbox...or the text name
function getDocFieldDataType($oDocField) {
	global $default;
	if (!isset($oDocField)) {
		return "";
	} else {
		return "Data Type: </td><td>" .	$oDocField->getDataType();
	}
	
}

// show check box
function getIsGenericCheckBox($oDocField) {
	if (!isset($oDocField)) {
		return "";
	} else {
		return "Is Generic: </td><td> <input type=\"checkbox\" name=\"fDocFieldIsGeneric\" " . ($oDocField->getIsGeneric() ? "CHECKED " : " ") . " />";
	}
}

// show text box
function getHasLookupCheckBox($oDocField) {
	if (!isset($oDocField)) {
		return "";
	} else {
		return "Has Lookup: </td><td><input type=\"checkbox\" name=\"fDocFieldHasLookup\" " . ($oDocField->getHasLookup() ? "CHECKED " : " ") . " />";
	}
	
}

// show delete button
function getDeleteButton($oDocField) {
	global $default;
	if (!isset($oDocField)) {
		return "Please select a Document Field: ";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" /></td>\n".
		"<td align = right><a href=\"$default->rootUrl/control.php?action=removeDocField\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}
?>
