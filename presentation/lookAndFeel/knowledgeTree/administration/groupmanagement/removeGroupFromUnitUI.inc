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
		$heading = "<tr><td>Please select a Group:</td></tr>\n";
	}
	
	// when unitid is gotten show the name
	if (isset($iUnitID)) {
		$oUnit = Unit::get($iUnitID);
		$unitdisplay = "<td>Unit Name: " . getUnitDisplay($oUnit) . "</td>\n";
		
	}
	else{
		$unitdisplay = "";
	}
		
	$sToRender .= "<table width=\"600\">" . renderHeading("Remove Group From Unit") . "</table>";
	$sToRender .= "<table>\n";
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
		
	$sToRender .= "<table width=\"600\">" . renderHeading("Remove Group From Unit") . "</table>";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>Sorry, Removal Failed</b></td></tr>\n";
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
			
	$sToRender .= "<table width=\"600\">" . renderHeading("Remove Group From Unit") . "</table>";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>Sorry, Selection Failed</b></td></tr>\n";
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
			
	$sToRender .= "<table width=\"600\">" . renderHeading("Remove Group From Unit") . "</table>";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>Success!</b></td></tr>\n";
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
	
	if (!isset($oGroup)) {
        $oPatternListBox = & new PatternListBox($default->owl_groups_table, "name", "id", "fGroupID");        
        if (Permission::userIsSystemAdministrator()) {
            $default->log->info("getGroupDisplay sysadmin");
            // if this is the system administrator, prepend group names with unit name
            $oPatternListBox->setFromClause("LEFT OUTER JOIN groups_units_link GUL on ST.id=GUL.group_id " .
                                            "LEFT OUTER JOIN units_lookup UL on GUL.unit_id=UL.id");
            $oPatternListBox->setCompositeDisplayName("DISTINCT COALESCE(CONCAT(CONCAT(UL.name, '-'),ST.name),ST.name)");
        } else if (Permission::userIsUnitAdministrator()) {
            $default->log->info("getGroupDisplay unitadmin");
            // else if this is a unit administrator, only display the groups in your unit
            $oPatternListBox->setFromClause("INNER JOIN $default->owl_groups_units_table GUL on ST.id=GUL.group_id");
            $oPatternListBox->setWhereClause("GUL.unit_id=" . User::getUnitID($_SESSION["userID"]));
        }
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
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" border=\"0\"/></td>\n";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" border=\"0\"/></td>\n";
	}
}

// show cancel button
function getCancelButton($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=groupManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" border = \"0\"/></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=removeGroupFromUnit\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}


?>