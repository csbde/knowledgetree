<?php

/**
 * $Id$
 *  
 * This page holds all presentation code for expunging documents pages. 
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.administration.documentmanagement
 */

/**
 * Displays the status of expunged documents
 */
function renderStatusPage($oDocument) {
	global $default;
	
	$sToRender  = renderHeading("Restore Deleted Document Status");
	$sToRender .= "<table>";
    $sToRender .= "<tr><td>The document <strong>" . $oDocument->getName() . "</strong> was successfully restored:</td></tr>\n";
    $sToRender .= "<tr><td>" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID(), $oDocument->getDisplayPath(true)) . "\n";
    $sToRender .= "<tr/><tr/>";
	
    $sToRender .= "<tr><td>" . generateControllerLink("deletedDocuments", "", "<img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"></td></tr>");
	$sToRender .= "</table>";
    return $sToRender;	
}

/**
 * Gives the user a last chance to bail out before restoring the document
 */
function renderConfirmationPage($iDocumentID, $iFolderID) {
	global $default;
    $sToRender = renderHeading("Restore Deleted Document");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n<td>Are you sure you want to restore deleted document <strong>" . Document::getDocumentName($iDocumentID) . "</strong></td></tr>";
	$sToRender .= "<tr><td>to folder <strong>" . Folder::getFolderDisplayPath($iFolderID) . "</strong>?</td></tr>\n";
	$sToRender .= "</tr><tr/><tr/>\n";
	$sToRender .= "<tr><td>" . generateControllerLink("restoreDeletedDocument", "fDocumentID=$iDocumentID&fFolderID=$iFolderID&fForMove=1&fConfirmed=1", "<img src=\"$default->graphicsUrl/widgets/restore.gif\" border=\"0\"/>") . "&nbsp;";
	$sToRender .= generateControllerLink("restoreDeletedDocument", "fDocumentID=$iDocumentID&fFolderID=$iFolderID", "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/>") . "</td></tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

/**
 * Displays the folder browse page
 */
function renderFolderBrowsePage($iDocumentID, $iFolderID) {
	global $default;
    $sToRender = renderHeading("Restore Deleted Document");
	$sToRender .= "<table width=100%>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . renderFolderPath($iFolderID, "/control.php?action=restoreDeletedDocument&fDocumentID=$iDocumentID") . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table width=\"100%\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . renderFolderList($iFolderID, "control.php?action=restoreDeletedDocument&fDocumentID=$iDocumentID") . "</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<tr><td><table><tr><td colspan=\"3\">Select the folder you would like to restore deleted document <strong>" . Document::getDocumentName($iDocumentID) . "</strong> to.</td></tr>";
	$sToRender .= "<tr/><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/restorehere.gif\" border=\"0\"/>&nbsp;" . generateControllerLink("deletedDocuments", "", "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\">") . "</td></tr></table></td>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}
?>