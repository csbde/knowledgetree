<?php
/**
 * $Id$
 *
 * Presentation logic for removing a link between two documents.
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

function getPage($sParentDocumentName, $sChildDocumentName, $iParentDocumentID) {
	global $default;
	$sToRender = renderHeading(_("Unlink document"));
	$sToRender .= displayDocumentPath($iParentDocumentID);
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>" . _("You have chosen to delete the link between these two documents") . ": '$sParentDocumentName' / '$sChildDocumentName'</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td>" . _("Select 'Delete' to confirm or 'Cancel' to cancel") . "</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "</tr>\n";	;
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/delete.gif\" border=\"0\"></td> <td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $iParentDocumentID . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td></tr></table></td>";	
	$sToRender .= "</tr>";
	$sToRender .= "</table>";
	return $sToRender;
}

?>
