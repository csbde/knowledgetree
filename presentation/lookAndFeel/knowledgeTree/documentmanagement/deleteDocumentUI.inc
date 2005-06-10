<?php
/**
 * $Id$
 *
 * Presentation information for documentDeleteBL.php.
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */
function getPage($aDocumentIDs) {
	global $default;
    $sToRender  = renderHeading(_("Delete Document"));
	$sToRender .= "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("You have chosen to delete the following document(s):") . " <br><br></td>\n";
	
	for ($i = 0; $i < count($aDocumentIDs); $i++) {
	  $oDocument = Document::get($aDocumentIDs[$i]);
	$sToRender .= "<tr>\n";
	  $sToRender .= "<td>&nbsp;&nbsp;'" .  $oDocument->getDisplayPath() . "'</td>\n";
	  $sQueryString .= "fDocumentIDs[]=$aDocumentIDs[$i]&";
	}
	$sToRender .= "<tr><tr><tr><tr>\n";

	$sToRender .= "<td>" . _("Select 'Delete' to confirm the deletion, or 'Cancel' to cancel it") . "</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td>";
    $sToRender .= "<a href=\"" . $_SERVER["PHP_SELF"] . "?$sQueryString&fDeleteConfirmed=1\"><img src=\"" . KTHtml::getDeleteButton() . "\" border=\"0\"></a>";
    $sToRender .= "</td> <td>";
    if (KTUtil::arrayGet($_REQUEST, "fReturnFolderID")) {
        $sToRender .= generateControllerLink("browse", "fFolderID=" . $_REQUEST['fReturnFolderID'], "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a>");
    } else if (KTUtil::arrayGet($_REQUEST, "fReturnDocumentID")) {
        $sToRender .= generateControllerLink("viewDocument", "fDocumentID=" . $_REQUEST['fReturnDocumentID'], "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a>");
    } else {
        $sToRender .= "<a href=\"javascript:history.go(-1)\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a>";
    }
    $sToRender .= "</td></tr></table></td>";	
	$sToRender .= "</tr>";
	$sToRender .= "</table>\n";
	
	return $sToRender;
	
}

function renderErrorPage($sErrorMessage, $iDocumentID = "") {
    global $default;
    if ($iDocumentID) {
    	return statusPage("Delete Document", "", $sErrorMessage, "viewDocument", "fDocumentID=$iDocumentID");
    } else {
	    $sToRender  = renderHeading(_("Delete Document"));    
	    $sToRender .= "<table>\n";
	    $sToRender .= "<tr><td><p class=\"errorText\">$sErrorMessage</p>\n" .
	                  "<a href=\"javascript:history.go(-2)\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\"/></a>\n" .
	                  "</p></td></tr></table>\n";
	   return $sToRender;
	}
}
?>
