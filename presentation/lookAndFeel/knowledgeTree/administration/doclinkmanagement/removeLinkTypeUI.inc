<?php
/**
 * $Id$
 *
 * Remove document link type UI functions.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */
 
// gets the delete stuff
function getDeletePage($iLinkID) {
    global $default;
    $oLink = null;
    if (isset($iLinkID)) {
        $oLink = LinkType::get($iLinkID);
        $sHeading .= "<b>" . _("Are you sure you wish to memove this Link Type?") . ":</b>\n";
    } else {
        $sHeading .= _("Please select a Link Type") . ":";
    }

    $sToRender .= renderHeading(_("Remove Document Link Type"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td colspan=\"2\">$sHeading</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Link Name: </td><td>" . getLinkDisplay($oLink) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . getDescr($oLink) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td/>" . getDeleteButton($oLink) . "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {

    global $default;

    $sToRender .= renderHeading(_("Remove Document Link Type"));
    $sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>" . _("Document Link Type successfully removed") . "!</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=listLinkTypes\">" . generateImage(KTHtml::getBackButton()) . "</a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get deletion failed page
function getDeleteFailPage() {

    global $default;

    $sToRender .= renderHeading(_("Remove Document Link Type"));
    $sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>" . _("Deletion failed") . "!</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>" . _("Please ensure that all document links from this type have been removed") . ".</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=listLinkTypes\">" . generateImage(KTHtml::getBackButton()) . "</a></td></tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// show listbox...or the text name
function getLinkDisplay($oLink) {
    global $default;
    if (!isset($oLink)) {
        $oPatternListBox = & new PatternListBox($default->quicklinks_table, "name", "id", "fLinkID");
        $oPatternListBox->setIncludeDefaultValue(true);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fLinkID\" value=\"" . $oLink->iId . "\">\n" .
               "<b>" . $oLink->getName() . "</b>";
    }
}

// show check box
function getDescr($oLink) {
    if (!isset($oLink)) {
        return "";
    } else {
        return _("Description") . ": </td><td>" . $oLink->getDescription();
    }
}

// show delete button
function getDeleteButton($oLink) {
    global $default;
    if (isset($oLink)) {
        return "<td colspan=\"2\" align=\"right\"><input type=\"image\" src=\"" . KTHtml::getDeleteButton() . "\" value=\"submit\" border=\"0\"/>\n".
               "<a href=\"$default->rootUrl/control.php?action=listLinks\">" . generateImage(KTHtml::getCancelButton()) . "</a></td>\n";
    }
}
?>
