<?php
/**
 * $Id$
 *
 * Edit document type fields UI functions.
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

// if its the manual edit page..then display normally
function getDetailsPage($iDocTypeID) {
    global $default;
    $oDocType = null;
    if (isset($iDocTypeID)) {
        $oDocType = DocumentType::get($iDocTypeID);
    }

    $sToRender .= renderHeading(_("Edit Document Type"));
    
    $sToRender .= "<table border=0 width=100%>\n";
    
    $sToRender .= "<tr>\n";
    $sToRender .= "<td width=\"45%\">" . _("Document Type Name:") . " </td><td>" . $oDocType->getName() . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><input type=hidden name=\"fDocTypeID\"  value=\"" . $iDocTypeID . "\"></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td valign=\"top\">" . _("Generic Document Fields:") . " </td><td>" . getGenericFieldsList() . "</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td valign=\"top\">" . _("Document Type Specific Fields:") . " </td>";    
    $sToRender .= "<td>" . getDocumentTypeFieldsList($iDocTypeID) . "</td></tr>\n";	
    $sToRender .= "<tr><td >" . generateControllerLink("addDocTypeFieldsLink", "fDocTypeID=$iDocTypeID", _("Add new Type Specific Field") . " &nbsp; <img border=0 src=\"" . KTHtml::getAddButton() . "\"/>");
    $sToRender .= generateControllerLink("listDocTypes", "", "<img border=0 src=\"" . KTHtml::getBackButton() . "\"/>"). "</td></tr>\n";    
    $sToRender .= "</table>\n";

    return $sToRender;
}

function getDeleteConfirmationPage($iDocTypeID, $iDocFieldID) {
	global $default;

    $oDocType = DocumentType::get($iDocTypeID);
    $oDocField = DocumentField::get($iDocFieldID);
	
    $sToRender .= renderHeading(_("Edit Document Type") . " - " . _("Delete Document Field Link"));
    $sToRender .= "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\">\n";
    $sToRender .= "<tr><td>" . 
	sprintf(_("Are you sure you want to remove field '%s' from document type '%s'?"), $oDocField->getName(), $oDocType->getName()) .
	"</td></tr>\n";
    $sToRender .= "<tr/><tr/>\n";
    $sToRender .= "<tr><td>" . _("Click 'Remove' to remove the link, or 'Cancel' to abort.") . "</td></tr>\n";
    $sToRender .= "<tr/><tr/>\n";
	$sToRender .= "<td align=\"right\">" . generateControllerLink("editDocTypeFields", "fConfirm=1&fRemove=1&fDocFieldID=$iDocFieldID&fDocTypeID=$iDocTypeID", "<img src =\"" . KTHtml::getRemoveButton() . "\" border = \"0\" />");
	$sToRender .= generateControllerLink("editDocTypeFields", "fDocTypeID=$iDocTypeID", "<img src =\"" . KTHtml::getCancelButton() . "\" border = \"0\" />") . "</td>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

function getEditDocumentFieldLinkPage($oDocTypeFieldLink) {
    global $default;

    $oDocField = DocumentField::get($oDocTypeFieldLink->getFieldID());	
	
    $sToRender .= renderHeading(_("Edit Document Type") . " - " . _("Update Document Field Link Mandatory Setting"));
    $sToRender .= "<table>\n";
	$sToRender .= "<input type=\"hidden\" name=\"fConfirm\" value=\"1\">\n";    
    $sToRender .= "<input type=\"hidden\" name=\"fDocTypeID\" value=\"" . $oDocTypeFieldLink->getDocumentTypeID() . "\">\n";
    $sToRender .= "<input type=\"hidden\" name=\"fDocFieldID\" value=\"" . $oDocTypeFieldLink->getFieldID() . "\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Document Field Name:") . " </td><td>" . $oDocField->getName() . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Mandatory") . ":  </td><td><input type=\"checkbox\" name=\"fIsMandatory\" value=\"1\" " . ($oDocTypeFieldLink->getIsMandatory() ? "CHECKED" : "") . "></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td><input type=\"image\" src=\"" . KTHtml::getUpdateButton() . "\"/>";
    $sToRender .= generateControllerLink("editDocTypeFields", "fDocTypeID=" . $oDocTypeFieldLink->getDocumentTypeID(), "<img src =\"" . KTHtml::getCancelButton() . "\" border = \"0\" />") . "</td></tr>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}


function getStatusPage($sHeading, $sText = "", $iDocTypeID) {
	global $default;
	
    $sToRender .= renderHeading(_("Edit Document Type"));
    $sToRender .= "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>$sHeading</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr/><tr/>\n";
    $sToRender .= "<tr><td>$sText</td></tr>\n";
    $sToRender .= "<tr/><tr/>\n";
	$sToRender .= "<td align=\"right\">" . generateControllerLink("editDocTypeFields", "fDocTypeID=$iDocTypeID", "<img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" />") . "</td>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

// if addition is successful print otu success page
function getSuccessPage($sMessage, $iDocTypeID) {
	return getStatusPage(_("Success") . "!", $sMessage, $iDocTypeID);
}

// gets the page if editing fails
function getFailurePage($sMessage, $iDocTypeID) {
	return getStatusPage(_("Failure") . "!", $sMessage, $iDocTypeID);
}

function getGenericFieldsList() {
    global $default;
    $aGenericDocumentFields = DocumentField::getList("is_generic=1");/*ok*/
    for($i = 0; $i < count($aGenericDocumentFields); $i++) {
        $sToRender .= "<li>" . $aGenericDocumentFields[$i]->getName() . "</li>\n";
    }
    return $sToRender;
}

