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

function renderBrowseAddPage($iFolderID) {
    global $default;
	$sToRender = "<table border=\"0\" width=\"100%\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderPath($iFolderID, "$default->uiUrl/foldermanagement/addFolderBL.php") . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    $sToRender .= "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
	$sToRender .= "<td valign=\"top\" width=\"300\">\n";
	$sToRender .= wrapInTable(renderFolderList($iFolderID, "control.php?action=addFolder"));
	$sToRender .= "</td>\n";
	$sToRender .= "<td valign=\"top\">\n";
	$sToRender .= getRightSide($iFolderID);
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table><br>\n";
	
    return $sToRender;
}

function getRightSide($iFolderID) {
	global $default;
	$sToRender .= "<table border=\"0\">\n";    
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><b>Folder name: </b></td>\n";
    $sToRender .= "<td><input type=\"text\" name=\"fFolderName\" value=\"\" /></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><b>Doc Type: </b></td>\n";
	$sToRender .= "<td>" . getDocumentTypeDropDown() . "</td>\n";
	$sToRender .= "</tr>\n";    
	$sToRender .= "<td><table border=\"0\"><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\"></td><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td></tr></table></td><td>&nbsp</td>\n";    
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

function getDocumentTypeDropDown() {
	global $default;
	$oPatternListBox = & new PatternListBox("$default->owl_document_types_table", "name", "id", "fDocumentTypeID");
	$oPatternListBox->setIncludeDefaultValue(false);
	return $oPatternListBox->render();
}

function renderBrowsePage($fFolderID) {
    global $default;
    $sToRender = "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderPath($fFolderID, "$default->uiUrl/foldermanagement/addFolderBL.php") . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    $sToRender .= "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderList($fFolderID, "control.php?action=addFolder") . "<td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table><br>\n";

    return $sToRender;
}

function wrapInTable($sHtml) {
	return "\n\t\t<table border = 0, width = 100%><tr><td>$sHtml</td></tr></table>\n";	
}

?>
