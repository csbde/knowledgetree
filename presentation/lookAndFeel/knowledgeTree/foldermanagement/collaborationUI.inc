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
	return displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse");		
}

function getFolderCollaborationData($iFolderCollaborationID) {
	global $default;
	$sQuery = "SELECT GFAL.id, GFAL.folder_id, F.name AS folder_name, GFAL.group_id, G.name AS group_name, GFAL.role_id, R.name AS role_name, GFAL.precedence " .
			"FROM $default->owl_groups_folders_approval_table AS GFAL INNER JOIN $default->owl_folders_table AS F ON GFAL.folder_id = F.id " .
			"INNER JOIN $default->owl_groups_table AS G ON GFAL.group_id = G.id " .
			"LEFT OUTER JOIN $default->owl_roles_table AS R ON GFAL.role_id = R.id " .
			"WHERE GFAL.id = $iFolderCollaborationID";
	
	$aDisplayColumns = array("group_name", "role_name", "precedence");
	$aStoreColumns = array("group_id", "role_id", "precedence");
	$aColumnNames = array("Group","Role", "Sequence");
	$aDisplayColumnTypes = array(3,3,1);
	$aDatabaseColumnTypes = array(0,0,1);
	$aDropDownListTables = array($default->owl_groups_table, $default->owl_roles_table);
	
	$oPatternEditableListFromQuery = & new PatternEditableListFromQuery($sQuery, $default->owl_groups_folders_approval_table, $aDisplayColumns, $aStoreColumns, $aColumnNames, $aDisplayColumnTypes, $aDatabaseColumnTypes);
	$oPatternEditableListFromQuery->setDropDownListTableNames($aDropDownListTables);
	$oPatternEditableListFromQuery->setUniqueName("folderCollab");
	$oPatternEditableListFromQuery->setColumnsRequired(0,0,1);
	return $oPatternEditableListFromQuery->render();
			
}

function getEditPage($iFolderCollaborationID, $iFolderID) {
	global $default;
	$sToRender .= "<table border=\"1\" width=\"100%\">\n";	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>" . getFolderPath($iFolderID) . "</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "</table>\n";
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>" . getFolderCollaborationData($iFolderCollaborationID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl\widgets\update.gif\" /></td><td><a href=\"$default->rootUrl/control.php?action=editFolder&fFolderID=" . $iFolderID . "\"><img src=\"$default->graphicsUrl\widgets\cancel.gif\" border=\"0\" /></a></td></tr></table>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";	
	
	return $sToRender;
}

function getGroupDropDown() {	
	global $default;
	$oPatternListBox = & new PatternListBox("$default->owl_groups_table", "name", "id", "fGroupID");
	$oPatternListBox->setIncludeDefaultValue(false);
	$oPatternListBox->setEmptyErrorMessage("There are no groups");
	return $oPatternListBox->render();
}

function getRoleDropDown() {
	global $default;
	$oPatternListBox = & new PatternListBox("$default->owl_roles_table", "name", "id", "fRoleID");	
	$oPatternListBox->setEmptyErrorMessage("There are no users in this group");		
	return $oPatternListBox->render();
}

function getAddPage() {
	global $default;
	return "<table border=0>\n" .
			"<caption><b>Edit folder routing information</b></caption>\n" .
			"<tr>\n" .
			"<td><b>Group</b></td><td>". getGroupDropDown() ."</td>\n" .
			"</tr>\n" .	
			"<tr>\n" .
			"<td><b>Role</b></td><td>".getRoleDropDown()."</td>\n" .
			"</tr>\n" .
			"<tr>\n" .
			"<tr>\n" .
			"<td><b>Seq. no.</b></td><td><input type=\"text\" name=\"fSequenceNumber\" size=\"5\" value=1 /></td>\n" .
			"</tr>\n" .
			"<tr>\n" .
			"<td colspan=\"2\">&nbsp</td>\n" .
			"</tr>\n" .
			"<tr>\n" .
			"<td><b><input type=\"image\" src=\"$default->graphicsUrl\widgets\update.gif\" /></b></td><td><a href=\"$default->rootUrl/control.php?action=editFolder&fFolderID=$iFolderID \"><img src=\"$default->graphicsUrl\widgets\cancel.gif\" border=\"0\" /></a></td>\n" .
			"</tr>\n" .
			"</table>\n";
}
?>
