<?php
/**
 * $Id$
 *
 * Remove organisation UI functions.
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
 * @package administration.orgmanagement
 */
 
// gets the delete stuff
function getDeletePage($iOrgID) {
    global $default;
    $oOrg = null;
    if (isset($iOrgID)) {
        $oOrg = Organisation::get($iOrgID);
        $sHeading .= "<b>" . _("Are you sure you wish to Remove this Organisation?") . "</b>\n";
    } else {
        $oOrg = null;
        $sHeading .= _("Please select an Organisation") . ":";
    }

    $sToRender .= renderHeading(_("Remove Organisation"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td>$sHeading</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Organisation Name") . ": " . getOrgDisplay($oOrg) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= getDeleteButton($oOrg);
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {

    global $default;

    $sToRender .= renderHeading(_("Remove Organisation"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>" . _("Organisation Successfully Removed!") . "</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=listOrg\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get deletion failed page
function getDeleteFailPage() {

    global $default;

    $sToRender .= renderHeading(_("Remove Organisation"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>" . _("Deletion Failed!") . "</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>" . _("Please Ensure that ALL Units has been Removed the Organisation it belongs to") . "</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=listOrg\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td></tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// show listbox...or the text name
function getOrgDisplay($oOrg) {
    global $default;
    if (!isset($oOrg)) {
        $oPatternListBox = & new PatternListBox($default->organisations_table, "name", "id", "fOrgID");
        $oPatternListBox->setIncludeDefaultValue(true);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fOrgID\" value=\"" . $oOrg->iId . "\">\n" .
               "<b>" . $oOrg->getName() . "</b>";
    }

}


// show delete button
function getDeleteButton($oOrg) {
    global $default;
    if (isset($oOrg)) {
         return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" border=\"0\"/></td>\n".
               "<td align = right><a href=\"$default->rootUrl/control.php?action=listOrg\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
    }
}
?>
