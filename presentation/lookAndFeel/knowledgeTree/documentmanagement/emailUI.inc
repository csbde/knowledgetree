<?php
/**
* Business logic to email a link to a document to a colleauge
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 31 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*
*/

function renderHeading($sHeading) {
    global $default;
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"600\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\">$sHeading</th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

function getDocumentPath($oDocument) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	    
	$sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID()), Folder::getFolderPathNamesAsArray($oDocument->getFolderID()), "$default->rootUrl/control.php?action=browse") . " > " . $oDocument->getName();
	return "<table border=\"0\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sDocumentPath</td></tr></table>\n";	
}

function getDocumentEmailPage($oDocument) {
	global $default;
    $sToRender = renderHeading("Email document link");
	$sToRender .= getDocumentPath($oDocument) . "\n<br>\n";	 
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td>Recipient name:</td><td><input type=\"text\" name=\"fToName\" /></td></tr>\n";
	$sToRender .= "<tr><td>Email address:</td><td><input type=\"text\" name=\"fToEmail\" />(separate multiple addresses with a semicolon)</td></tr>\n";
	$sToRender .= "<tr><td>&nbsp</td><td>&nbsp</td></tr>\n";
	$sToRender .= "<tr><td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/email.gif\" /></td><td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/></a></td></tr></table></td>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}
	
?>
