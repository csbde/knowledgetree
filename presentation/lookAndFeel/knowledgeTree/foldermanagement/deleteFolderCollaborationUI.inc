<?php
/**
 * $Id$
 *
 * Presentation information for deletion of folder collaboration entry. 
 * Will be used by deleteFolderCollaborationBL.php.
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

function getFolderPath($iFolderID) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sFolderPathLink = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse");	    
	return "<table border=\"0\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sFolderPathLink</td></tr></table>\n";    
}

function getPage($iFolderID, $iGroupID, $iRoleID, $iSequenceNumber) {
	global $default;
    $sToRender = renderHeading(_("Delete Folder Collaboration Step"));
    $sToRender .= getFolderPath($iFolderID);
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Group</td><td>" . getGroup($iGroupID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Role</td><td>" . getRole($iRoleID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Sequence No.</td><td>$iSequenceNumber</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/delete.gif\" border=\"0\"/></td>";
	$sToRender .= "<td>" . generateControllerLink("editFolder", "fFolderID=$iFolderID&fShowSection=folderRouting", "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/>") . "</td>";
	$sToRender .= "</tr></table></td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "</table>\n";
	
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\t return confirm('Are you sure you wish to delete this folder collaboration step?');}\n";	
	$sToRender .= "//-->\n</script>\n\n";
	
	return $sToRender;
}

function getGroup($iGroupID) {
	$oGroup = Group::get($iGroupID);
	return $oGroup->getName();
}

function getRole($iRoleID) {	
	$oRole = Role::get($iRoleID);
	return $oRole->getName();
}
?>
