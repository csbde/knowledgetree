<?php

/**
* Presentation information for documentDeleteBL.php
* 
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 19 February 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
* 
*/

function getDocumentPath($iFolderID, $sDocumentName) {
	global $default;
	$sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), "$default->owl_root_url/control.php?action=browse") . " > " . $sDocumentName;
	return "<table border=1 width = 100%><tr><td>$sDocumentPath</td></tr></table>\n";	
}

function getPage($iDocumentID, $iFolderID, $sDocumentName) {
	global $default;
	$sToRender = getDocumentPath($iFolderID, $sDocumentName) . "<br>\n";
	$sToRender .= "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>You have chosen to delete the document entitled '" . $sDocumentName . "'.</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Select 'Delete' to confirm the deletion, or 'Cancel' to cancel it</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><a href=\"" . $_SERVER["PHP_SELF"] . "?fDocumentID=$iDocumentID&fDeleteConfirmed=1\"><img src=\"$default->owl_graphics_url\widgets\delete.gif\" border=\"0\"></a></td> <td><a href=\"$default->owl_root_url/control.php?action=viewDocument&fDocumentID=" . $iDocumentID . "\"><img src=\"$default->owl_graphics_url\widgets\cancel.gif\" border=\"0\"></a></td></tr></table></td>";	
	$sToRender .= "</tr>";
	$sToRender .= "</table>\n";
	
	return $sToRender;
	
}

?>
