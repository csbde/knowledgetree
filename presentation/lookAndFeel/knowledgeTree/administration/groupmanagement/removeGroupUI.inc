<?php
/**
 * $Id$
 *
 * Remove a group UI functions.
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

// gets the delete stuff
function getDeletePage($iGroupID) {	
	global $default;
	$oGroup = null;
	if (isset($iGroupID)) {
		$oGroup = Group::get($iGroupID);
		$sHeading = "<b>" . _("Are you sure you wish to Remove this Group?:") . "</b>\n";
	} else {
        $sHeading = _("Please select a group:");
    }
	
    $sToRender .= renderHeading(_("Remove Group"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td>$sHeading</td></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Group Name") . ": " . getGroupDisplay($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getUnitAdminCheckBox($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getSysAdminCheckBox($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= getDeleteButton($oGroup);	
	$sToRender .= "</table>\n";

	return $sToRender;
}

// show listbox...or the text name
function getGroupDisplay($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		$oPatternListBox = & new PatternListBox($default->groups_table, "name", "id", "fGroupID");
		$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $oGroup->iId . "\">\n" .
		"<b>" . $oGroup->getName() . "</b>";
	}
}

// show check box
function getUnitAdminCheckBox($oGroup) {
	if (isset($oGroup)) {
		return _("Unit Administrator") . ": <input disabled=\"true\" type=\"checkbox\" name=\"fGroupUnitAdmin\" " . ($oGroup->getUnitAdmin() ? "CHECKED " : " ") . " />";
	}
}

// show text box
function getSysAdminCheckBox($oGroup) {
	if (isset($oGroup)) {
		return _("System Administrator") . ": <input disabled=\"true\" type=\"checkbox\" name=\"fGroupSysAdmin\" " . ($oGroup->getSysAdmin() ? "CHECKED " : " ") . " />";
	}
}

// show delete button
function getDeleteButton($oGroup) {
	global $default;
	if (isset($oGroup)) {
        return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" border=\"0\"/></td>\n".
        "<td align = right><a href=\"$default->rootUrl/control.php?action=listGroups\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
    }
}
?>
