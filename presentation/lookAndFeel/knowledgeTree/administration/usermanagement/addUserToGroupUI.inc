<?php
/**
 * $Id$
 *
 * Add a user to a group UI functions.
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
 * @package administration.usermanagement
 */
// gets main page
function getPage($iUserID, $iGroupID) {	
	global $default;
	$oUser = null;
	$oGroup = null;
	
	if (isset($iUserID)) {
		$oUser = User::get($iUserID);		
	}
	
	if (isset($iGroupID)) {
		$oGroup = Group::get($iGroupID);
		$heading = "<tr><td colspan=\"2\">" . _("Please confirm your choice by clicking 'Assign', or 'Cancel' to abort.") . "</td></tr>\n";		
	} else {
		$heading = "<tr><td colspan=\"2\">" . _("Please choose the Group to assign to this user.") . "</td></tr>\n";
	}
	$sQueryString = "&fUserID=$iUserID";
    
    $sToRender .= renderHeading(_("Add User to Group"));
	$sToRender .= "<table>\n";
	$sToRender .= $heading;
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<td>" . _("User Name") . ": </td><td><input type=hidden id=\"fUserID\" name=\"fUserID\" value=\"" . $oUser->getID() . "\">" .  $oUser->getName() . "</a></td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Group Name") . ": </td><td>" . getGroupDisplay($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td><td>" . getAssignButton($oUser);
	$sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=editUserGroups$sQueryString\"><img src =\"" . KTHtml::getCancelButton() . "\" border = \"0\" /></a></td>";		
	$sToRender .= "</td></table>\n";
	
	
	return $sToRender;
}


// page that comes up when there is nothing selected
function getPageNotSelected($sQueryString) {	
	global $default;

    $sToRender .= renderHeading(_("Add User to Group"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . _("Sorry, Selection Failed") . "</b></td></tr>\n";
	$sToRender .= "<tr><td>" . _("Please select a Group") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUserToGroup$sQueryString\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

//if addition successful
function getPageSuccess($sQueryString) {
	global $default;

    $sToRender .= renderHeading(_("Add User to Group"));            
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . _("Success") . "!</b></td></tr>\n";
	$sToRender .= "<tr><td>" . _("The User was successfully assigned to the specified Group") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUserToGroup$sQueryString\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

//if edition successful
function getPageFail($sQueryString) {
	global $default;
		
    $sToRender .= "<table border=\"0\" width=\"600\">" . renderHeading(_("Add User to Group"));
    $sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . strtoupper(_("Failure")) . "!</b></td></tr>\n";
	$sToRender .= "<tr><td>" . _("The User already belongs to the specified Group") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUserToGroup$sQueryString\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// display the listbox initially ..then just display the text
function getUserDisplay($oUser) {
	global $default;
	if (!isset($oUser)) {
        $oPatternListBox = & new PatternListBox($default->users_table, "username", "id", "fUserID");
        if (Permission::userIsUnitAdministrator() && !Permission::userIsSystemAdministrator()) {
            $oPatternListBox->setFromClause("INNER JOIN $default->users_groups_table UGL ON ST.id=UGL.user_id " .
                                            "INNER JOIN $default->groups_units_table GUL ON UGL.group_id=GUL.group_id");
            $oPatternListBox->setWhereClause("GUL.unit_id=" . User::getUnitID($_SESSION["userID"]));
            
            $oUnassignedUsersLB = & new PatternListBox($default->users_table, "username", "id", "fUserID");
            $oUnassignedUsersLB->setFromClause("LEFT OUTER JOIN $default->users_groups_table UGL ON ST.id=UGL.user_id");
            $oUnassignedUsersLB->setWhereClause("ISNULL(UGL.group_id)");
            $oPatternListBox->setAdditionalEntries($oUnassignedUsersLB->getEntries());
        }
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fUserID\" value=\"" . $oUser->iId . "\">\n" .
		 $oUser->getUserName();
	}
	
}

// display the listbox initially ..then just display the text
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
        }
        return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $oGroup->iId . "\">\n" .
		$oGroup->getName();
	}
		
}

// show button after User selected
function getAssignButton($oUser) {
	global $default;
	if (!isset($oUser)) {
		return "<td align = right><input type=\"image\" src =\"" . KTHtml::getSelectButton() . "\" value=\"submit\" border=\"0\"/></td>\n";
	} else {
		return "<td align = right><input type=\"image\" src =\"" . KTHtml::getAssignButton() . "\" value=\"submit\" border=\"0\"/></td>\n";
	}
}
// show button after User selected
function getCancelButton($oUser) {
	global $default;
	if (!isset($oUser)) {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=userManagement\"><img src =\"" . KTHtml::getCancelButton() . "\" value=\"cancel\" border=\"0\"/></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=addUserToGroup\"><img src =\"" . KTHtml::getCancelButton() . "\" border=\"0\" /></a></td>\n";
	}
}


?>
