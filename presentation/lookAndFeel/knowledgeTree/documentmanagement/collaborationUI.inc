<?php
/**
 * $Id$
 *  
 * Presentation data for collaborationBL.php
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */

function getGroupRow($iGroupID) {	
	$oGroup = Group::get($iGroupID);
	return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $iGroupID . "\" />" . $oGroup->getName();
}

function getUserDropDown($iGroupID, $iUserID) {
	global $default;	
	$oPatternListBox = & new PatternListBox("$default->owl_users_table", "name", "id", "fUserID");			
	$sFromClause = "INNER JOIN $default->owl_users_groups_table AS UGL ON ST.id = UGL.user_id ";
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
	$oPatternListBox = & new PatternListBox("$default->owl_roles_table", "name", "id", "fRoleID");
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
	return 	renderHeading("Edit Document Approval Routing") .
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
