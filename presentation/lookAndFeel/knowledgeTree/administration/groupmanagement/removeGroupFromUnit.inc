<?php
/**
* Presentation information for adding a group
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

// get page for removal
function getPage($iGroupID, $iUnitID) {	
	global $default;
	$oGroup = null;
	$oUnit = null;
	
	// change headings if group selected
	if (isset($iGroupID)) {
		$oGroup = Group::get($iGroupID);
		$heading = "<tr><td>Are you sure you wish to remove the Group from the Unit?</td> </tr>\n";
	}else{
		$heading = "<tr>Please select a Group: </tr>\n";
	}
	
	// when unitid is gotten show the name
	if (isset($iUnitID)) {
		$oUnit = Unit::get($iUnitID);
		$unitdisplay = "<td>Unit Name: " . getUnitDisplay($oUnit) . "</td>\n";
		
	}
	else{
		$unitdisplay = "";
	}
		
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Select a Group to Remove from its Unit:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= $heading;
	$sToRender .= "<br>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td>Group Name: " . getGroupDisplay($oGroup) . "</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= $unitdisplay;
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td><td>" . getDeleteButton($oGroup);	
	$sToRender .= "<td></td><td>" . getCancelButton($oGroup);
	$sToRender .= "</td></table>\n";
	
	
	return $sToRender;
}

// show paig if deletion fails
function getPageFail($iGroupID) {	
	global $default;
	$oGroup = null;
	$oUnit = null;
		
	if (isset($iGroupID)) {
		$oGroup = Group::get($iGroupID);
	}
	
	$iUnitID = GroupUnitLink::groupBelongsToUnit($iGroupID);
	
	$oUnit= Unit::get($iUnitID);
		
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Sorry, Removal Failed</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeGroupFromUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// if no group selected show selection fail page
function getPageNotSelected() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Sorry, Selection Failed</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>Please select a Group</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeGroupFromUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// show success page if removed from unit successfully
function getPageSuccess() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Success!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The Group was successfully removed from the Unit</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeGroupFromUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// show listbox of groups belonging to groups_units_link table
function getGroupDisplay($oGroup) {
	global $default;
	// added clauses to get those groups belonging to the groups_units_link table
	$from = "," . $default->owl_groups_units_table . " AS GU";
	$where = "(ST.id = GU.group_id)";
	
	
	if (!isset($oGroup)) {
		$oPatternListBox = & new PatternListBox($default->owl_groups_table, "name", "id", "fGroupID");
		$oPatternListBox->setWhereClause($where);
		$oPatternListBox->setFromClause($from);
		//$oPatternListBox->setIncludeDefaultValue(true);
		//$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $oGroup->iId . "\">\n" .
		 $oGroup->getName();
	}
	
}

// show unit details on confirm deletion page
function getUnitDisplay($oUnit) {
	global $default;
	if (!isset($oUnit)) {
		//$oPatternListBox = & new PatternListBox($default->owl_units_table, "name", "id", "fUnitID");
		//$oPatternLiListBoxstBox->setPostBackOnChange(true);
		//return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fUnitID\" value=\"" . $oUnit->iId . "\">\n" .
		$oUnit->getName();
	}
		
}

// show delete button or select button
function getDeleteButton($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" /></td>\n";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" /></td>\n";
	}
}

// show cancel button
function getCancelButton($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		//return "<td align = right><a href=\"$default->rootUrl/control.php?action=groupManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=removeGroupFromUnit\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}


?>