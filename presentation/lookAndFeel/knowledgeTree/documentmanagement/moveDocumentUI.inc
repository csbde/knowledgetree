<?php

function getConfirmationPage($iFolderID, $iDocumentID) {
	global $default;
    $sToRender = renderHeading("Move Document");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n<td>Are you sure you want to move </td></tr>";
	$sToRender .= "<tr><td><strong>" . Document::getDocumentDisplayPath($iDocumentID) . "</strong> to </td></tr>\n";
	$sToRender .= "<tr><td><strong>" . Folder::getFolderDisplayPath($iFolderID) . " > " . Document::getDocumentName($iDocumentID) . "</strong></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=moveDocument&fDocumentID=$iDocumentID&fFolderID=$iFolderID&fForMove=1&fConfirmed=1\"><img src=\"$default->graphicsUrl/widgets/move.gif\" border=\"0\"/></a>&nbsp;<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $iDocumentID . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td></tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}


function getPage($iFolderID, $iDocumentID) {
	global $default;
    $sToRender = renderHeading("Move Document");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . renderFolderPath($iFolderID, "/control.php?action=moveDocument&fDocumentID=$iDocumentID") . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table width=\"100%\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . renderFolderList($iFolderID, "control.php?action=moveDocument&fDocumentID=$iDocumentID") . "</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<tr><td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/movehere.gif\" border=\"0\"/></td><td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $iDocumentID . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td></tr></table></td>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

?>
