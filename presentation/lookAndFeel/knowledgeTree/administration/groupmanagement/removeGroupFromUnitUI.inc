<?php
/**
 * $Id$
 *
 * Remove group-unit mapping UI functions.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Mukhtar Dharsey, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.groupmanagement
 */

function getNoUnitPage($fGroupID){
	global $default;
	$sToRender .= "<table><tr><td>\n";
	$sToRender .= "<br>" . _("Group does not belong to an existing Unit.") . "\n";
	$sToRender .= "</td><td></td></tr><tr><td></td><td>\n";
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=editGroupUnit&fGroupID=$fGroupID\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a>\n";
	$sToRender .= "</td></tr></table>\n";
	return $sToRender;
	
}

// get page for removal
function getPage($iGroupID, $iUnitID) {	
	global $default;
	$oGroup = null;
	$oUnit = null;
	
	// change headings if group selected
	if (isset($iGroupID)) {
		$oGroup = Group::get($iGroupID);
		$heading = "<tr><td>" . _("Are you sure you wish to remove the Group from the Unit?") . "</td> </tr>\n";
	}else{
		$heading = "<tr><td>" . _("Please select a Group") . ":</td></tr>\n";
	}
	
	// when unitid is gotten show the name
	if (isset($iUnitID)) {
		$oUnit = Unit::get($iUnitID);
		$unitdisplay = "<td>" . _("Unit Name") . ": " . getUnitDisplay($oUnit) . "</td>\n";
		
	}
	else{
		$unitdisplay = "";
	}
		
	$sToRender .= renderHeading(_("Remove Group From Unit"));
	$sToRender .= "<table>\n";
	$sToRender .= $heading;
	$sToRender .= "<br>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td>" . _("Group Name") . ": " . getGroupDisplay($oGroup) . "</td>\n";
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
		
	$sToRender .= renderHeading(_("Remove Group From Unit"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . _("Sorry, Removal Failed") . "</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeGroupFromUnit\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// if no group selected show selection fail page
function getPageNotSelected() {	
	global $default;
			
	$sToRender .= renderHeading(_("Remove Group From Unit"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . _("Sorry, Selection Failed") . "</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>" . _("Please select a Group") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeGroupFromUnit\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// show success page if removed from unit successfully
function getPageSuccess($iGroupID) {	
	global $default;
			
	$sToRender .= renderHeading(_("Remove Group From Unit"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . _("Success") . "!</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>" . _("The Group was successfully removed from the Unit") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editGroupUnit&fGroupID=$iGroupID\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// show listbox of groups belonging to groups_units_link table
function getGroupDisplay($oGroup) {
	global $default;
	
	if (!isset($oGroup)) {
        $oPatternListBox = & new PatternListBox($default->groups_table, "name", "id", "fGroupID");        
        if (Permission::userIsSystemAdministrator()) {
            $default->log->info("getGroupDisplay sysadmin");
            // if this is the system administrator, prepend group names with unit name
            $oPatternListBox->setFromClause("LEFT OUTER JOIN groups_units_link GUL on ST.id=GUL.group_id " .
                                            "LEFT OUTER JOIN units_lookup UL on GUL.unit_id=UL.id");
            $oPatternListBox->setCompositeDisplayName("DISTINCT COALESCE(CONCAT(CONCAT(UL.name, '-'),ST.name),ST.name)");
        } else if (Permission::userIsUnitAdministrator()) {
            $default->log->info("getGroupDisplay unitadmin");
            // else if this is a unit administrator, only display the groups in your unit
            $oPatternListBox->setFromClause("INNER JOIN $default->groups_units_table GUL on ST.id=GUL.group_id");
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
		//$oPatternListBox = & new PatternListBox($default->units_table, "name", "id", "fUnitID");
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
		return "<td align = right><input type=\"image\" src =\"" . KTHtml::getSelectButton() . "\" value=\"submit\" border=\"0\"/></td>\n";
	} else {
		return "<td align = right><input type=\"image\" src =\"" . KTHtml::getDeleteButton() . "\" value=\"submit\" border=\"0\"/></td>\n";
	}
}

// show cancel button
function getCancelButton($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=groupManagement\"><img src =\"" . KTHtml::getCancelButton() . "\" value=\"cancel\" border = \"0\"/></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=editGroupUnit&fGroupID=" . $oGroup->iId . "\"><img src =\"" . KTHtml::getCancelButton() . "\" border = \"0\" /></a></td>\n";
	}
}


?>
