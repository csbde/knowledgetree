<?php
/**
 * $Id$
 *
 * Edit a users groups UI functions.
 *
 * Copyright (c) 2004 Jam Warehouse http://www.jamwarehouse.com
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
 * @package administration.groupmanagement
 */

// Create two listbox
function renderGroupPicker($iGroupID){
    global $default;

    // change headings if User selected
    if (isset($iGroupID)) {
        $oGroup = Group::get($iGroupID);
    }
	
	$sToRender .= renderHeading(_("Edit User's Groups"));

	$sToRender .= "<table width=\"600\" border=0>";
    $sToRender .= "<tr><td width=100%><b>" . _("Group Name") . ": </b>" . getGroupDisplay($oGroup) . "</td><td>";
    $sToRender .= "<a href=\"$default->rootUrl/control.php?action=listGroups\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a>";
    $sToRender .= "</td></tr>\n";
    $sToRender .= "</table>\n";

	$sToRender .= "<table width=\"600\" border=0>";

	// Headings
	$sToRender .= "<tr>";
	$sToRender .= "<td><strong>Assigned User to</strong></td>";
	$sToRender .= "<td></td>";
 	$sToRender .= "<td><strong>Available Users</strong></td>";
	$sToRender .= "</tr>";

	// Initialise structure
	$aInfoUsers = InitUsers($oGroup);


	// Select Boxes	
	$sToRender .= "<tr>";	
	$sToRender .= "<td valign=\"top\" width=1%>\n";
	$sToRender .= "<select name=\"userSelect\" size=\"10\" multiple>\n";
	// retrieve users' groups
	$sToRender .= getGroupUsers($aInfoUsers);
	$sToRender .= "</select></td>";

	// Move buttons
	$sToRender .= "<td align=\"center\">";	
	$sToRender .= "<input TYPE=\"button\" NAME=\"right\" style=\"width:60px\" VALUE=\"- &gt;&gt;\" ONCLICK=\"optUser.transferRight()\">";
	$sToRender .= "<BR>";
	$sToRender .= "<input TYPE=\"button\" NAME=\"left\" style=\"width:60px\" VALUE=\"&lt;&lt; +\" ONCLICK=\"optUser.transferLeft()\">";	
	$sToRender .= "</td>";
	
	$sToRender .= "<td valign=\"top\" width=1%>\n";	
	$sToRender .= "<select name=\"chosenUsers\" size=\"10\" multiple>\n";
	// retrieve other groups
	$sToRender .= getOtherUsers($aInfoUsers);
	$sToRender .= "</select></td>";
	$sToRender .= "</tr>";

	// Filter Button
	$sToRender .= "<tr>";	
	$sToRender .= "<td>Filter <BR><input type=\"text\" name=\"filterUG\" onkeyup=\"optUser.sortSelectMatch(userSelect, this.value)\" onchange=\"optGroup.sortSelectMatch(userSelect, this.value)\"></td>";
	$sToRender .= "<td></td>";
	$sToRender .= "<td>Filter <BR><input type=\"text\" name=\"filterOG\" onkeyup=\"optUser.sortSelectMatch(chosenUsers, this.value)\" onchange=\"optGroup.sortSelectMatch(chosenUsers, this.value)\"></td>";
	$sToRender .= "</tr>";

	// Assign/Cancel button
	$sToRender .= "<tr><td align=\"right\" colspan=\"3\">";	
    $sToRender .= "<input type=\"image\" src=\"". KTHtml::getAssignButton() . "\" border=\"0\" />";
	$sToRender .= "<a href=\"" . $_SERVER["PHP_SELF"] . "?fGroupID=" . $iGroupID . "\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"/></a>\n";
	$sToRender .= "</td></tr>";

	
	// Insert all user's groups
	$sBgColor = "#F5F6EE";
	$sToRender .= "<tr>";
	$sToRender .= "<td colspan=\"3\"><strong>" . _("Current Users") . "</strong><BR>";
	$sToRender .= "<table cellspacing=1 border=0>";
	for ($i=0; $i < count($aInfoUsers[0]); $i++) {
		$oUser = User::get($aInfoUsers[0][$i]);
		$sToRender .=  "<tr><td>" . ($i+1) . "</td><td bgcolor=$sBgColor>". $oUser->getName() ."</td></tr>\n";
	}
	$sToRender .= "</table></td></tr>";

	$sToRender .= "</table>";
	
	$sToRender .= "<input type=\"hidden\" name=\"userNewLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"userNewRight\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"userRemovedLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"userRemovedRight\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"userAddedLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"userAddedRight\"/><br>";	
	return $sToRender;
}




