<?php
/**
 * $Id$
 *
 * Move document presentation functions.
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
 
function getConfirmationPage($iFolderID, $iDocumentID) {
	global $default;
    $sToRender = renderHeading("Move Document");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n<td>Are you sure you want to move </td></tr>";
	$sToRender .= "<tr><td><strong>" . Document::getDocumentDisplayPath($iDocumentID) . "</strong> to </td></tr>\n";
	$sToRender .= "<tr><td><strong>" . Folder::getFolderDisplayPath($iFolderID) . " > " . Document::getDocumentName($iDocumentID) . "</strong></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=moveDocument&fDocumentID=$iDocumentID&fFolderID=$iFolderID&fForMove=1&fConfirmed=1\"><img src=\"$default->graphicsUrl/widgets/move.gif\" border=\"0\"/></a>&nbsp;<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $iDocumentID . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td></tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}


function getPage($iFolderID, $iDocumentID, $sErrorMessage = "") {
	global $default;
    $sToRender = renderHeading("Move Document");
	$sToRender .= "<table>\n";
	if ($sErrorMessage) {
		$sToRender .= "<tr><td><font color=\"red\">$sErrorMessage</font></td></tr>";
	}
	$sToRender .= "<tr><td>Move document " . Document::getDocumentDisplayPath($iDocumentID) . " to:</td></tr>";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . renderFolderPath($iFolderID, "/control.php?action=moveDocument&fDocumentID=$iDocumentID") . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table width=\"100%\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . renderFolderList($iFolderID, "/control.php?action=moveDocument&fDocumentID=$iDocumentID") . "</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<tr><td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/movehere.gif\" border=\"0\"/></td><td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $iDocumentID . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td></tr></table></td>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

?>
