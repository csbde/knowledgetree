<?php
/**
* Business logic to email a link to a document to a colleauge
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 31 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*
*/

function getDocumentPath($oDocument) {
	global $default;
	$sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID()), "$default->owl_root_url/control.php?action=browse") . " > " . $oDocument->getName();
	return "<table border=1 width = 100%><tr><td>$sDocumentPath</td></tr></table>\n";	
}

function getEmailPage($oDocument) {	
	$sToRender = "<table>\n" .
	$sToRender .= "<tr>\n" .
	$sToRender .= "<td><b>Document:</b></td><td>" . getDocumentPath($oDocument) . "</td>\n" .
	$sToRender .= "</tr>\n" .
	$sToRender .= "</table>\n";
	/*
	$sToRender .= "<tr>\n" .
	$sToRender .= "<td><b>Recipient's name:</b></td><td><input type=\"text\" name=\"fToName\" /></td>\n" .
	$sToRender .= "</tr>\n" .
	$sToRender .= "<tr>\n" .
	$sToRender .= "<td><b>Recipient's email address:</b></td><td><input type=\"text\" name=\"fToEmailAddress\" /></td>\n" .
	$sToRender .= "</tr>\n" .
	$sToRender .= "<tr>\n" .
	$sToRender .= "<td><input type=\"Submit\" value=\"Send link\" /></td>\n" .
	$sToRender .= "</tr>\n" .
	$sToRender .= "</table>\n";*/
	return $sToRender;
}

function getDocumentEmailPage($oDocument) {
	global $default;
	$sToRender = getDocumentPath($oDocument) . "\n<br>\n";	 
	$sToRender .= "<table>\n";
	$sToRender .= "<th colspan=\"2\" align=\"left\">Email details</th>\n";	
	$sToRender .= "<tr><td>&nbsp</td><td>&nbsp</td></tr>\n";
	$sToRender .= "<tr><td>Recipient name:</td><td><input type=\"text\" name=\"fToName\" /></td></tr>\n";
	$sToRender .= "<tr><td>Email address:</td><td><input type=\"text\" name=\"fToEmail\" /></td></tr>\n";
	$sToRender .= "<tr><td>&nbsp</td><td>&nbsp</td></tr>\n";
	$sToRender .= "<tr><td><input type=\"image\" src=\"$default->owl_graphics_url/widgets/email.gif\" /></td><td>&nbsp</td></tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}
	
?>
