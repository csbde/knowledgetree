<?php
/**
 * $Id$
 *
 * Presentation data for collaborationBL.php.
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

function getGroupDropDown($iFolderCollaborationID, $iFolderID, $iGroupID) {
	global $default;
	$oPatternListBox = & new PatternListBox($default->groups_table, "Name", "ID", "fGroupID");
	$oPatternListBox->setPostBackOnChange(true);
	$oPatternListBox->setOnChangeAction("setActionAndSubmit('" . $_SERVER["userID"] . "?fFolderID=$iFolderID&fFolderCollaborationID=$iFolderCollaborationID');");
	if (isset($iGroupID)) {
		$oPatternListBox->setSelectedValue($iGroupID);
	}
	return $oPatternListBox->render();
	
}

function getUserDropDown($iGroupID, $iUserID) {
	global $default;
	if (!(isset($iGroupID))) {
		$iGroupID = -1;
	}
	$oPatternListBox = & new PatternListBox($default->users_table, "Name", "ID", "fUserID", "UGL.group_id = $iGroupID");
	$oPatternListBox->setFromClause("INNER JOIN $default->users_groups_table AS UGL ON UGL.user_id = ST.id");	
	if (isset($iUserID)) {
		$oPatternListBox->setSelectedValue($iUserID);
	}
	return $oPatternListBox->render();
	
}

function getEditPage($iFolderCollaborationID, $iFolderID, $iGroupID, $iUserID, $iRoleID, $iSequenceNumber) {
	global $default;
	$sToRender = renderHeading(_("Edit Folder Collaboration Step"));
    $sToRender .= getFolderPath($iFolderID);
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Group") . "</td><td>" . getGroupDropDown($iFolderCollaborationID, $iFolderID, $iGroupID) . "</td>\n";
	$sToRender .= "</tr>";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("User") . "</td><td>" . getUserDropDown($iGroupID, $iUserID) . "</td>\n";
	$sToRender .= "</tr>";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Role") . "</td><td>" . getRoleDropDown($iRoleID) . "</td>\n";
	$sToRender .= "</tr>";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Sequence") . "</td><td><input type=\"text\" name=\"fSequenceNumber\" value=\"$iSequenceNumber\" /></td>\n";
	$sToRender .= "</tr>";			
	$sToRender .= "<tr><td><input type=\"image\" src=\"" . KTHtml::getUpdateButton() . "\" border=\"0\"/></td>";
	$sToRender .= "<td>" . generateControllerLink("editFolder", "fFolderID=$iFolderID&fShowSection=folderRouting", "<img border=\"0\" src=\"" . KTHtml::getCancelButton() . "\"/>")  . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(theForm.fGroupID,'" . _("Group") . "')) || !(validRequired(theForm.fUserID,'" . _("User") . "')) || !(validRequired(theForm.fRoleID,'" . _("Role") . "')) || !(validNum(theForm.fSequenceNumber,'" . _("Sequence") . "')) || !(validRequired(theForm.fSequenceNumber,'" . _("Sequence") . "'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";	
	
	return $sToRender;
}

function getRoleDropDown($iRoleID) {
	global $default;
	$oPatternListBox = & new PatternListBox("$default->roles_table", "name", "id", "fRoleID");
	$oPatternListBox->setEmptyErrorMessage(_("There are no users in this group"));
	if (isset($iRoleID)) {
		$oPatternListBox->setSelectedValue($iRoleID);
	}
	
	return $oPatternListBox->render();
}
?>
