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
	return displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse");		
}


function getFolderData($iFolderID) {
	global $default;
	$sQuery = "SELECT F.id, F.name AS folder_name, F.description, F.is_public " .
			"FROM $default->owl_folders_table AS F " .
			"WHERE F.id = $iFolderID";
	
	$aDisplayColumns = array("folder_name", "description", "is_public");
	$aStoreColumns = array("name", "description" , "is_public");
	$aColumnNames = array("Name", "Description", "Public");
	$aDisplayColumnTypes = array(1,1,2);
	$aDatabaseColumnTypes = array(1,1,2);
	$aDropDownListTableNames = array(2 => "$default->owl_document_types_table");
	
	$oPatternEditableListFromQuery = & new PatternEditableListFromQuery($sQuery, $default->owl_folders_table, $aDisplayColumns, $aStoreColumns, $aColumnNames, $aDisplayColumnTypes, $aDatabaseColumnTypes);
	$oPatternEditableListFromQuery->setUniqueName("folderEdit");
	$oPatternEditableListFromQuery->setDropDownListTableNames($aDropDownListTableNames);
	$oPatternEditableListFromQuery->setColumnsRequired(array(1,0,1,0));
	
	return $oPatternEditableListFromQuery->render();
	
}

function getFolderDocTypes($iFolderID) {
	global $default;
	$sQuery = "SELECT FDL.id, FDL.folder_id, FDL.document_type_id, DTL.name, 'Remove' AS del " .
				"FROM $default->owl_folder_doctypes_table AS FDL INNER JOIN $default->owl_document_types_table AS DTL ON FDL.document_type_id = DTL.id " .
				"WHERE FDL.folder_id = $iFolderID " .
				"ORDER BY DTL.name ASC";
	
	$aColumns = array("name", "del");
	$aColumnHeaders = array("","");
	$aColumnTypes = array(1,3,3);
	$aDBColumnArray = array("id","folder_id");
	$aQueryStringVariableNames = array("fFolderDocTypeID", "fFolderID");
	$aLinkURLs = array(1=>"$default->rootUrl/control.php?action=deleteFolderDocType");
	
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "90%", $aLinkURLs, $aDBColumnArray,$aQueryStringVariableNames);
	$oPatternTableSqlQuery->setTableHeading("Document Types");
	$oPatternTableSqlQuery->setDisplayColumnHeadings(true);
	return $oPatternTableSqlQuery->render();
	
}

function getFolderRouting($iFolderID) {
	global $default;
	$sQuery = "SELECT GFAL.id as id, GFAL.folder_id AS folder_id, G.name AS group_name, R.name AS role_name, GFAL.precedence AS precedence, 'Edit' as edit, 'Delete' as del " .	
				"FROM $default->owl_groups_folders_approval_table AS GFAL INNER JOIN roles AS R ON GFAL.role_id = R.id " .
				"INNER JOIN $default->owl_groups_table AS G ON G.id = GFAL.group_id " .
				"WHERE GFAL.folder_id = $iFolderID " .
				"ORDER BY GFAL.precedence, role_name ASC";
	
	$aColumns = array("group_name", "role_name", "precedence", "edit", "del");
	$aColumnHeaders = array("Group", "Role", "Seq", "", "");
	$aColumnTypes = array(1,1,1,3,3);
	$aDBColumnArray = array("id","folder_id");
	$aQueryStringVariableNames = array("fFolderCollaborationID", "fFolderID");
	$aLinkURLs = array(3=>"$default->rootUrl/control.php?action=modifyFolderCollaboration",4=>"$default->rootUrl/control.php?action=deleteFolderCollaboration");
	
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "90%", $aLinkURLs, $aDBColumnArray,$aQueryStringVariableNames);
	$oPatternTableSqlQuery->setTableHeading("Document Routing");
	$oPatternTableSqlQuery->setDisplayColumnHeadings(true);
	return $oPatternTableSqlQuery->render();
	
}

function getPage($iFolderID) {
	global $default;	
	$sToRender = "<table border=\"1\" width=\"100%\"><tr><td>" . getFolderPath($iFolderID) . "</td></tr></table>\n";
	
	$sToRender .= "<table border  =\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td valign=\"top\">\n";
	$sToRender .= getLeftSide($iFolderID);
	$sToRender .= "</td>\n";
	$sToRender .= "<td valign=\"top\">\n";
	$sToRender .= getRightSide($iFolderID);
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	/*$sToRender = "<table border=\"1\" width=\"100%\"><tr><td>" . getFolderPath($iFolderID) . "</td></tr></table>\n"; 
	$sToRender .= "<table border  =\"0\">\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td valign=\"top\">" . wrapInTable(getFolderData($iFolderID)) . "</td>\n";
	$sToRender .= "<td>" . wrapInTable(getFolderDocTypes($iFolderID)) . "</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" border=\"0\" /></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>" . wrapInTable(getFolderRouting($iFolderID)) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=addFolderCollaboration&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\" /></a></td>\n";
	$sToRender .= "</table>\n";*/
	return $sToRender;
}

function getLeftSide($iFolderID) {
	global $default;
	$sToRender .= "<table border  =\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= wrapInTable(getFolderData($iFolderID));
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= "<input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" border=\"0\" />\n";
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .=  wrapInTable(getFolderRouting($iFolderID)) . "\n";
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .=  "<a href=\"$default->rootUrl/control.php?action=addFolderCollaboration&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\" /></a>\n";
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

function getRightSide($iFolderID) {
	global $default;
	$sToRender .= "<table border  =\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= wrapInTable(getFolderDocTypes($iFolderID));
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .=  "<a href=\"$default->rootUrl/control.php?action=addFolderDocType&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\" /></a>\n";
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

function wrapInTable($sHtml) {
	return "\n\t\t\t<table border = 1, width = 100%><tr><td>$sHtml</td></tr></table>\n";	
}
?>
