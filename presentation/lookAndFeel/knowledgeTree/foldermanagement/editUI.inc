<?php
/**
* Presentation information used for folder editing.  Used by editBL.php
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 2 February 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*
*/

function getFolderPath($iFolderID) {
	global $default;
	return displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), "$default->owl_root_url/control.php?action=browse");		
}


function getFolderData($iFolderID) {
	global $default;
	$sQuery = "SELECT F.id, F.name AS folder_name, F.description, F.document_type_id, DTL.name AS document_type_name, F.is_public " .
			"FROM $default->owl_folders_table AS F INNER JOIN $default->owl_document_types_table AS DTL ON F.document_type_id = DTL.ID " .
			"WHERE F.id = $iFolderID";
	
	$aDisplayColumns = array("folder_name", "description", "document_type_name", "is_public");
	$aDisplayColumns = array("folder_name", "description" , "document_type_id", "is_public");
	$aColumnNames = array("Name", "Description", "Document type", "Public");
	$aDisplayColumnTypes = array(1,1,3,2);
	$aDatabaseColumnTypes = array(0,1,0,2);
	$aDropDownListTableNames = array(2 => "$default->owl_document_types_table");
	
	$oPatternEditableListFromQuery = & new PatternEditableListFromQuery($sQuery, $default->owl_folders_table, $aDisplayColumns, $aStoreColumns, $aColumnNames, $aDisplayColumnTypes, $aDatabaseColumnTypes);
	$oPatternEditableListFromQuery->setUniqueName("folderEdit");
	$oPatternEditableListFromQuery->setDropDownListTableNames($aDropDownListTableNames);
	return $oPatternEditableListFromQuery->render();
	
}

function getFolderRouting($iFolderID) {
	global $default;
	$sQuery = "SELECT GFAL.id as id, GFAL.folder_id AS folder_id, G.name AS group_name, R.name AS role_name, GFAL.precedence AS precedence, 'Edit' as edit " .	
				"FROM $default->owl_groups_folders_approval_table AS GFAL INNER JOIN roles AS R ON GFAL.role_id = R.id " .
				"INNER JOIN $default->owl_groups_table AS G ON G.id = GFAL.group_id " .
				"WHERE GFAL.folder_id = $iFolderID " .
				"ORDER BY GFAL.precedence, role_name ASC";
	
	$aColumns = array("group_name", "role_name", "precedence", "edit");
	$aColumnHeaders = array("Group", "Role", "Seq", "");
	$aColumnTypes = array(1,1,1,3);
	$aDBColumnArray = array("id","folder_id");
	$aQueryStringVariableNames = array("fFolderCollaborationID", "fFolderID");
	
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "90%", "$default->owl_root_url/control.php?action=modifyFolderRouting",$aDBColumnArray,$aQueryStringVariableNames);
	$oPatternTableSqlQuery->setTableHeading("Document Routing");
	$oPatternTableSqlQuery->setDisplayColumnHeadings(true);
	return $oPatternTableSqlQuery->render();
	
}

function getPage($iFolderID) {
	global $default;
	$sToRender = "<table border=\"1\" width=\"100%\"><tr><td>" . getFolderPath($iFolderID) . "</td></tr></table>\n"; 
	$sToRender .= "<table border  =\"0\">\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>" . wrapInTable(getFolderData($iFolderID)) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>" . wrapInTable(getFolderRouting($iFolderID)) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><table><tr><td><a href=\"$default->owl_root_url/control.php?action=addFolderCollaboration\">Add</a></td><td>&nbsp</td></tr></table></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

function wrapInTable($sHtml) {
	return "\n\t\t\t<table border = 1, width = 100%><tr><td>$sHtml</td></tr></table>\n";	
}
?>
