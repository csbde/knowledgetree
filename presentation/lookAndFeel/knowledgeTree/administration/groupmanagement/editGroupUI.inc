<?php
/**
 * $Id$
 *
 * Edit a group UI functions.
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

// get hte edit page if it comes from the add page
function getCreatePage($iGroupID) {
	
	global $default;
	$oGroup = null;
	
	/*ok*/ $sQuery = array("SELECT * FROM $default->groups_table WHERE id = ?", $iGroupID);
	// #2978 if you're not a system administrator, you can't add a sys admin group
	// FIXME: there must be a more elegant way to do this?  possibly some exclusions to the pattern 
	if (Permission::userIsSystemAdministrator()) {
		$aDisplayColumns = array("name","is_sys_admin","is_unit_admin");
		$aStoreColumns = array("name","is_sys_admin","is_unit_admin");
		$aColumnNames = array(_("Name") . ":", _("System Administrator") . ":", _("Unit Administrator") . ":");
		$aDisplayColumnTypes = array(1,2,2);
		$aDatabaseColumnTypes = array(1,2,2);
	} else {
		$aDisplayColumns = array("name","is_unit_admin");
		$aStoreColumns = array("name","is_unit_admin");
		$aColumnNames = array(_("Name") . ":", _("Unit Administrator") . ":");
		$aDisplayColumnTypes = array(1,2);
		$aDatabaseColumnTypes = array(1,2);		
	}
	
	// get list of group properties...name...text field...is_Sys_admin and is_unit_admin..checkboxes
	$oPattern = & new PatternEditableListFromQuery($sQuery, $default->groups_table, $aDisplayColumns, $aStoreColumns, $aColumnNames, $aDisplayColumnTypes, $aDatabaseColumnTypes);
	$oPattern->setUniqueName("groupEdit");
	$oPattern->setColumnsRequired(array(1,0,0));
		
	$sToRender .= renderHeading(_("Add Group Properties"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPattern->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	
	$sToRender .= "<td align=\"right\"><input type=\"image\" src=\"" . KTHtml::getUpdateButton() . "\" value=\"Update\" border=\"0\"/></td>\n";	
	$sToRender .= "</table>\n";
	return $sToRender;
}


// if fails ..
function getAddFailPage() {
	
	global $default;
    
	$sToRender .= renderHeading(_("Edit Group Properties"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . _("Add Group Failed") . ":</b></td></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Sorry, Group Already Exists!") . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=listGroups\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
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
	
    $sToRender .= renderHeading(_("Edit Group Properties"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Group Name") . ": </td><td>" . getGroupDisplay($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	
	// if a group selected...show checkboxes
	if (isset($iGroupID)) {
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td>" . _("Unit Administrator") . ": </td><td>" . getUnitAdminCheckBox($oGroup) . "</td>\n";
		$sToRender .= "</tr>\n";
		if (Permission::userIsSystemAdministrator()) {
			$sToRender .= "<tr>\n";
			$sToRender .= "<td>" . _("System Administrator") . ": </td><td>" . getSysAdminCheckBox($oGroup) . "</td>\n";
			$sToRender .= "</tr>\n";
		}
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
			
	$sToRender .= renderHeading(_("Edit Group Properties"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . _("Success") . "!</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>" . _("The Group was successfully modified") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listGroups\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// gets the page if edition fails
function getEditPageFail() {	
	global $default;
			
    $sToRender .= renderHeading(_("Edit Group Properties"));

	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . _("Failure") . "!</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>" . _("The Group modification Failed!") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listGroups\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// show the list box when no group selected
// then shows a text box with the group's name
function getGroupDisplay($oGroup) {
	global $default;
	if (!isset($oGroup)) {
        if (Permission::userIsSystemAdministrator()) {
            // if this is the system administrator, prepend group names with unit name
            $oPatternListBox = & new PatternListBox($default->groups_table, "name", "id", "fGroupID");
            $oPatternListBox->setFromClause("LEFT OUTER JOIN groups_units_link GUL on ST.id=GUL.group_id " .
                                            "LEFT OUTER JOIN units_lookup UL on GUL.unit_id=UL.id");
            $oPatternListBox->setCompositeDisplayName("DISTINCT COALESCE(CONCAT(CONCAT(UL.name, '-'),ST.name),ST.name)");
        } else if (Permission::userIsUnitAdministrator()) {
            // else if this is a unit administrator, only display the groups in your unit
            $oPatternListBox = & new PatternListBox($default->groups_table, "name", "id", "fGroupID");
            $oPatternListBox->setFromClause("INNER JOIN $default->groups_units_table GUL on ST.id=GUL.group_id");
            $oPatternListBox->setWhereClause("GUL.unit_id=" . User::getUnitID($_SESSION["userID"]));
            
            $oUnassignedUsersLB = & new PatternListBox($default->groups_table, "name", "id", "fGroupID");
            $oUnassignedUsersLB->setFromClause("LEFT OUTER JOIN $default->groups_units_table GUL ON ST.id=GUL.group_id");
            $oUnassignedUsersLB->setWhereClause("ISNULL(GUL.unit_id) AND ST.name <> 'Anonymous' AND ST.name <> 'System Administrators'");
            $oPatternListBox->setAdditionalEntries($oUnassignedUsersLB->getEntries());            
        }

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
		return _("Please select a Group") . ": ";
	} else {
		return "<td align = right><input type=\"image\" src =\"" . KTHtml::getUpdateButton() . "\" value=\"submit\" border=\"0\"/></td>\n";
	}
}

// gets the cancel button when a group is selected
function getCancelButton($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=groupManagement\"><img src =\"" . KTHtml::getCancelButton() . "\" value=\"cancel\" border=\"0\"/></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=listGroups\"><img src =\"" . KTHtml::getCancelButton() . "\" border=\"0\"/></a></td>\n";
	}
}
?>
