<?php
/**
* Presentation information for renaming a unit
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

// if its the manual edit page..then display normally
function getEditPage($iUnitID) {	
	global $default;
	$oUnit = null;
	if (isset($iUnitID)) {
		$oUnit = Unit::get($iUnitID);
    }
	
	$sToRender .= "<table width=\"600\">" . renderHeading("Edit Unit") . "</table>";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td>Please select a unit:</td></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Unit Name: </td><td>" . getUnitDisplay($oUnit) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr><td colspan=\"2\" align = right>" . getUpdateButton($oUnit) . getCancelButton($oUnit) . "</td></tr>";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

// if edition is successful print otu success page
function getEditPageSuccess() {	
	global $default;
			
	$sToRender .= "<table width=\"600\">" . renderHeading("Edit Unit") . "</table>";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>Success!</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The Unit was successfully modified</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// gets the page if edition fails
function getEditPageFail() {	
	global $default;
			
	$sToRender .= "<table width=\"600\">" . renderHeading("Edit Unit") . "</table>";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>FAILED!</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>Unit modification Failed!</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

// show the list box when no unit selected
// then shows a text box with the unit's name
function getUnitDisplay($oUnit) {
	global $default;
	if (!isset($oUnit)) {
		$oPatternListBox = & new PatternListBox($default->owl_units_table, "name", "id", "fUnitID");
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fUnitID\" value=\"" . $oUnit->iId . "\">\n" .
		"<input size = \"40\" type=\"text\" name=\"fUnitName\" value=\"" . $oUnit->getName() . "\">";
	}
}


// gets the updatebutton when a unit is selected
function getUpdateButton($oUnit) {
	global $default;
	if (isset($oUnit)) {
		return "<input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"submit\" border=\"0\"/>\n";
	}
}

// gets the cancel button when a unit is selected
function getCancelButton($oUnit) {
	global $default;
	if (!isset($oUnit)) {
		return "<a href=\"$default->rootUrl/control.php?action=unitManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" border=\"0\"/></a>\n";
	} else {
		return "<a href=\"$default->rootUrl/control.php?action=editUnit\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a>\n";
	}
}
?>