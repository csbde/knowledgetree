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

function getDocumentPath($iFolderID, $sDocumentName) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	    
	$sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID), "$default->rootUrl/control.php?action=browse") . " > " . $sDocumentName;
	return "<table border=\"0\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sDocumentPath</td></tr></table>\n";	
}

function getPage($iDocumentID, $iFolderID, $sDocumentName) {
	global $default;
    $sToRender  = renderHeading(_("Delete Document"));
	$sToRender .= getDocumentPath($iFolderID, $sDocumentName) . "<br>\n";
	$sToRender .= "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>You have chosen to delete the document entitled '" . $sDocumentName . "'.</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Select 'Delete' to confirm the deletion, or 'Cancel' to cancel it</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><a href=\"" . $_SERVER["PHP_SELF"] . "?fDocumentID=$iDocumentID&fDeleteConfirmed=1\"><img src=\"$default->graphicsUrl/widgets/delete.gif\" border=\"0\"></a></td> <td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $iDocumentID . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td></tr></table></td>";	
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
	                  "<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"/></a>\n" .
	                  "</p></td></tr></table>\n";
	   return $sToRender;
	}
}
?>
