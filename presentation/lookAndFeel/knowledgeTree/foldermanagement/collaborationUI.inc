<?php
/**
* Presentation data for collaborationBL.php
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 28 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*
*/

function getGroupDropDown($iDocumentRoutingID, $fDocumentID) {	
	global $_POST, $default;
	$oPatternListBox = & new PatternListBox("$default->owl_groups_table", "name", "id", "fGroupID");
	$oPatternListBox->setPostBackOnChange(true);
	$sOnChangeAction = $_SERVER["PHP_SELF"] . "?fForFilter=1&fDocumentRoutingID=$iDocumentRoutingID&fDocumentID=$fDocumentID";
	$oPatternListBox->setOnChangeAction("setActionAndSubmit('$sOnChangeAction')");
	if (isset($_POST["fGroupID"])) {
		$oPatternListBox->setSelectedValue($_POST["fGroupID"]);
	}
	$oPatternListBox->setEmptyErrorMessage("There are no groups");
	return $oPatternListBox->render();
}

function getUserDropDown() {
	global $_GET, $_POST, $default;	
	$oPatternListBox = & new PatternListBox("$default->owl_users_table", "name", "id", "fUserID");
	if (isset($_GET["fForFilter"])) {		
		$sFromClause = "INNER JOIN $default->owl_users_groups_table AS UGL ON ST.id = UGL.user_id ";
		$oPatternListBox->setFromClause($sFromClause);
		$sWhereClause = "UGL.group_id = " . $_POST["fGroupID"];
		$oPatternListBox->setWhereClause($sWhereClause);
	}	
		
	$oPatternListBox->setEmptyErrorMessage("There are no users in this group");
	return $oPatternListBox->render();
}

function getRoleDropDown() {
	global $default;
	$oPatternListBox = & new PatternListBox("$default->owl_roles_table", "name", "id", "fRole");	
	$oPatternListBox->setEmptyErrorMessage("There are no users in this group");
	return $oPatternListBox->render();
}

function getDocumentRoutingPage($oDocumentRouting, $fDocumentID) {
	global $default;
	return "<table border=0>\n" .
			"<caption><b>Edit document routing information</b></caption>\n" .
			"<tr>\n" .
			"<td><b>Group</b></td><td>".getGroupDropDown(1, 1)."</td>\n" .
			"</tr>\n" .
			"<tr>\n" .
			"<td><b>User</b></td><td>".getUserDropDown()."</td>\n" .
			"</tr>\n" .
			"<tr>\n" .
			"<td><b>Role</b></td><td>".getRoleDropDown()."</td>\n" .
			"</tr>\n" .
			"<tr>\n" .
			"<tr>\n" .
			"<td><b>Seq. no.</b></td><td><input type=\"text\" size=\"5\" /></td>\n" .
			"</tr>\n" .
			"<tr>\n" .
			"<td colspan=\"2\">&nbsp</td>\n" .
			"</tr>\n" .
			"<tr>\n" .
			"<td><b><input type=\"submit\" value=\"Submit\" /></b></td><td><a href=\"$default->owl_root_url/control.php?action=viewDocument&fDocumentID=$fDocumentID \">Cancel</a></td>\n" .
			"</tr>\n" .
			"</table>\n";
}

/*function getRoutingRow($iSequenceValue, $iIndex) {
	$sToRender = "<tr>\n";
	$sToRender .= "\t<td>" . renderGroupDropDown($iIndex) . "</td>\n";
	$sToRender .= "\t<td>" . renderUserDropDown($iIndex) . "</td>\n";
	$sToRender .= "\t<td>" . renderRoleDropDown($iIndex) . "</td>\n";
	$sToRender .= "\t<td><input type=\"text\" value=\"$iSequenceValue\"</td>\n";
	$sToRender .= "</tr>\n";
	return $sToRender;
}

function something($fDocumentID) {
	$sQuery = "SELECT GFAL.id, R.name AS role_name, COALESCE(U.Name, 'Not assigned') AS name, GFAL.precedence AS precedence " .
				"FROM documents AS D INNER JOIN groups_folders_approval_link AS GFAL ON D.folder_id = GFAL.folder_id " .
				"INNER JOIN roles AS R ON GFAL.role_id = R.id " .
				"LEFT OUTER JOIN folders_users_roles_link AS FURL ON FURL.folder_id = D.folder_id " .
				"LEFT OUTER JOIN users AS U ON FURL.user_id = U.id " .
				"WHERE D.id = $fDocumentID " .
				"ORDER BY GFAL.precedence, role_name ASC";
	$sql = new Owl_DB();
	$sql->query($sQuery);
	
	$sToRender .= "<table border=\"1\">\n";
	$sToRender .= "<th>Group</th>\n";
	$sToRender .= "<th>User</th>\n";
	$sToRender .= "<th>Role</th>\n";
	$sToRender .= "<th>Sequence no.</th>\n";
	
	$iIndex = 0;
	while ($sql->next_record()) {		
		$sToRender .= getRoutingRow($sql->f("id"), $sql->f("precedence"));
		$iIndex++;
	}
	$sToRender .= "</table>\n";	
	
	return $sToRender;
}

function getPage($fSelectedGroupID, $iSequenceValue, $iIndex) {
	$sToRender .= "<table>\n";
	$sToRender .= "<th>Group</th>\n";
	$sToRender .= "<th>User</th>\n";
	$sToRender .= "<th>Sequence no.</th>\n";
	for ($i = 0; $i < 5; $i++) {
		$sToRender .= getRoutingRow($fSelectedGroupID, $iSequenceValue, $i);
	}
	$sToRender .= "</table>\n";
}*/

?>
