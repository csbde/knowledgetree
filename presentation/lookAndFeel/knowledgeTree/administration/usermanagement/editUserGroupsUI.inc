<?php
/**
 * $Id$
 *
 * Edit a users groups UI functions.
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

// Create two listbox
function renderGroupPicker($iUserID){
    global $default;

    // change headings if User selected
    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
    }

	$sToRender .= renderHeading(_("Edit User Groups"));

	$sToRender .= "<table width=\"600\" border=0>";
    $sToRender .= "<tr><td width=100%><b>" . _("User Name") . ": </b>" . getUserDisplay($oUser) . "</td><td>";
    $sToRender .= "<a href=\"$default->rootUrl/control.php?action=listUsers\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a>";
    $sToRender .= "</td></tr>\n";
    $sToRender .= "</table>\n";

	$sToRender .= "<table width=\"600\" border=0>";

	// Headings
	$sToRender .= "<tr>";
	$sToRender .= "<td ><strong>" . _("Assigned Groups") . "</strong></td>";
	$sToRender .= "<td></td>";
 	$sToRender .= "<td ><strong>" . _("Available Groups") . "</strong></td>";
	$sToRender .= "</tr>";

	// Initialise structure
	$aInfoGroups = InitGroups($iUserID);

	// Select Boxes	
	$sToRender .= "<tr>";	
	$sToRender .= "<td valign=\"top\" width=1%>\n";
	$sToRender .= "<select name=\"groupSelect\" size=\"10\" multiple>\n";
	// retrieve users' groups
	$sToRender .= getUserGroups($aInfoGroups);
	$sToRender .= "</select></td>";

	// Move buttons
	$sToRender .= "<td align=\"center\">";	
	$sToRender .= "<input TYPE=\"button\" NAME=\"right\" style=\"width:60px\" VALUE=\"- &gt;&gt;\" ONCLICK=\"optGroup.transferRight()\">";
	$sToRender .= "<BR>";
	$sToRender .= "<input TYPE=\"button\" NAME=\"left\" style=\"width:60px\" VALUE=\"&lt;&lt; +\" ONCLICK=\"optGroup.transferLeft()\">";	
	$sToRender .= "</td>";
	
	$sToRender .= "<td valign=\"top\" width=1%>\n";	
	$sToRender .= "<select name=\"chosenGroups\" size=\"10\" multiple>\n";
	// retrieve other groups
	$sToRender .= getOtherGroups($aInfoGroups);
	$sToRender .= "</select></td>";
	$sToRender .= "</tr>";

	// Filter Button
	$sToRender .= "<tr>";	
	$sToRender .= "<td>" . _("Filter") . " <BR><input type=\"text\" name=\"filterUG\" onkeyup=\"optGroup.sortSelectMatch(groupSelect, this.value)\" onchange=\"optGroup.sortSelectMatch(groupSelect, this.value)\"></td>";
	$sToRender .= "<td></td>";
	$sToRender .= "<td>" . _("Filter") . " <BR><input type=\"text\" name=\"filterOG\" onkeyup=\"optGroup.sortSelectMatch(chosenGroups, this.value)\" onchange=\"optGroup.sortSelectMatch(chosenGroups, this.value)\"></td>";
	$sToRender .= "</tr>";

	// Assign/Cancel button
	$sToRender .= "<tr><td align=\"right\" colspan=\"3\">";	
	$sToRender .= "<input type=\"image\" src=\"" . KTHtml::getAssignButton() . "\" border=\"0\" />";
	$sToRender .= "<a href=\"" . $_SERVER["PHP_SELF"] . "?fUserID=" . $iUserID . "\"><img src=\"" . KTHtml::getCancelButton() ."\" border=\"0\"/></a>\n";
	$sToRender .= "</td></tr>";

	
	// Insert all user's groups
	$sBgColor = "#F5F6EE";
	$sToRender .= "<tr>";
	$sToRender .= "<td colspan=\"3\"><strong>" . _("Current Groups") . "</strong><BR>";
	$sToRender .= "<table cellspacing=1 border=0>";
	for ($i=0; $i < count($aInfoGroups[0]); $i++) {
		$oGroup = Group::get($aInfoGroups[0][$i]);
		$sToRender .=  "<tr><td>" . ($i+1) . "</td><td bgcolor=$sBgColor>". $oGroup->getName() ."</td></tr>\n";
	}
	$sToRender .= "</table></td></tr>";

	$sToRender .= "</table>";
	
	$sToRender .= "<input type=\"hidden\" name=\"groupNewLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"groupNewRight\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"groupRemovedLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"groupRemovedRight\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"groupAddedLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"groupAddedRight\"/><br>";

	return $sToRender;
}




/* Initialise some structures to build ui
 * Return an array of:
 *		Current Users Group Ids
 *		UnitAdmin Manageable Group Ids
*/
function InitGroups($iUserID) {
	global $default;

	$aUserGroupIDs = array();
	$aManageableGroupIDs = array();
	
    $oUser = User::get($iUserID);
	$aGroups = GroupUtil::listGroupsForUser($oUser);
    $aGroupIDs = array();
    foreach ($aGroups as $oGroup) {
        $aGroupIDs[] = $oGroup->getID();
    }
	
	if (Permission::userIsUnitAdministrator()) {
		/*ok*/ $sQuery = array("SELECT ST.id FROM " . $default->groups_table . " ST " .
			"INNER JOIN " . $default->groups_units_table . " GUL on ST.id=GUL.group_id " .
			"WHERE GUL.unit_id = ?", User::getUnitID($_SESSION["userID"]));
		$sql = $default->db;
		$sql->query($sQuery);
		
		while ($sql->next_record()) {
			array_push($aManageableGroupIDs, $sql->f("id"));
		}		
	}

    return array($aGroupIDs, $aManageableGroupIDs);
}