function getDocumentTypeFieldsList($iDocTypeID) {
    global $default;
    $aDocumentTypeFields = DocumentTypeFieldLink::getDocumentTypeFields($iDocTypeID);
	$sToRender .= "<table border=0>";
    for($i = 0; $i < count($aDocumentTypeFields); $i++) {
        $oDocField = $aDocumentTypeFields[$i];
        if ($oDocField) {
	        $oDocumentTypeFieldLink = DocumentTypeFieldLink::getByFieldAndTypeIDs($iDocTypeID, $oDocField->getID());
	        $sToRender .= "<tr>";
	        $sToRender .= "<td>" . $oDocField->getName() . "</td>";
	        $sToRender .= "<td><img src=\"$default->graphicsUrl/widgets/" . ($oDocumentTypeFieldLink->getIsMandatory() ? "checked" : "unchecked") . ".gif\"/></td>";
	        $sToRender .= "<td>" . getEditSpecificFieldButton($oDocField->getID(), $iDocTypeID) . "</td>";
	        $sToRender .= "<td>" . getRemoveSpecificFieldButton($oDocField->getID(), $iDocTypeID) . "</td>";
	        $sToRender .= "</tr>"; 
        } else {
        	// FIXME: what about broken links?        	
        }
    }
	$sToRender .= "</table>\n";    
    return $sToRender;
}

function getEditSpecificFieldButton($iDocFieldID, $iDocTypeID) {
    global $default;
	return generateControllerLink("editDocTypeFields", "fUpdateMandatory=1&fDocFieldID=$iDocFieldID&fDocTypeID=$iDocTypeID", "<img border=0 src =\"" . KTHtml::getEditButton() . "\" value=\"edit\" />");	
}

function getRemoveSpecificFieldButton($iDocFieldID, $iDocTypeID) {
    global $default;
	return generateControllerLink("editDocTypeFields", "fRemove=1&fDocFieldID=$iDocFieldID&fDocTypeID=$iDocTypeID", "<img border=0 src =\"" . KTHtml::getRemoveButton() . "\" value=\"remove\" />");	
}
?>
