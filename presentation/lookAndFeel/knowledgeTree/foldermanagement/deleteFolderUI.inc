<?php

/**
 * Presentation information for deleteFolderBL.php
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.foldermanagement
 */

function getFolderPath($iFolderID) {
	global $default;
	$sFolderPath = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), "$default->owl_root_url/control.php?action=browse");
	return "<table border=1 width = 100%><tr><td>$sFolderPath</td></tr></table>\n";	
}

function getFolderNotEmptyPage($iFolderID, $iObjectCount, $sObjectType) {
	global $default;
	$sToRender = getFolderPath($iFolderID) . "<br>\n";
	$sToRender .= "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>There are still $iObjectCount $sObjectType in this folder</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Select 'Cancel' to return to the browse page.</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><a href=\"$default->owl_root_url/control.php?action=browse&fFolderID=" . $iFolderID . "\"><img src=\"$default->owl_graphics_url\widgets\cancel.gif\" border=\"0\"></a></td></tr></table></td>";
	$sToRender .= "</tr>";
	$sToRender .= "</table>\n";
	return $sToRender;    
}

function getConfirmPage($iFolderID, $sFolderName) {
	global $default;
	$sToRender = getFolderPath($iFolderID) . "<br>\n";
	$sToRender .= "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>You have chosen to delete the folder entitled '" . $sFolderName . "'.</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Select 'Delete' to confirm the deletion, or 'Cancel' to cancel it</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><a href=\"" . $_SERVER["PHP_SELF"] . "?fFolderID=$iFolderID&fDeleteConfirmed=1\"><img src=\"$default->owl_graphics_url\widgets\delete.gif\" border=\"0\"></a></td> <td><a href=\"$default->owl_root_url/control.php?action=browse&fFolderID=" . $iFolderID . "\"><img src=\"$default->owl_graphics_url\widgets\cancel.gif\" border=\"0\"></a></td></tr></table></td>";
	$sToRender .= "</tr>";
	$sToRender .= "</table>\n";
	return $sToRender;
}

?>
