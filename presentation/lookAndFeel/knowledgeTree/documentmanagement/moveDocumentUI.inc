<?php

function getFolderPath($iFolderID) {
    global $default;
    $sFolderPath = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse");
    return "<table border=1 width = 100%><tr><td>$sFolderPath</td></tr></table>\n";
}

function getPage($iFolderID, $iDocumentID) {
	global $default;
	$sToRender = "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getFolderPath($iFolderID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . renderFolderList($iFolderID, "control.php?action=moveDocument&fDocumentID=$iDocumentID") . "</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<tr><td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/movehere.gif\" /></td></tr></table></td>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

?>
