<?php
/**
 * $Id$
 *
 * Remove a role UI functions.
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
 * @package administration.rolemanagement
 */
// gets the delete stuff
function getDeletePage($iRoleID) {
    global $default;
    $oRole = null;
    if (isset($iRoleID)) {
        $oRole = Role::get($iRoleID);
        $sHeading .= "<b>Are you sure you wish to Remove this Role?:</b>\n";
    } else {
        $sHeading .= "Please select a Role:";
    }

    $sToRender .= renderHeading("Remove Role");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td>$sHeading</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Role Name: </td><td>" . getRoleDisplay($oRole) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .=  getReadableCheckBox($oRole) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= getWriteableCheckBox($oRole) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= getDeleteButton($oRole);
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {

    global $default;

    $sToRender .= renderHeading("Remove Role");
    $sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>Role SuccessFully Removed!</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=listRoles\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get deletion failed page
function getDeleteFailPage() {

    global $default;

    $sToRender .= renderHeading("Remove Role");
    $sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>Deletion Failed!</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>Please Ensure that The Role has not been assigned to any Folder</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=listRoles\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td></tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// show listbox...or the text name
function getRoleDisplay($oRole) {
    global $default;
    if (!isset($oRole)) {
        $oPatternListBox = & new PatternListBox($default->roles_table, "name", "id", "fRoleID");
        $oPatternListBox->setIncludeDefaultValue(true);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fRoleID\" value=\"" . $oRole->iId . "\">\n" .
               "<b>" . $oRole->getName() . "</b>";
    }

}

// show check box
function getActiveCheckBox($oRole) {
    if (!isset($oRole)) {
        return "";
    } else {
        return "<td>Active: </td><td><input disabled=\"true\" type=\"checkbox\" name=\"fActive\" " . ($oRole->getActive() ? "CHECKED " : " ") . " />";
    }
}

// show check box
function getReadableCheckBox($oRole) {
    if (!isset($oRole)) {
        return "";
    } else {
        return "<td>Readable: </td><td><input disabled=\"true\" type=\"checkbox\" name=\"fReadable\" " . ($oRole->getReadable() ? "CHECKED " : " ") . " />";
    }
}

// show text box
function getWriteableCheckBox($oRole) {
    if (!isset($oRole)) {
        return "";
    } else {
        return "<td>Writable: </td><td><input disabled=\"true\" type=\"checkbox\" name=\"fWritable\" " . ($oRole->getWriteable() ? "CHECKED " : " ") . " />";
    }

}

// show delete button
function getDeleteButton($oRole) {
    global $default;
    if (isset($oRole)) {
        return "<td></td><td><td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" border=\"0\"/></td>\n".
               "<td align = right><a href=\"$default->rootUrl/control.php?action=listRoles\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
    }
}
?>
