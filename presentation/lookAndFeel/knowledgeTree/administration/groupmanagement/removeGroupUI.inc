<?php

/**
* Presentation information for Deleting a group 
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getDeletePage($iGroupID) {	
	global $default;
	$oGroup = null;
	if (isset($iGroupID)) {
		$oGroup = Group::get($iGroupID);
		$sToRender .= "<br></br>\n";
		$sToRender .= "<b>Are you sure you wish to Remove this Group?:</b>\n";
	}
	else{
		$sToRender .= "<br></br>\n";		
		$sToRender .= "<b>Remove Group From System:</b>\n";
	}
	
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getGroupDisplay($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getUnitAdminCheckBox($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getSysAdminCheckBox($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= getDeleteButton($oGroup);	
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

function getGroupDisplay($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		$oPatternListBox = & new PatternListBox($default->owl_groups_table, "name", "id", "fGroupID");
		$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $oGroup->iId . "\">\n" .
		"Group Name: <b>" . $oGroup->getName() . "</b>";
	}
	
}

function getUnitAdminCheckBox($oGroup) {
	if (!isset($oGroup)) {
		return "";
	} else {
		return "Unit Administrator: <input type=\"checkbox\" name=\"fGroupUnitAdmin\" " . ($oGroup->getUnitAdmin() ? "CHECKED " : " ") . " />";
	}
}

function getSysAdminCheckBox($oGroup) {
	if (!isset($oGroup)) {
		return "";
	} else {
		return "System Administrator: <input type=\"checkbox\" name=\"fGroupSysAdmin\" " . ($oGroup->getSysAdmin() ? "CHECKED " : " ") . " />";
	}
	
}


function getDeleteButton($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		return "Please select a group";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->owl_graphics_url/widgets/Delete.gif\" value=\"submit\" /></td>\n".
		"<td align = right><a href=\"$default_owl_root_url/control.php?action=removeGroup\"><img src =\"$default->owl_graphics_url/widgets/Cancel.gif\" border = \"0\" /></a></td>\n";
	}
}
?>
