<?php
/**
 * $Id$
 *
 * Edit user preferences UI functions.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 * Modified 2004 by William Hawkins
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
 * @package preferences
 */

function getUserDetailsPage($iUserID) {
    global $default;    
    $sQuery = "SELECT * FROM $default->users_table WHERE id = $iUserID";
    $aDisplayColumns = array("name", "email", "mobile", "email_notification");
    $aStoreColumns = array("name", "email", "mobile", "email_notification");
    $aColumnNames = array(_("Name") . ": ", _("Email Address") . ": ", _("Mobile Number") . ": ", _("Email Notification") . ": ");
    $aDisplayColumnTypes = array(1, 1, 1, 2);
    $aDatabaseColumnTypes = array(1, 1, 1, 2);

    // get list of User properties...name...text field...is_Sys_admin and is_unit_admin..checkboxes
    $oPattern = & new PatternEditableListFromQuery($sQuery, $default -> users_table, $aDisplayColumns, $aStoreColumns, $aColumnNames, $aDisplayColumnTypes, $aDatabaseColumnTypes);
    $oPattern -> setUniqueName("userEdit");
    $oPattern -> setColumnsRequired(array(1, 0, 0, 0));

    $sToRender.= renderHeading(_("Preferences"));
    $sToRender.= "<table width=\"600\">\n";
    $sToRender.= "<tr></tr>\n";
    $sToRender.= "<tr/><tr/><tr><td><b>" . _("Edit User Properties") . ":</b></td></tr>\n";
    $sToRender.= "<tr>\n";
    $sToRender.= "<td>".$oPattern -> render()."</td>\n";
    $sToRender.= "</tr>\n";
    $sToRender.= "<tr>\n";
    $sToRender.= "</tr>\n";
    $sToRender.= "<tr>\n";
    $sToRender.= "</tr>\n";

    $sToRender.= "<tr><td><input type=\"image\" src =\"" . KTHtml::getUpdateButton() . "\" value=\"Update\" border=\"0\"/>\n";
    if (strcmp($default -> authenticationClass, "DBAuthenticator") == 0) {
        $sToRender.= "<a href=\"$default->rootUrl/control.php?action=changeUserPassword\"><img src =\"" . KTHtml::getUpdatePasswordButton() . "\" border=\"0\" /></td></tr>\n";
    }
    $sToRender.= "</table>\n";
    return $sToRender;
}

function getEditPageSuccess() {
    global $default;
    $sToRender.= renderHeading(_("Preferences"));
    $sToRender.= "<table width=\"600\">\n";
    $sToRender.= "<tr/><tr/><tr><td><b>" . _("Success") . "!</b></td></tr>\n";
    $sToRender.= "<tr><td>" . _("The User was successfully modified") . "</td></tr>\n";
    $sToRender.= "<tr></tr>\n";
    $sToRender.= "<tr></tr>\n";
    $sToRender.= "<tr>\n";
    $sToRender.= "<td><a href=\"$default->rootUrl/control.php?action=preferences\"><img src =\"" . KTHtml::getBackButton() . "\" border=\"0\" /></a></td>\n";
    $sToRender.= "</tr>\n";
    $sToRender.= "</table>\n";
    return $sToRender;
}

function getEditPageFail() {
    global $default;
    $sToRender.= renderHeading(_("Preferences"));
    $sToRender.= "<table>\n";
    $sToRender.= "<tr/><tr/>\n";
    $sToRender.= "<tr><td>" . _("User modification Failed!") . "</td></tr>\n";
    $sToRender.= "<tr></tr>\n";
    $sToRender.= "<tr></tr>\n";
    $sToRender.= "<tr>\n";
    $sToRender.= "<td></td><td align=\"right\"><a href=\"$default->rootUrl/control.php?action=preferences\"><img src =\"" . KTHtml::getBackButton() . "\" border=\"0\" /></a></td>\n";
    $sToRender.= "</tr>\n";
    $sToRender.= "</table>\n";
    return $sToRender;
}
