<?php
/**
* Presentation information for renaming a Org
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

// if its the manual edit page..then display normally
function getEditPage($iOrgID) {	
	global $default;
	$oOrg = null;
	if (isset($iOrgID)) {
		$oOrg = Organisation::get($iOrgID);
		
	}
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Edit Organisation Properties:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Organisation Name: </td><td>" . getOrgDisplay($oOrg) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td>" . getUpdateButton($oOrg);	
	$sToRender .= getCancelButton($oOrg);	
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
	$sToRender .= "<tr><td>The Organisation was successfully modified</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default_owl_root_url/control.php?action=editOrg\"><img src =\"$default->owl_graphics_url/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// gets the page if edition fails
function getEditPageFail() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Success!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>Organisation modification Failed!</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default_owl_root_url/control.php?action=editOrg\"><img src =\"$default->owl_graphics_url/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// show the list box when no Org selected
// then shows a text box with the Org's name
function getOrgDisplay($oOrg) {
	global $default;
	if (!isset($oOrg)) {
		$oPatternListBox = & new PatternListBox($default->owl_organisations_table, "name", "id", "fOrgID");
		//$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fOrgID\" value=\"" . $oOrg->iId . "\">\n" .
		"<input size = \"40\" type=\"text\" name=\"fOrgName\" value=\"" . $oOrg->getName() . "\">";
	}
	
}


// gets the updatebutton when a Org is selected
function getUpdateButton($oOrg) {
	global $default;
	if (!isset($oOrg)) {
		return "Please select a Organisation: ";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->owl_graphics_url/widgets/update.gif\" value=\"submit\" /></td>\n";
	}
}

// gets the cancel button when a Org is selected
function getCancelButton($oOrg) {
	global $default;
	if (!isset($oOrg)) {
		//return "<td align = right><a href=\"$default_owl_root_url/control.php?action=groupManagement\"><img src =\"$default->owl_graphics_url/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default_owl_root_url/control.php?action=editOrg\"><img src =\"$default->owl_graphics_url/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}
?>