<?php

/**
* Presentation information for documentDeleteBL.php
* 
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 19 February 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
* 
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

function getDocumentPath($iFolderID, $sDocumentName) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	    
	$sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID), "$default->rootUrl/control.php?action=browse") . " > " . $sDocumentName;
	return "<table border=\"0\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sDocumentPath</td></tr></table>\n";	
}

function getPage($iDocumentID, $iFolderID, $sDocumentName) {
	global $default;
    $sToRender  = renderHeading("Delete Document");
	$sToRender .= getDocumentPath($iFolderID, $sDocumentName) . "<br>\n";
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
	$sToRender .= "<td><table><tr><td><a href=\"" . $_SERVER["PHP_SELF"] . "?fDocumentID=$iDocumentID&fDeleteConfirmed=1\"><img src=\"$default->graphicsUrl/widgets/delete.gif\" border=\"0\"></a></td> <td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $iDocumentID . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td></tr></table></td>";	
	$sToRender .= "</tr>";
	$sToRender .= "</table>\n";
	
	return $sToRender;
	
}

function renderErrorPage($sErrorMessage, $iDocumentID = "") {
    global $default;
    if ($iDocumentID) {
    	return statusPage("Delete Document", "", $sErrorMessage, "viewDocument", "fDocumentID=$iDocumentID");
    } else {
	    $sToRender  = renderHeading("Delete Document");    
	    $sToRender .= "<table>\n";
	    $sToRender .= "<tr><td><p class=\"errorText\">$sErrorMessage</p>\n" .
	                  "<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"/></a>\n" .
	                  "</p></td></tr></table>\n";
	   return $sToRender;
	}
}
?>
