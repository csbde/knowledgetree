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
 * @package documentmanagement
 */

function getGroupRow($iGroupID) {	
	$oGroup = Group::get($iGroupID);
	return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $iGroupID . "\" />" . $oGroup->getName();
}

function getUserDropDown($iGroupID, $iUserID) {
	global $default;	
	$oPatternListBox = & new PatternListBox("$default->users_table", "name", "id", "fUserID");			
	$sFromClause = "INNER JOIN $default->users_groups_table AS UGL ON ST.id = UGL.user_id ";
	$oPatternListBox->setFromClause($sFromClause);
	$sWhereClause = "UGL.group_id = $iGroupID";
	$oPatternListBox->setWhereClause($sWhereClause);
	$oPatternListBox->setIncludeDefaultValue(false);
	
	if (isset($iUserID)) {
		$oPatternListBox->setSelectedValue($iUserID);
	}
	
	$oPatternListBox->setEmptyErrorMessage("There are no users in this group");
	return $oPatternListBox->render();
}

function getRole($iRoleID) {
	$oRole = Role::get($iRoleID);
	return "<input type=\"hidden\" name=\"fRoleID\" value=\"$iRoleID\" />" . $oRole->getName();
	/*global $default;
	$oPatternListBox = & new PatternListBox("$default->roles_table", "name", "id", "fRoleID");
	if (isset($iRoleID)) {
		$oPatternListBox->setSelectedValue($iRoleID);
	}
	$oPatternListBox->setEmptyErrorMessage("There are no users in this group");
	return $oPatternListBox->render();*/
}

function getDocumentRoutingPage($iGroupID, $iUserID, $iRoleID, $iSequenceNumber, $fDocumentID) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sTHBGColour = $default->siteMap->getSectionColour($sSectionName, "th");
	return 	renderHeading(_("Edit Document Approval Routing")) .
			displayDocumentPath($fDocumentID) .
			"<table cellspacing=2, cellpadding=2, border=0>\n" .
			"<caption><b>Document Routing</b></caption>\n" .
			"<tr>\n" .
			"<td bgcolor=\"$sTDBGColour\"><b>Group</b></td><td>".getGroupRow($iGroupID)."</td>\n" .
			"</tr>\n" .
			"<tr>\n" .			
			"<td bgcolor=\"$sTDBGColour\"><b>Role</b></td><td bgcolor=\"F5F6EE\">".getRole($iRoleID)."</td>\n" .			
			"</tr>\n" .
			"<tr>\n" .
			"<td bgcolor=\"$sTDBGColour\"><b>Player</b></td><td>".getUserDropDown($iGroupID, $iUserID)."</td>\n" .
			"</tr>\n" .
			"<tr>\n" .
			"<tr>\n" .
			"<td bgcolor=\"$sTDBGColour\"><b>Seq. no.</b></td><td bgcolor=\"F5F6EE\">$iSequenceNumber</td>\n" .
			"</tr>\n" .
			"<tr>\n" .
			"<td colspan=\"2\">&nbsp</td>\n" .
			"</tr>\n" .
			"<tr>\n" .
			"<td><b><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" border=\"0\"/></b></td><td>" . generateControllerLink("viewDocument", "fDocumentID=$fDocumentID&fShowSection=documentRouting", "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/>") . "</td>\n" .
			"</tr>\n" .
			"</table>\n";
}
?>
