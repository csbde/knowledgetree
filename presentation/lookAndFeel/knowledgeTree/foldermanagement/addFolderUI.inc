<?php
/**
* Presentation page that provides HTML for adding a new folder (used by
* addFolderBL.php)
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 27 January 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*
*/

function renderFolderPath($fFolderID) {	
	$sFolderPath = displayFolderPathLink(Folder::getFolderPathAsArray($fFolderID, "$default->owl_ui_directory/foldermanagement/addFolderBL.php"));
	return "<table border=1 width = 100%><tr><td>$sFolderPath</td></tr></table>\n";	
}

function renderFolderList($fFolderID) {
	global $default;
	$sQuery = "SELECT F.id AS id, F.name AS name " .
				"FROM $default->owl_folders_table AS F " .
				"WHERE F.parent_id = " . $fFolderID;
	
	$aColumns = array("name");
	$aColumnTypes = array(3);
	$aColumnHeaderNames = array("Folder");
	
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaderNames, "100%", "$default->owl_root_url/control.php?action=addFolder&fFolderID=", "id");
	$oPatternTableSqlQuery->setImageURL("$default->owl_fs_root/graphics/widgets/dfolder.gif");
	$oPatternTableSqlQuery->setEmptyTableMessage("This folder contains no sub folders");
	return $oPatternTableSqlQuery->render();
}

function renderPage($fFolderID) {	 
	$sToRender = "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "\t<td>" . renderFolderPath($fFolderID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "\t<td>" . renderFolderList($fFolderID) . "<td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}



?>
