<?php
/**
 * $Id$
 *
 * Map a group to a unit UI functions.
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

function getGotGroupPage($iGroupID,$iUnitID){
	global $default;
    $oGroup = null;
    $oUnit = null;

    if (isset($iGroupID)) {
        $oGroup = Group::get($iGroupID);
        //$heading = "<tr><td colspan=\"2\"><b>Group Name: <b>" . $oGroup->getName() . "</td></tr>\n";
    } else {
        if (Permission::userIsUnitAdministrator()) {
            $heading = "<tr><td colspan=\"2\">" . _("Please Assign a Group to your Unit:") . "</td></tr>\n";
        } else {
            $heading = "<tr><td colspan=\"2\">" . _("Please Assign a Group to a Unit:") . "</td></tr>\n";
        }
    }
    
    if (isset($iUnitID)) {
        $oUnit = Unit::get($iUnitID);
    }

    $sToRender .= renderHeading("Assign Group to Unit");
    $sToRender .= "<table>\n";
    $sToRender .= $heading;
    $sToRender .= "<br>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td>" . _("Group Name") . ": </td><td>" . getGroupDisplay($oGroup) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Unit Name") . ": </td><td>" . getUnitDisplay($oUnit) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td></td><td>" . getAssignButton($oGroup);
    $sToRender .= getCancelButton($oGroup);
    $sToRender .= "</td></table>\n";

    $sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {;\n";
	$sToRender .= "\tif (!(validRequired(theForm.fGroupID, 'Group'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";
	// #3543 patched by stefano.ciancio@mail.wind.it	
	$sToRender .= "\tif (!(validRequired(theForm.fUnitID,'Unit'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";
	$sToRender .= "return true;\n}\n";		
	$sToRender .= "-->\n</script>\n\n";

    return $sToRender;
	
}

// gets main page
function getPage($iGroupID, $iUnitID) {
    global $default;
    
    $oGroup = Group::get($iGroupID);
    if (isset($iUnitID)) {
        $oUnit = Unit::get($iUnitID);
    }

    if ($oUnit) {
        $heading = "<tr><td colspan=\"2\">" . _("Are you sure you wish to assign the Group to the Unit?") . "</td></tr>\n";
    } else {
        if (Permission::userIsUnitAdministrator() && !Permission::userIsSystemAdministrator()) {
            $heading = "<tr><td colspan=\"2\">" . _("Please Assign a Group to your Unit:") . "</td></tr>\n";
        } else {
            $heading = "<tr><td colspan=\"2\">" . _("Please Assign a Group to a Unit:") . "</td></tr>\n";
        }
    }

    $sToRender .= renderHeading("Assign Group to Unit");
    $sToRender .= "<table>\n";
    $sToRender .= $heading;
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td>" . _("Group Name") . ": </td><td>" . getGroupDisplay($oGroup) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Unit Name") . ": </td><td>" . getUnitDisplay($oUnit) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td></td><td>" . getAssignButton($oGroup);
    $sToRender .= getCancelButton($oGroup);
    $sToRender .= "</td></table>\n";

    $sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {;\n";
	$sToRender .= "\tif (!(validRequired(theForm.fGroupID, 'Group'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";
	// #3543 patched by stefano.ciancio@mail.wind.it
	$sToRender .= "\tif (!(validRequired(theForm.fUnitID,'Unit'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";	
	$sToRender .= "return true;\n}\n";		
	$sToRender .= "-->\n</script>\n\n";

    return $sToRender;
}

// page when failure occurred
function getPageFail($iGroupID) {
    global $default;
    $oGroup = null;
    $oUnit = null;

    if (isset($iGroupID)) {
        $oGroup = Group::get($iGroupID);
    }
    // find otu which unit the group belogns to
    $iUnitID = GroupUnitLink::groupBelongsToUnit($iGroupID);
    // get the id of that unit
    $oUnit= Unit::get($iUnitID);

    $sToRender .= renderHeading("Assign Group to Unit");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>" . _("Sorry, Assignment Failed") . "</b></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>" . _("Group Already Belongs to A Unit") . "\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Group Name") . ": </td><td><b>" .  $oGroup->getName() . "</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Unit Name") . ": </td><td><b>" . $oUnit->getName($iUnitID) . "</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listGroups\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";

    $sToRender .= "</table>\n";


    return $sToRender;
}

// page that comes up when there is nothing selected
function getPageNotSelected() {
    global $default;

    $sToRender .= renderHeading("Assign Group to Unit");
    $sToRender .= "<table border=1>\n";
    $sToRender .= "<tr><td><b>" . _("Sorry, Selection Failed") . "</b></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>" . _("Please select BOTH a Group AND a Unit") . "</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listGroups\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}

//if edition successful
function getPageSuccess() {
    global $default;

    $sToRender .= renderHeading("Assign Group to Unit");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>" . _("Success") . "!</b></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>" . _("The Group was successfully assigned to the specified Unit") . "</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listGroups\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}

// display the listbox initially ..then just display the text
function getGroupDisplay($oGroup) {
    global $default;

    if (!isset($oGroup)) {
        // only display unassigned groups
        $oPatternListBox = & new PatternListBox($default->groups_table, "name", "id", "fGroupID");
        $oPatternListBox->setFromClause("LEFT OUTER JOIN $default->groups_units_table GUL on ST.id=GUL.group_id");
        $oPatternListBox->setWhereClause("ISNULL(GUL.group_id) AND ST.name <> 'Anonymous' AND ST.name <> 'System Administrators'");
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $oGroup->iId . "\">\n" .
               $oGroup->getName();
    }

}

// display the listbox initially ..then just display the text
function getUnitDisplay($oUnit) {
    global $default;
    if (Permission::userIsUnitAdministrator() && !Permission::userIsSystemAdministrator()) {
        $oUnit = Unit::get(User::getUnitID($_SESSION["userID"]));
    }
    if (!isset($oUnit)) {
        $oPatternListBox = & new PatternListBox($default->units_table , "name", "id", "fUnitID");
        return $oPatternListBox->render();            
    } else {
        return "<input type=\"hidden\" name=\"fUnitID\" value=\"" . $oUnit->iId . "\">\n" .
               $oUnit->getName();
    }

}

// show button after group selected
function getAssignButton($oGroup) {
    global $default;
    if (!isset($oGroup)) {
        return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" border=\"0\"/></td>\n";
    } else {
        return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/assign.gif\" value=\"submit\" border=\"0\"/></td>\n";
    }
}
// show button after group selected
function getCancelButton($oGroup) {
    global $default;
	if ($oGroup){
    	return "<td align = right><a href=\"$default->rootUrl/control.php?action=editGroupUnit&fGroupID=" . $oGroup->getID() . "\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";    
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=listGroups\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}


?>