/* Initialise some structures to build ui
 * Return an array of:
 *		Current Users assigned to Group
 *		UnitAdmin Manageable User Ids
*/
function InitUsers($oGroup) {
	global $default;

	$aUsersGroup = array();
	$aUserGroupIDs = array();
	$aManageableUsers = array();
	$aManageableUserIDs = array();

	// Current Users assigned to Group
	$aUsersGroup = $oGroup->getUsers();
	foreach ($aUsersGroup as $tmp) {
		array_push($aUserGroupIDs, $tmp->getID());
	}
	
	// Manageable Users
	if (Permission::userIsSystemAdministrator()) {
		$aManageableUsers = User::getList();/*ok*/
		foreach ($aManageableUsers as $tmp) {
			array_push($aManageableUserIDs, $tmp->getID());
		}
		
	} elseif (Permission::userIsUnitAdministrator()) {
        $aIds = User::getUnitIDs($_SESSION["userID"]);
        $iNumIds = count($aIds);
        $sQms = join(",", array_fill(0, $iNumIds, '?'));
        
		/*ok*/ $sQuery = array("SELECT DISTINCT U.id as userID " .
				"FROM $default->users_table U " . 
				"LEFT OUTER JOIN $default->users_groups_table UGL ON U.id = UGL.user_id " .
				"LEFT OUTER JOIN $default->groups_table GL ON UGL.group_id = GL.id " .
				"INNER JOIN $default->groups_units_table GUL on GL.id=GUL.group_id " .
				"WHERE GUL.unit_id IN ($sQms)", $aIds);
		$sql = $default->db;
		$sql->query($sQuery);

		while ($sql->next_record()) {
			array_push($aManageableUserIDs, $sql->f("userID"));
		}
	}

	return array($aUserGroupIDs, $aManageableUserIDs);
}


/* Get all Users in a Group */
function getGroupUsers($aInfoUsers) {

	foreach ($aInfoUsers[0] as $iUserID) {
		$oUser = User::get($iUserID);
		$sToRender .= "<OPTION value=\"" . $oUser->getID() . "\" onDblClick=\"optUser.transferRight()\" >\n";	
		$sToRender .= $oUser->getName();
		$sToRender .= "</OPTION>\n";
	}

	return $sToRender;
}


// Get other Users
function getOtherUsers($aInfoUsers) {

	$aOtherUserIDs = array();

	$aOtherUserIDs = array_diff($aInfoUsers[1], $aInfoUsers[0]);

	foreach ($aOtherUserIDs as $iUserID) {
		$oUser = User::get($iUserID);
		$sToRender .= "<OPTION value=\"" . $oUser->getID() . "\" onDblClick=\"optUser.transferRight()\" >\n";	
		$sToRender .= $oUser->getName();
		$sToRender .= "</OPTION>\n";
	}

	return $sToRender;
}



// get page for removal
function getPage($iGroupID) {
    global $default;
    $oUser = null;
    $oGroup = null;

    // change headings if Group selected
    if (isset($iGroupID)) {
        $oGroup = Group::get($iGroupID);
    } else {
        $heading = "<tr><td>" . _("Please select a Group") . ":</td></tr>\n";
    }

    $sToRender .= renderHeading(_("Edit User's Groups"));
    $sToRender .= "<table>\n";
    $sToRender .= $heading;
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td>" . _("Group Name") . ": " . getGroupDisplay($oGroup) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";    
    $sToRender .= "</td></table>\n";
   
    return $sToRender;
}

// page that comes up when there is nothing selected
function getPageNotSelected() {
	global $default;
 
	$sToRender .= renderHeading(_("Edit Users's Group"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . _("Sorry, Selection Failed") . "</b></td></tr>\n";
	$sToRender .= "<tr><td>" . _("Please select a Group") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listGroups\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";

	return $sToRender;
}


// show listbox of Users belonging to Users_Groups_link table
function getGroupDisplay($oGroup) {
    global $default;
    if (!isset($oGroup)) {
        $oPatternListBox = & new PatternListBox($default->groups_table, "name", "id", "fGroupID");
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $oGroup->getID() . "\">\n" .
               $oGroup->getName();
    }
}

?>