/* Get all groups of a user	*/
function getUserGroups($aInfoGroups) {

	$aManageableGroupIDs = array();

	if (Permission::userIsSystemAdministrator()) {
		$aManageableGroupIDs = $aInfoGroups[0];
	} else if (Permission::userIsUnitAdministrator()) {
		$aManageableGroupIDs = array_intersect($aInfoGroups[0], $aInfoGroups[1]);
	}

	foreach ($aManageableGroupIDs as $iGroupID) {
		$oGroup = Group::get($iGroupID);
		$sToRender .= "<OPTION value=\"" . $oGroup->getID() . "\" onDblClick=\"optGroup.transferRight()\" >\n";	
		$sToRender .= $oGroup->getName();
		$sToRender .= "</OPTION>\n";
	}

	return $sToRender;
}




// Get other group
function getOtherGroups($aInfoGroups) {

	$aOtherGroupIDs = array();
	$allGroupIDs = array();

	if (Permission::userIsSystemAdministrator()) {
		$allGroups = Group::getList("ORDER BY name");/*ok*/
		foreach ($allGroups as $oGroup) {
			array_push($allGroupIDs, $oGroup->getID());
		}
		$aOtherGroupIDs = array_diff($allGroupIDs, $aInfoGroups[0]);
		
	} else if (Permission::userIsUnitAdministrator()) {
		$aOtherGroupIDs = array_diff($aInfoGroups[1], $aInfoGroups[0]);
	}

	foreach ($aOtherGroupIDs as $iGroupID) {
		$oGroup = Group::get($iGroupID);
		$sToRender .= "<OPTION value=\"" . $oGroup->getID() . "\" onDblClick=\"optGroup.transferRight()\" >\n";	
		$sToRender .= $oGroup->getName();
		$sToRender .= "</OPTION>\n";
	}

	return $sToRender;
}


// get page for removal
function getPage($iUserID) {
    global $default;
    $oUser = null;
    $oGroup = null;

    // change headings if User selected
    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
    } else {
        $heading = "<tr><td>" . _("Please select a User") . ":</td></tr>\n";
    }

    $sToRender .= renderHeading(_("Edit User Groups"));
    $sToRender .= "<table>\n";
    $sToRender .= $heading;
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td>" . _("User Name") . ": " . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";    
    $sToRender .= "</td></table>\n";
   
    return $sToRender;
}


// show listbox of Users belonging to Users_Groups_link table
function getUserDisplay($oUser) {
    global $default;
    if (!isset($oUser)) {
		$oPatternListBox = & new PatternListBox($default->users_table, "username", "id", "fUserID");
        if (Permission::userIsUnitAdministrator()) {
            $oPatternListBox->setFromClause("INNER JOIN $default->users_groups_table UGL on ST.id=UGL.user_id " .
                                            "INNER JOIN $default->groups_units_table GUL on UGL.group_id=GUL.group_id");
            $oPatternListBox->setWhereClause("GUL.unit_id=" . User::getUnitID($_SESSION["userID"]));
        }
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();	
    } else {
        return "<input type=\"hidden\" name=\"fUserID\" value=\"" . $oUser->iId . "\">\n" .
               $oUser->getUserName();
    }
}

?>
