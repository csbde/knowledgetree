<?php
/**
* Presentation information for adding a group
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// get hte edit page if it comes from the add page
function getCreatePage($iGroupID) {
	
	global $default;
	$oGroup = null;
	//if (isset($iGroupID)) {
	//	$oGroup = Group::get($iGroupID);
	//}
	
	$sQuery =  "SELECT * FROM $default->owl_groups_table WHERE id = $iGroupID";
	$aDisplayColumns = array("name","is_sys_admin","is_unit_admin");
	$aStoreColumns = array("name","is_sys_admin","is_unit_admin");
	$aColumnNames = array("Name:","System admin:", "Unit admin:");
	$aDisplayColumnTypes = array(1,2,2);
	$aDatabaseColumnTypes = array(1,2,2);
	
	// get list of group properties...name...text field...is_Sys_admin and is_unit_admin..checkboxes
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
	//$sToRender .= "<td>Assign to a Unit: &nbsp&nbsp" . getUnitDisplay() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	
	$sToRender .= "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"Update\" /></td>\n";	
	$sToRender .= "</table>\n";
		
	
		
	return $sToRender;
	
	
	
}


// if fails ..
function getAddFailPage() {
	
	global $default;
			
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr><b>Add Group Failed:</b></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Sorry, Group Already Exists!</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td align = right><a href=\"$default_rootUrl/control.php?action=addGroup\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
		
	return $sToRender;
	
}

// if its the manual edit page..then display normally
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
	$sToRender .= "<td>Group Name: </td><td>" . getGroupDisplay($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	
	// if a group selected...show checkboxes
	if (isset($iGroupID)) {
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td>Unit Administrator: </td><td>" . getUnitAdminCheckBox($oGroup) . "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td>System Administrator: </td><td>" . getSysAdminCheckBox($oGroup) . "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "</tr>\n";
	}


	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td>" . getUpdateButton($oGroup);	
	$sToRender .= getCancelButton($oGroup);	
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
	$sToRender .= "<tr><td>The Group was successfully modified</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default_rootUrl/control.php?action=editGroup\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
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
	$sToRender .= "<tr><td>The Group modification Failed!</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default_rootUrl/control.php?action=editGroup\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// show the list box when no group selected
// then shows a text box with the group's name
function getGroupDisplay($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		$oPatternListBox = & new PatternListBox($default->owl_groups_table, "name", "id", "fGroupID");
		//$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $oGroup->iId . "\">\n" .
		"<input size = \"40\" type=\"text\" name=\"fGroupName\" value=\"" . $oGroup->getName() . "\">";
	}
	
}


// returns the checkbox ticked or unticked
function getUnitAdminCheckBox($oGroup) {
	if (!isset($oGroup)) {
		return "";
	} else {
		return "<input type=\"checkbox\" name=\"fGroupUnitAdmin\" " . ($oGroup->getUnitAdmin() ? "CHECKED " : " ") . " />";
	}
}

// returns the checkbox ticked or unticked
function getSysAdminCheckBox($oGroup) {
	if (!isset($oGroup)) {
		return "";
	} else {
		return "<input type=\"checkbox\" name=\"fGroupSysAdmin\" " . ($oGroup->getSysAdmin() ? "CHECKED " : " ") . " />";
	}
	
}

// gets the updatebutton when a group is selected
function getUpdateButton($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		return "Please select a group: ";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"submit\" /></td>\n";
	}
}

// gets the cancel button when a group is selected
function getCancelButton($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		//return "<td align = right><a href=\"$default_rootUrl/control.php?action=groupManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default_rootUrl/control.php?action=editGroup\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}
?>