<?php
/**
 * $Id$
 *
 * Presentation information for adding a new step to the folder collaboration flow
 * Used by addFolderCollaborationBL.ph
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

function getPage($iFolderID, $iGroupID, $iRoleID, $iSequenceNumber) {
	global $default;
    $sToRender = renderHeading("Add Folder Collaboration Step");
    $sToRender .= getFolderPath($iFolderID);
	$sToRender .= "<br>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Group</td><td>" . getGroupDropDown($iGroupID, $iFolderID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>User</td><td>" . getUserDropDown($iGroupID) . "</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Role</td><td>" . getRoleDropDown($iRoleID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Sequence No.</td><td><input type=\"text\" name=\"fSequenceNumber\" size=\"5\" " . (isset($iSequenceNumber) ? "value=\"$iSequenceNumber\" " : " ") . "/></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\"/></td><td>" . generateControllerLink("editFolder", "fFolderID=$iFolderID&fShowSection=folderRouting", "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/>") . "</td></tr></table></td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "</table>\n";
	
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(theForm.fGroupID,'Group')) || !(validRequired(theForm.fUserID,'User')) || !(validNum(theForm.fSequenceNumber,'Sequence no.')) || !(validRequired(theForm.fSequenceNumber,'Sequence no.'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";
	
	return $sToRender;
}

function getFolderPath($iFolderID) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sFolderPathLink = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse");	    
	return "<table border=\"0\" width=\"100%\"><tr><td bgcolor=\"$sTDBGColour\">$sFolderPathLink</td></tr></table>\n";    
}

function getGroupDropDown($iGroupID, $iFolderID) {
	global $default;
	$oPatternListBox = & new PatternListBox("$default->groups_table", "name", "id", "fGroupID");
	$oPatternListBox->setPostBackOnChange(true);
	$oPatternListBox->setOnChangeAction("setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fFolderID=$iFolderID');");
	if (isset($iGroupID)) {
		$oPatternListBox->setSelectedValue($iGroupID);		
	}
	

	//this code restricts the user to seeing only
	//the groups in their unit
	/*$oPatternListBox;
    if (Permission::userIsSystemAdministrator()) {
        $oPatternListBox = & new PatternListBox("$default->groups_table", "name", "id", "fGroupID");
    } else {
        $oPatternListBox = & new PatternListBox("$default->groups_table", "name", "id", "fGroupID", "GUL.unit_id=" . User::getUnitID($_SESSION["userID"]));
        $oPatternListBox->setFromClause("INNER JOIN $default->groups_units_table AS GUL ON ST.id = GUL.group_id");
    }                                    
	$oPatternListBox->setEmptyErrorMessage("There are no groups");
	$oPatternListBox->setIncludeDefaultValue(false);
	$oPatternListBox->setPostBackOnChange(true);
	$oPatternListBox->setOnChangeAction("setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fFolderID=$iFolderID');");
	if (isset($iGroupID)) {
		$oPatternListBox->setSelectedValue($iGroupID);
	}*/
	return $oPatternListBox->render();
}

function getUserDropDown($iGroupID) {
	global $default;
	if (!(isset($iGroupID))) {
		$iGroupID = -1;
	}
	
	$oPatternListBox = & new PatternListBox("$default->users_table", "name", "id", "fUserID", "UGL.group_id = $iGroupID");
    $oPatternListBox->setFromClause("INNER JOIN $default->users_groups_table AS UGL ON ST.id = UGL.user_id");
    
    if ($iGroupID == -1) {         
		$oPatternListBox->setEmptyErrorMessage("Select a group");
		$oPatternListBox->setIncludeDefaultValue(false);
	}	
	return $oPatternListBox->render();
}

function getRoleDropDown($iRoleID) {
	global $default;	
	$oPatternListBox = & new PatternListBox("$default->roles_table", "name", "id", "fRoleID");
	$oPatternListBox->setEmptyErrorMessage("There are no roles");
	$oPatternListBox->setIncludeDefaultValue(false);
	if (isset($iRoleID)) {
		$oPatternListBox->setSelectedValue($iRoleID);
	}
	return $oPatternListBox->render();
}
?>
