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

function renderBrowseAddPage($fFolderID) {
    global $default;
    $sToRender = "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderPath($fFolderID, "$default->owl_ui_directory/foldermanagement/addFolderBL.php") . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    $sToRender .= "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderList($fFolderID, "control.php?action=addFolder") . "<td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table><br>\n";
    $sToRender .= "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td colspan=\"2\"><b>New folder name:<b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td><input type=\"text\" name=\"fFolderName\" value=\"\" /></td>\n";
    $sToRender .= "<td><input type=\"image\" src=\"$default->owl_graphics_url/widgets/add.gif\"></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

function renderBrowsePage($fFolderID) {
    global $default;
    $sToRender = "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderPath($fFolderID, "$default->owl_ui_directory/foldermanagement/addFolderBL.php") . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    $sToRender .= "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderList($fFolderID, "control.php?action=addFolder") . "<td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table><br>\n";

    return $sToRender;
}
?>
