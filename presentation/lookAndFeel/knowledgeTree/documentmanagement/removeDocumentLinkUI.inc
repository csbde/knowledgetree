<?php
/**
* Presentation logic for removing a link between two documents
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 22 May 2003
*/

/*function renderHeading() {
    global $default;
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"600\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">Unlink document</font></th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}*/

function getPage($sParentDocumentName, $sChildDocumentName, $iParentDocumentID) {
	global $default;
	$sToRender = renderHeading("Unlink document");
	$sToRender .= displayDocumentPath($iParentDocumentID);
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>You have chosen to delete the link between the document '$sParentDocumentName' and the document '$sChildDocumentName'</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td>Select 'Delete' to confirm or 'Cancel' to cancel</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "</tr>\n";	;
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/delete.gif\" border=\"0\"></td> <td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $iParentDocumentID . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td></tr></table></td>";	
	$sToRender .= "</tr>";
	$sToRender .= "</table>";
	return $sToRender;
}

?>