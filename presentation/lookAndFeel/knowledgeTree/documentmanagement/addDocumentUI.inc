<?php
/**
* Presentation data used to add a document.  Used by addDocumentBL.php
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 28 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*
*/

function getDocumentListForFolder($fFolderID) {
	global $default;
	$sQuery = "SELECT D.id AS  id, D.folder_id AS folder_id, D.name AS name, COALESCE(MT.icon_path, '$default->owl_graphics_url/star.gif') AS image_url " . 
			"FROM $default->owl_documents_table AS D LEFT OUTER JOIN $default->owl_mime_table AS MT ON D.mime_id = MT.id " .
			"WHERE folder_id = $fFolderID " .
			"ORDER BY name ASC";
	
	$aColumns = array("name");
	$aColumnTypes = array(3);
	$aColumnHeaderNames = ("Name");
	$aDBColumns = array("id");
	$aQueryStringVariableNames = array("fDocumentID");	
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaderNames, "100%", "$default->owl_root_url/control.php?action=viewDocument", $aDBColumns, $aQueryStringVariableNames);
	$oPatternTableSqlQuery->setEmptyTableMessage("This folder contains no documents");
	$oPatternTableSqlQuery->setUseImageURLFromQuery(true);
	return $oPatternTableSqlQuery->render();
	
}


function getBrowsePage($fFolderID) {
	$sToRender = "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "\t<td>" . renderFolderPath($fFolderID, "$default->owl_ui_directory/documentmanagement/management/addDocument.php") . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "\t<td>" . renderFolderList($fFolderID, "control.php?action=addDocument") . "<td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getDocumentListForFolder($fFolderID) . "</td>\n";
	$sToRender .= "</tr>";
	$sToRender .= "</table><br>\n";
	return $sToRender;
}


function getBrowseAddPage($fFolderID) {
    global $default;
    
	$sToRender = "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "\t<td>" . renderFolderPath($fFolderID, "$default->owl_ui_directory/documentmanagement/management/addDocument.php") . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "\t<td>" . renderFolderList($fFolderID, "control.php?action=addDocument") . "<td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getDocumentListForFolder($fFolderID) . "</td>\n";
	$sToRender .= "</tr>";
	$sToRender .= "</table><br>\n";
	$sToRender .= "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td colspan=\"2\"><b>Document to add:<b></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td><input type=\"file\" name=\"fFile\"></td>\n";
	$sToRender .= "<td><input type=\"image\" src=\"$default->owl_graphics_url/widgets/add.gif\" border=\"0\" value=\"Add\"></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}
?>
