<?php
/**
 * $Id$
 *
 * Presentation information for deleteFolderBL.php.
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

function getFolderPath($iFolderID) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sFolderPath = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse");
	return "<table border=\"0\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sFolderPath</td></tr></table>\n";	
}

function getFolderNotEmptyPage($iFolderID, $iObjectCount, $sObjectType) {
	global $default;
    $sToRender  = renderHeading(_("Delete Folder"));    
	$sToRender .= getFolderPath($iFolderID) . "<br>\n";
	$sToRender .= "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("This folder cannot be deleted while it is not empty.") . "</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Select 'Cancel' to return to the browse page.") . "</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=" . $iFolderID . "\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a></td></tr></table></td>";
	$sToRender .= "</tr>";
	$sToRender .= "</table>\n";
	return $sToRender;    
}

function getConfirmPage($iFolderID, $sFolderName) {
	global $default;
    $sToRender  = renderHeading(_("Delete Folder"));
	$sToRender .= getFolderPath($iFolderID) . "<br>\n";
	$sToRender .= "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("You have chosen to delete the folder entitled") . " '" . $sFolderName . "'.</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Select 'Delete' to confirm the deletion, or 'Cancel' to cancel it") . "</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><a href=\"" . $_SERVER["PHP_SELF"] . "?fFolderID=$iFolderID&fDeleteConfirmed=1\"><img src=\"" . KTHtml::getDeleteButton() . "\" border=\"0\"></a></td> <td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=" . $iFolderID . "\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a></td></tr></table></td>";
	$sToRender .= "</tr>";
	$sToRender .= "</table>\n";
	return $sToRender;
}

?>
