<?php
/**
 * $Id$
 *
 * Remove user UI functions.
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
// gets the delete stuff
function getDeletePage($iUserID) {
    global $default;
    $oUser = null;
    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
        $sHeading .= "<tr><td><b>" . _("Are you sure you wish to remove this user?") . ":</b></td></tr>\n";
    } else {
        $sHeading .= "<tr><td><b>" . _("Please select a user") . ":</b></td></tr>\n";
    }

    $sToRender .= renderHeading(_("Remove User"));
    $sToRender .= "<table>\n";
    $sToRender .= $sHeading;
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>User Name: " . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {

    global $default;

    $sToRender .= renderHeading(_("Remove User"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>" . _("User SuccessFully Removed!") . "</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=listUsers\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get deletion failed page
function getDeleteFailPage() {

    global $default;

    $sToRender .= renderHeading(_("Remove User"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>" . _("Deletion Failed!") . "</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=listUsers\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td></tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}


function getDeleteConfirmedPage($iUserID) {
    global $default;
    $oUser = null;
    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
    }

    $sToRender .= renderHeading(_("Remove User"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>" . _("Are you sure you wish to remove this user?") . ":</b></td></tr>";
    $sToRender .= "<tr><td>" . _("Note: This User will be Removed from ALL Groups as well") . "\n</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("User Name") . ": " . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .=  getUserDetails($oUser). "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td>" . getDeleteButton($oUser);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}


// show listbox...or the text name
function getUserDisplay($oUser) {
    global $default;
    if (!isset($oUser)) {
        $oPatternListBox = & new PatternListBox($default->users_table, "username", "id", "fUserID");
        $oPatternListBox->setIncludeDefaultValue(true);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fUserID\" value=\"" . $oUser->iId . "\">\n" .
               "<td><b>" . $oUser->getUserName() . "</b>";
    }
}

// show listbox...or the text name
function getUserDetails($oUser) {
    global $default;
    if (!isset($oUser)) {
        return "";
    } else {
        return
            "<tr>\n".
            "<td>" . _("Name") . ": </td><td>". $oUser->getName() . "</td>\n" .
            "</tr>\n".
            "<tr>\n".
            "<td>" . _("Email Address") . ": </td><td>". $oUser->getEmail() . "</td>\n" .
            "</tr>\n".
            "<tr>\n".
            "<td>" . _("Mobile Number") . ": </td><td>". $oUser->getMobile() . "</td>\n" .
            "</tr>\n";
    }
}


// show delete button
function getDeleteButton($oUser) {
    global $default;
    return "<td align = right><input type=\"image\" src =\"" . KTHtml::getDeleteButton() . "\" value=\"submit\" border=\"0\"/></td>\n".
			"<td><a href=\"$default->rootUrl/control.php?action=listUsers\"><img src =\"" . KTHtml::getCancelButton() . "\" border=\"0\" /></a></td>\n";
}


// show cancel button
function getCancelButton($oUser) {
    global $default;
    if (!isset($oUser)) {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=userAdministration\"><img src =\"" . KTHtml::getCancelButton() . "\" value=\"cancel\" /></a></td>\n";
    }
    else {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=removeUser\"><img src =\"" . KTHtml::getCancelButton() . "\" border = \"0\" /></a></td>\n";    
    }
}

?>
