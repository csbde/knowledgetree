<?php

/**
 * $Id$
 *
 * Web document presentation functions.
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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */
 
/**
 * Prompts the user for a checkout comment
 *
 * @param object the document we're checking out
 */
function renderUploadPage($oWebDocument) {
	global $default;
	$oDocument = Document::get($oWebDocument->getDocumentID());
    $oWebSite = WebSite::get($oWebDocument->getWebSiteID());
    $oUser = User::get($oDocument->getCreatorID());
    
    $sToRender  = renderHeading("Web document");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td>'" . $oUser->getName() . "' has requested that the document </td></tr>";
    $sToRender .= "<tr><td><strong>" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID(), $oDocument->getDisplayPath()) . "</strong></td></tr>";
    $sToRender .= "<tr><td>be uploaded to website <strong>'" . $oWebSite->getWebSiteName() . "'</strong> (" . $oWebSite->getWebSiteURL() . ")</td></tr>\n";
	$sToRender .= "<tr><td>Once the document has been uploaded, please enter the URL that it has been uploaded to in the text box below and click 'Done' </td></tr>\n";
	$sToRender .= "<tr/>\n";
	$sToRender .= "<tr><td>Upload URL: <input type=\"text\" name=\"fUploadUrl\" size=\"30\" value=\"" . $oWebSite->getWebSiteURL() . "\"/></td></tr>\n";
    $sToRender .= "<tr><td><input type=\"hidden\" name=\"fUploaded\" value=\"1\"/></td></tr>\n";
    $sToRender .= "<tr><td><input type=\"hidden\" name=\"fWebDocumentID\" value=\"" . $oWebDocument->getID() . "\"/></td></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
    $sToRender .= "<td><input type=\"image\" border=\"0\" src =\"$default->graphicsUrl/widgets/done.gif\" value=\"Submit\" onClick=\"return validRequired(document.MainForm.fUploadUrl, 'upload URL');\" />\n";
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=dashboard\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td>\n";	    
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;    
}

function renderErrorPage($sErrorMessage, $iDocumentID = -1) {
    global $default;
    return "<p class=\"errorText\">$sErrorMessage</p>\n" .
           (($iDocumentID == -1) ? 
           "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=$iDocumentID\">" :
           "<a href=\"javascript:history.go(-1)\">") .
           "<img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"/></a>\n";
}
?>
