<?php
/**
 * $Id$
 *
 * Remove a document type UI functions.
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
 * @package administration.doctypemanagement
 */

// gets the delete stuff
function getDeletePage($iDocTypeID) {
    global $default;
    $oDocType = null;
    if (isset($iDocTypeID)) {
        $oDocType = DocumentType::get($iDocTypeID);
        $sHeading .= "<b>" . _("Are you sure you wish to Remove this Document Type?") . ":</b>\n";
    } else {
        $sHeading .= _("Please select a Document Type") . ":";
    }

    $sToRender .= renderHeading(_("Remove Document Type"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td>$sHeading</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Document Type Name:") . " " . getDocTypeDisplay($oDocType) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= getDeleteButton($oDocType);
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {

    global $default;

    $sToRender .= renderHeading(_("Remove Document Type"));
    $sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>" . _("DocumentType SuccessFully Removed!") . "</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=listDocTypes\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get deletion failed page
function getDeleteFailPage() {

    global $default;

    $sToRender .= renderHeading(_("Remove Document Type"));
    $sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>" . _("Deletion Failed!") . "</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>" . _("Please Ensure that the Document Type has been Removed from ALL Folders") . "</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=listDocTypes\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td></tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// show listbox...or the text name
function getDocTypeDisplay($oDocType) {
    global $default;
    if (!isset($oDocType)) {
        $oPatternListBox = & new PatternListBox($default->document_types_table, "name", "id", "fDocTypeID");
        $oPatternListBox->setFromClause("LEFT OUTER JOIN $default->documents_table D on ST.id=D.document_type_id");
        $oPatternListBox->setWhereClause("ISNULL(D.id)");
        $oPatternListBox->setIncludeDefaultValue(true);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fDocTypeID\" value=\"" . $oDocType->iId . "\">\n" .
               "<b>" . $oDocType->getName() . "</b>";
    }

}


// show delete button
function getDeleteButton($oDocType) {
    global $default;
    if (isset($oDocType)) {
        return "<td align = right><input type=\"image\" src =\"" . KTHtml::getDeleteButton() . "\" value=\"submit\" border=\"0\"/></td>\n".
               "<td align = right><a href=\"$default->rootUrl/control.php?action=listDocTypes\"><img src =\"" . KTHtml::getCancelButton() . "\" border = \"0\" /></a></td>\n";
    }
}
?>
