<?php
/**
 * $Id$
 *
 * Presentation information used for folder data editing.
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
 * @package foldermanagement
 */

function getFolderData($iFolderID, $sStatusMessage = "") {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sQuery = "SELECT F.id, F.name, F.description, F.is_public " .
			"FROM $default->folders_table AS F " .
			"WHERE F.id = $iFolderID";
			
	$sql = $default->db;
	$sql->query($sQuery);
	
    $sToRender .= renderHeading(_("Modify Folder Properties"));
    $sToRender .= renderFolderPath($iFolderID, generateControllerUrl("browse", "", false));	
	$sToRender .= "<table>";
	$sToRender .= "<tr><td>";
	$sToRender .= "<table cellpadding=\"5\" border=\"0\" width=\"610\">\n";
	$sToRender .= "<caption><strong>Folder Data</strong></caption>";
	if ($sql->next_record()) {
		$sToRender .= "<tr>\n";
		$sToRender .= "<td bgcolor=\"" . $sTDBGColour . "\">Name</td>\n";
		if (Folder::folderIsUnitRootFolder($iFolderID)) {
			$sToRender .= "<td>" . $sql->f("name") . "</td>\n";
		} else {
			$sToRender .= "<td><input type=\"text\" name=\"fFolderName\" value=\"" . $sql->f("name") . "\" size=\"50\"></td>\n";
		}
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td bgcolor=\"" . $sTDBGColour . "\">Description</td>\n";
		$sToRender .= "<td><input type=\"text\" name=\"fDescription\" value=\"" . $sql->f("description") . "\" size=\"50\"></td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td bgcolor=\"" . $sTDBGColour . "\">Public</td>\n";
		$sToRender .= "<td><input type=\"checkbox\" name=\"fIsPublic\" " . ($sql->f("is_public") ? "CHECKED" : "") ." ></td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td></td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<input type=\"hidden\" name=\"fFolderID\" value=\"$iFolderID\">";
		$sToRender .= "<input type=\"hidden\" name=\"fForUpdate\" value=\"1\">";
		
	}
	$sToRender .= "</table></td></tr>";
	$sToRender .= "<tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" border=\"0\" />";
	$sToRender .= generateControllerLink("editFolder", "fFolderID=$iFolderID", "<image src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/>") . "</td></tr>\n";
	$sToRender .= "</table>\n";

    return $sToRender;
}
?>
