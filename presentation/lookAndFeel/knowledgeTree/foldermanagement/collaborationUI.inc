<?php
/**
* Presentation data for collaborationBL.php
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 28 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*
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
	$oPatternListBox = & new PatternListBox($default->owl_groups_table, "Name", "ID", "fGroupID");
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
	$oPatternListBox = & new PatternListBox($default->owl_users_table, "Name", "ID", "fUserID", "UGL.group_id = $iGroupID");
	$oPatternListBox->setFromClause("INNER JOIN $default->owl_users_groups_table AS UGL ON UGL.user_id = ST.id");	
	if (isset($iUserID)) {
		$oPatternListBox->setSelectedValue($iUserID);
	}
	return $oPatternListBox->render();
	
}

function getEditPage($iFolderCollaborationID, $iFolderID, $iGroupID, $iUserID, $iRoleID, $iSequenceNumber) {
	global $default;
	$sToRender = renderHeading("Edit Folder Collaboration Step");
    $sToRender .= getFolderPath($iFolderID);
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Group</td><td>" . getGroupDropDown($iFolderCollaborationID, $iFolderID, $iGroupID) . "</td>\n";
	$sToRender .= "</tr>";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>User</td><td>" . getUserDropDown($iGroupID, $iUserID) . "</td>\n";
	$sToRender .= "</tr>";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Role</td><td>" . getRoleDropDown($iRoleID) . "</td>\n";
	$sToRender .= "</tr>";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Sequence</td><td><input type=\"text\" name=\"fSequenceNumber\" value=\"$iSequenceNumber\" /></td>\n";
	$sToRender .= "</tr>";			
	$sToRender .= "<tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" border=\"0\"/></td>";
	$sToRender .= "<td>" . generateControllerLink("editFolder", "fFolderID=$iFolderID&fShowSection=folderRouting", "<img border=\"0\" src=\"$default->graphicsUrl/widgets/cancel.gif\"/>")  . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(theForm.fGroupID,'Group')) || !(validRequired(theForm.fUserID,'User')) || !(validRequired(theForm.fRoleID,'Role')) || !(validNum(theForm.fSequenceNumber,'Sequence no.')) || !(validRequired(theForm.fSequenceNumber,'Sequence no.'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";	
	
	return $sToRender;
}

function getRoleDropDown($iRoleID) {
	global $default;
	$oPatternListBox = & new PatternListBox("$default->owl_roles_table", "name", "id", "fRoleID");		
	$oPatternListBox->setEmptyErrorMessage("There are no users in this group");		
	if (isset($iRoleID)) {
		$oPatternListBox->setSelectedValue($iRoleID);
	}
	
	return $oPatternListBox->render();
}
?>