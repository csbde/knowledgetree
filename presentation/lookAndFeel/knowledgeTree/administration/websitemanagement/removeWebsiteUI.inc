<?php
/**
 * $Id$
 *
 * Remove a website UI functions.
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
 * @package administration.websitemanagement
 */
 
// gets the delete stuff
function getDeletePage($iWebSiteID) {
    global $default;
    $oWebSite = null;
    if (isset($iWebSiteID)) {
        $oWebSite = WebSite::get($iWebSiteID);
        $sHeading .= "<b>" . _("Are you sure you wish to Remove this WebSite?") . ":</b>\n";
    } else {
        $sHeading .= _("Select a WebSite") . ":\n";
    }

    $sToRender .= renderHeading(_("Remove Website"));
    $sToRender .= "<table>\n";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td colspan=\"2\">$sHeading</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("WebSite Name") . ": </td><td>" . getWebSiteDisplay($oWebSite) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . getWebSiteUrl($oWebSite) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . getWebMasterName($oWebSite) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td></td>" . getDeleteButton($oWebSite);
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {
    global $default;

    $sToRender .= renderHeading(_("Remove Website"));
    $sToRender .= "<table>\n";
    $sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>" . _("Website Successfully Removed!") . "</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=listWebsites\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get deletion failed page
function getDeleteFailPage() {

    global $default;

    $sToRender .= renderHeading(_("Remove Website"));
    $sToRender .= "<table>\n";
    $sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>" . _("Deletion Failed!") . "</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>" . _("Please ensure that the website has been removed from any unit it belongs to") . "</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=listWebsites\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td></tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// show listbox...or the text name
function getWebSiteDisplay($oWebSite) {
    global $default;
    if (!isset($oWebSite)) {
        $oPatternListBox = & new PatternListBox($default->web_sites_table, "web_site_name", "id", "fWebSiteID");
        $oPatternListBox->setIncludeDefaultValue(true);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fWebSiteID\" value=\"" . $oWebSite->iId . "\">\n" .
               "<b>" . $oWebSite->getWebSiteName() . "</b>";
    }
}

// show check box
function getWebSiteUrl($oWebSite) {
    if (!isset($oWebSite)) {
        return "";
    } else {
        return  "WebSite URL: </td><td>" . $oWebSite->getWebSiteUrl();
    }
}

// show text box
function getWebMasterName($oWebSite) {
    $oUser = null;

    if (!isset($oWebSite)) {
        return "";
    } else {
        $iUserID = $oWebSite->getWebMasterID();
    }
}

// show delete button

function getDeleteButton($oWebSite) {
    global $default;
    if (!isset($oWebSite)) {
    }
    else {
        return "<td align = right><input type=\"image\" src =\"" . KTHtml::getDeleteButton() . "\" value=\"submit\" border=\"0\"/>\n".
               "<a href=\"$default->rootUrl/control.php?action=listWebsites\"><img src =\"" . KTHtml::getCancelButton() . "\" border = \"0\" /></a></td>\n";
    }
}
?>
