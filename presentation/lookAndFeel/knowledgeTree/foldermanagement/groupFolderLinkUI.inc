<?php
/**
 * $Id$
 *
 * Presentation information for adding folder access
 * Used by addFolderAccessBL.php
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
	$sFolderPathLink = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse");	    
	return "<table border=\"0\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sFolderPathLink</td></tr></table>\n";    
}

function getDeletePage($oGroupFolderLink, $iFolderID) {
	global $default;
    $sToRender  = renderHeading(_("Delete Folder Access"));
    $sToRender .= getFolderPath($iFolderID);    
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Group") . "</td><td>" . Group::getGroupName($oGroupFolderLink->getGroupID()) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Access Rights") . "</td><td>" . getAccessCheckBoxes(true, $oGroupFolderLink->getCanRead(), $oGroupFolderLink->getCanWrite()) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"" . KTHtml::getDeleteButton() . "\" border=\"0\"/></td><td>" . generateControllerLink("editFolder", "fFolderID=" . $oGroupFolderLink->getFolderID() . "&fShowSection=folderPermissions", "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"/>") . "</td></tr></table></td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "</table>\n";

	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\t return confirm('" . _("Are you sure you wish to delete this folder access entry?") . "');}\n";	
	$sToRender .= "//-->\n</script>\n\n";

	return $sToRender;
}

function getEditPage($oGroupFolderLink, $iFolderID) {
	global $default;
    $sToRender  = renderHeading(_("Edit Folder Access"));
    $sToRender .= getFolderPath($iFolderID);    
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Group") . "</td><td>" . getGroupDropDown($oGroupFolderLink->getGroupID()) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$default->log->info("getEditPage: " . arrayToString($oGroupFolderLink));
	$sToRender .= "<td>" . _("Access Rights") . "</td><td>" . getAccessCheckBoxes(false, $oGroupFolderLink->getCanRead(), $oGroupFolderLink->getCanWrite()) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"" . KTHtml::getUpdateButton() . "\" border=\"0\"/></td><td>" . generateControllerLink("editFolder", "fFolderID=" . $oGroupFolderLink->getFolderID() . "&fShowSection=folderPermissions", "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"/>") . "</td></tr></table></td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "</table>\n";
	
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
    $sToRender .= "\tif (!theForm.fCanRead.checked && !theForm.fCanWrite.checked) {\n";
	$sToRender .= "\t\talert('" . _("You must select either read or write access") . "'); return false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";

	return $sToRender;
}

function getAddPage($iFolderID) {
	global $default;
    $sToRender  = renderHeading(_("Add Folder Access"));
    $sToRender .= getFolderPath($iFolderID);
    
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Group") . "</td><td>" . getGroupDropDown() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Access Rights") . "</td><td>" . getAccessCheckBoxes(false) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"" . KTHtml::getAddButton() . "\" border=\"0\"/></td><td>" . generateControllerLink("editFolder", "fFolderID=$iFolderID&fShowSection=folderPermissions", "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"/>") . "</td></tr></table></td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "</table>\n";
	
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
    $sToRender .= "\tif (!theForm.fCanRead.checked && !theForm.fCanWrite.checked) {\n";
	$sToRender .= "\t\talert('" . _("You must select either read or write access") . "'); return false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";

	return $sToRender;
}

function getAccessCheckBoxes($bReadOnly, $bCanRead = false, $bCanWrite = false) {
    return "<input name=\"fCanRead\" type=\"checkbox\" " . ($bReadOnly ? "disabled " : "") . ($bCanRead ? "checked " : "") . "/>" . _("Read") . "\n" .
           "<input name=\"fCanWrite\" type=\"checkbox\" " . ($bReadOnly ? "disabled " : "") . ($bCanWrite ? "checked " : "") . "/>" . _("Write") . "\n";
}

function getGroupDropDown($iGroupID = -1) {
	global $default;
    if (Permission::userIsSystemAdministrator()) {
        $oPatternListBox = & new PatternListBox("$default->groups_table", "name", "id", "fGroupID");
    } else {
        $oPatternListBox = & new PatternListBox("$default->groups_table", "name", "id", "fGroupID", "GUL.unit_id in (" . implode(",", User::getUnitIDs($_SESSION["userID"])) . ")");
        $oPatternListBox->setFromClause("INNER JOIN $default->groups_units_table AS GUL ON ST.id = GUL.group_id");
    }
	$oPatternListBox->setEmptyErrorMessage(_("There are no groups"));
	$oPatternListBox->setIncludeDefaultValue(false);
    if ($iGroupID != -1) {
        $oPatternListBox->setWhereClause("ST.id = $iGroupID");
    }
	return $oPatternListBox->render();
}

function renderErrorPage($sErrorMessage, $iFolderID) {
    global $default;
    return "<p class=\"errorText\">$sErrorMessage</p>\n" .
           "<a href=\"$default->rootUrl/control.php?action=editFolder&fFolderID=$iFolderID\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\"/></a>\n";
}
?>
