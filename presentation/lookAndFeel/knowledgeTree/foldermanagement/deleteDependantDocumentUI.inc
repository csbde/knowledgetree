<?php
/**
 * $Id$
 *
 * Presentation logic for deleting a dependant document.
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
 * @package foldermanagement
 */

function getPage($iFolderID, $iFolderCollaborationID, $sDocumentTitle, $sUserName, $sTemplateDocument) {
	global $default;
	$sToRender = renderHeading(_("Delete dependant document"));
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Document title") . "</td><td>$sDocumentTitle</td>";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("User") . "</td><td>$sUserName</td>";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Template document") . "</td><td>$sTemplateDocument</td>";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/delete.gif\" border=\"0\"/></td><td><a href=\"" . $default->rootUrl . "/control.php?action=viewDependantDocument&fFolderID=$iFolderID&fFolderCollaborationID=$iFolderCollaborationID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/></a></td></tr></table>\n";
	$sToRender .= "</table\n";
	
	return $sToRender;	
}

?>
