<?php
/**
 * $Id$
 *
 * Remove document field UI functions.
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
 * @package administration.docfieldmanagement
 */
 
// gets the delete stuff
function getDeletePage($fDocFieldID) {
    global $default;
    $oDocField = null;
    if (isset($fDocFieldID)) {
        $oDocField = DocumentField::get($fDocFieldID);
        $sHeading = "<b>" . _("Are you sure you wish to Remove this Document Field?") . ":</b>\n";
    } else {
        $sHeading = _("Please select a Document Field:");
    }

    $sToRender .= renderHeading(_("Remove Document Field"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td colspan=\"2\">$sHeading</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Document Field Name:") . " </td><td>" . getDocFieldDisplay($oDocField) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . getDocFieldDataType($oDocField) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td>" . getDeleteButton($oDocField);// . getCancelButton($oDocField);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}

function getStatusPage($sHeading, $sText = "") {
	global $default;
	
    $sToRender .= renderHeading(_("Remove Document Field"));
    $sToRender .= "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>$sHeading</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr/><tr/>\n";
    $sToRender .= "<tr><td>$sText</td></tr>\n";
    $sToRender .= "<tr/><tr/>\n";
	$sToRender .= "<td align=\"right\">" . generateControllerLink("listDocFields", "", "<img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" />") . "</td>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {
	return getStatusPage(_("Document Field SuccessFully Removed!"));
}

// get deletion failed page
function getDeleteFailPage() {
	return getStatusPage(_("Deletion Failed!"), _("Please Ensure that The Document Field has not been assigned"));
}

function getFieldMappedPage($sDocumentFieldName, $aDocumentTypes) {
	global $default;
	
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td>" . 
        sprintf(_("Document field '%s' is mapped to the following document type(s), and can therefore not be deleted."), $sDocumentFieldName) . "</td></tr>\n";
    $sToRender .= "<tr><td>" . _("Click on the Document Type name to edit its fields, or click 'Back' to return to List Document Fields.") . "</td></tr>\n";
    for ($i=0; $i<count($aDocumentTypes); $i++) {
    	$sToRender .= "<tr><td><strong>" . generateControllerLink("editDocTypeFields", "fDocTypeSelected=1&fDocTypeID=" . $aDocumentTypes[$i]->getID(), $aDocumentTypes[$i]->getName()) . "</strong></td></tr>";
    }
    $sToRender .= "</table>\n";

	return getStatusPage(_("Can't delete document field") . " '$sDocumentFieldName'", $sToRender);
}

// show listbox...or the text name
function getDocFieldDisplay($oDocField) {
    global $default;
    if (!isset($oDocField)) {
        $oPatternListBox = & new PatternListBox($default->document_fields_table, "name", "id", "fDocFieldID");
        $oPatternListBox->setFromClause("LEFT OUTER JOIN $default->document_fields_link_table DFL ON ST.id=DFL.document_field_id");
        $oPatternListBox->setWhereClause("ISNULL(DFL.value)");
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fDocFieldID\" value=\"" . $oDocField->iId . "\">\n" .
               "<b>" . $oDocField->getName() . "</b>";
    }
}

// show listbox...or the text name
function getDocFieldDataType($oDocField) {
    global $default;
    if (!isset($oDocField)) {
        return "";
    } else {
        return "Data Type: </td><td>" . $oDocField->getDataType();
    }
}

// show delete button
function getDeleteButton($oDocField) {
    global $default;
    if (isset($oDocField)) {
        return "<td align=\"right\"><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\"border=\"0\" /></td>\n".
               "<td align=\"right\">" . generateControllerLink("listDocFields", "", "<img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" />") . "</td>\n";
    }
}

// gets the cancel button when a DocField is selected
function getCancelButton($oDocField) {
    global $default;
    if (!isset($oDocField)) {
        return "<td align=\"right\">" . generateControllerLink("docfield", "", "<img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" />") . "</td>\n";
    } else {
        return "<td align=\"right\">" . generateControllerLink("listDocFields", "", "<img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" />") . "</td>\n";
    }
}
?>
