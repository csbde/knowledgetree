<?php
/**
* Presentation information for adding a group
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

        function getCreatePage($iGroupID) {
	
	global $default;
	$oGroup = null;
	//if (isset($iGroupID)) {
	//	$oGroup = Group::get($iGroupID);
	//}
	
	$sQuery =  "SELECT * FROM $default->owl_groups_table WHERE id = $iGroupID";
	$aDisplayColumns = array("name","is_sys_admin","is_unit_admin");
	$aStoreColumns = array("name","is_sys_admin","is_unit_admin");
	$aColumnNames = array("Name","System admin", "Unit admin");
	$aDisplayColumnTypes = array(1,2,2);
	$aDatabaseColumnTypes = array(1,2,2);
	
	$oPattern = & new PatternEditableListFromQuery($sQuery, $default->owl_groups_table, $aDisplayColumns, $aStoreColumns, $aColumnNames, $aDisplayColumnTypes, $aDatabaseColumnTypes) ;
	$oPattern->setUniqueName("groupEdit");
	$oPattern->setColumnsRequired(array(1,0,0));
		
	$sToRender .= "<b>Edit Group Properties:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPattern->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	//$sToRender .="<input type=\"hidden\" name=\"fGroupName\" value=\"" . $oGroup->setName() . "\"";
	$sToRender .= "</tr>\n";
	
	$sToRender .= "<td align = right><input type=\"image\" src =\"$default->owl_graphics_url/widgets/update.gif\" value=\"Update\" /></td>\n";	
	$sToRender .= "</table>\n";
		
	
		
	return $sToRender;
	
	
	
}

function getEditPage($iGroupID) {	
	global $default;
	$oGroup = null;
	if (isset($iGroupID)) {
		$oGroup = Group::get($iGroupID);
	}
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Edit Group Properties:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getGroupDisplay($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getUnitAdminCheckBox($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getSysAdminCheckBox($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= getUpdateButton($oGroup);	
	$sToRender .= getCancelButton($oGroup);	
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
		"<input type=\"text\" name=\"fGroupName\" value=\"" . $oGroup->getName() . "\"";
	}
	
}

function getUnitAdminCheckBox($oGroup) {
	if (!isset($oGroup)) {
		return "";
	} else {
		return "<input type=\"checkbox\" name=\"fGroupUnitAdmin\" " . ($oGroup->getUnitAdmin() ? "CHECKED " : " ") . " />";
	}
}

function getSysAdminCheckBox($oGroup) {
	if (!isset($oGroup)) {
		return "";
	} else {
		return "<input type=\"checkbox\" name=\"fGroupSysAdmin\" " . ($oGroup->getSysAdmin() ? "CHECKED " : " ") . " />";
	}
	
}

function getUpdateButton($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		return "Please select a group";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->owl_graphics_url/widgets/update.gif\" value=\"update\" /></td>\n";
	}
}

function getCancelButton($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		return "<td align = right><a href=\"$default_owl_root_url/control.php?action=browse\"><img src =\"$default->owl_graphics_url/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default_owl_root_url/control.php?action=editGroup\"><img src =\"$default->owl_graphics_url/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
	}
}
?>