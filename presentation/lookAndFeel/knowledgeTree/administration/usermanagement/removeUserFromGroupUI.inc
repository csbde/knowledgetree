<?php
/**
 * $Id$
 *
 * Remove user from a group UI functions.
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
// get page for removal
function getPage($iUserID) {
    global $default;
    $oUser = null;
    $oGroup = null;

    // change headings if User selected
    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
    } else {
        $heading = "<tr><td>Please select a User:</td></tr>\n";
    }

    $sToRender .= renderHeading(_("Remove User From Group"));
    $sToRender .= "<table>\n";
    $sToRender .= $heading;
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td>User Name: " . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    
    $sToRender .= "</td></table>\n";

    return $sToRender;
}



// get page for removal
function getGroupPage($iUserID, $aGroup) {
    global $default;
    $oUser = null;
    $oGroup = null;

    // change headings if User selected
    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
    }

    // change headings if User selected
    if (isset($fGroupID)) {
        $oGroup = Group::get($fGroupID);
        echo "what: " . $oGroup->getName();
    }

    // when Groupid is gotten show the name
    if (!isset($oGroup)) {

        $Groupdisplay = "<td>Group Name: " . getGroupDisplay($aGroup) . "</td>\n";
    } else {
        $Groupdisplay = "<td>Group Name: " . $oGroup->getName() . "</td>\n";
    }

    $sToRender .= renderHeading(_("Remove User From Group"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td>" . _("Select a Group to Remove User from") . ":</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td>User Name: " . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= $Groupdisplay;
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td></td><td>" . getSelectButton($oUser);
    $sToRender .= "<td></td><td>" . getCancelButton($oUser);
    $sToRender .= "</td></table>\n";


    return $sToRender;
}

// get page for removal
function getDeleteConfirmedPage($iUserID,$fGroupID) {
    global $default;
    $oUser = null;
    $oGroup = null;

    // change headings if User selected
    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
        $heading = "<tr><td>" . _("Are you sure you wish to remove this user from the Group?") . "</td></tr>";
    }

    // change headings if User selected
    if (isset($fGroupID)) {
        $oGroup = Group::get($fGroupID);
        $Groupdisplay = "<td>" . _("Group Name") . ": " . $oGroup->getName() . "</td>\n";
    }

    $sToRender .= renderHeading(_("Remove User From Group"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= $heading;
    $sToRender .= "</tr>\n";
    $sToRender .= "<td>User Name: " . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= $Groupdisplay;
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td></td><td>" . getDeleteButton($oUser);
    $sToRender .= "<td></td><td>" . getCancelButton($oUser);
    $sToRender .= "</td></table>\n";


    return $sToRender;
}

// show paig if deletion fails
function getPageFail() {
    global $default;

    $sToRender .= renderHeading(_("Remove User From Group"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>" . _("Sorry, Removal Failed") . "</b></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeUserFromGroup\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";

    $sToRender .= "</table>\n";


    return $sToRender;
}

// show success page if removed from Group successfully
function getPageSuccess($fUserID) {
    global $default;

    $sToRender .= renderHeading(_("Remove User From Group"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>" . _("Success") . "!</b></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>" . _("The User was successfully removed from the Group") . "</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editUserGroups&fUserID=$fUserID\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


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

// show Group details on confirm deletion page
function getGroupDisplay($aGroup) {
    global $default;

    $oGroup= null;
    $sGroup = "<select name=\"fGroupID\">";
    for ($i=0;$i < count($aGroup);$i++) {
        $oGroup= Group::get($aGroup[$i]);
        $Options .=  "<OPTION value= \"". $oGroup->iId . "\">". $oGroup->getName() ."</OPTION>\n";
    }
    $sGroup .= $Options . "</SELECT>";
    return $sGroup;
}


// show delete button or select button
function getDeleteButton($oUser) {
    global $default;
    if (!isset($oUser)) {
        return "<td align = right><input type=\"image\" src =\"" . KTHtml::getSelectButton() . "\" value=\"submit\" border=\"0\"/></td>\n";
    } else {
        return "<td align = right><input type=\"image\" src =\"" . KTHtml::getDeleteButton() . "\" value=\"submit\" border=\"0\"/></td>\n";
    }
}


// show delete button or select button
function getSelectButton($oUser) {
    global $default;
    if (!isset($oUser)) {
        return "<td align = right><input type=\"image\" src =\"" . KTHtml::getSelectButton() . "\" value=\"submit\" border=\"0\"/></td>\n";
    } else {
        return "<td align = right><input type=\"image\" src =\"" . KTHtml::getSelectButton() . "\" value=\"submit\" border=\"0\"/></td>\n";
    }
}
// show cancel button
function getCancelButton($oUser) {
    global $default;
    if (!isset($oUser)) {
        //return "<td align = right><a href=\"$default->rootUrl/control.php?action=UserManagement\"><img src =\"" . KTHtml::getCancelButton() . "\" value=\"cancel\" /></a></td>\n";
    }
    else {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=editUserGroups&fUserID=" . $oUser->getID() .  "\"><img src =\"" . KTHtml::getCancelButton() . "\" border = \"0\" /></a></td>\n";
    }
}


?>
