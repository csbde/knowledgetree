<?php

/** Presentation logic for deleting a dependant document
*
* @author Rob Cherry
* @date 22 May 2002
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement 
*/

/*function renderHeading($sHeading) {
    global $default;
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"600\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\">$sHeading</th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}*/

function getPage($iFolderID, $iFolderCollaborationID, $sDocumentTitle, $sUserName, $sTemplateDocument) {
	global $default;
	$sToRender = renderHeading("Delete dependant document");
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Document title</td><td>$sDocumentTitle</td>";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>User</td><td>$sUserName</td>";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Template document</td><td>$sTemplateDocument</td>";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/delete.gif\" border=\"0\"/></td><td><a href=\"" . $default->rootUrl . "/control.php?action=viewDependantDocument&fFolderID=$iFolderID&fFolderCollaborationID=$iFolderCollaborationID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/></a></td></tr></table>\n";
	$sToRender .= "</table\n";
	
	return $sToRender;	
}

?>