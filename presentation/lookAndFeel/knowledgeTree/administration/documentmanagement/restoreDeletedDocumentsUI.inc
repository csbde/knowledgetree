<?php
/**
 * $Id$
 *
 * This page holds all presentation code for expunging documents pages.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.documentmanagement
 */

/**
 * Displays the status of expunged documents
 */
function renderStatusPage($oDocument) {
	global $default;
	
	$sToRender  = renderHeading(_("Restore Deleted Document"));
	$sToRender .= "<table>";
    $sToRender .= "<tr><td>" . _("The document") . " <strong>" . $oDocument->getName() . "</strong> " . _("was successfully restored:") . "</td></tr>\n";
    $sToRender .= "<tr><td>" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID(), $oDocument->getDisplayPath(true)) . "\n";
    $sToRender .= "<tr/><tr/>";
	
    $sToRender .= "<tr><td>" . generateControllerLink("deletedDocuments", "", "<img src=\"" . KTHtml::getBackButton() . "\" border=\"0\"></td></tr>");
	$sToRender .= "</table>";
    return $sToRender;	
}

/**
 * Gives the user a last chance to bail out before restoring the document
 */
function renderConfirmationPage($iDocumentID, $iFolderID) {
	global $default;
    $sToRender = renderHeading(_("Restore Deleted Document"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n<td>" . 
		sprintf(_("Are you sure you want to restore deleted document <strong>%s</strong>"),Document::getDocumentName($iDocumentID)) .
		"</td></tr>";
	$sToRender .= "<tr><td>" . _("to folder") . " <strong>" . Folder::getFolderDisplayPath($iFolderID) . "</strong>?</td></tr>\n";
	$sToRender .= "</tr><tr/><tr/>\n";
	$sToRender .= "<tr><td>" . generateControllerLink("restoreDeletedDocument", "fDocumentID=$iDocumentID&fFolderID=$iFolderID&fForMove=1&fConfirmed=1", "<img src=\"" . KTHtml::getRestoreButton() . "\" border=\"0\"/>") . "&nbsp;";
	$sToRender .= generateControllerLink("restoreDeletedDocument", "fDocumentID=$iDocumentID&fFolderID=$iFolderID", "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"/>") . "</td></tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

/**
 * Displays the folder browse page
 */
function renderFolderBrowsePage($iDocumentID, $iFolderID) {
	global $default;
    $sToRender = renderHeading(_("Restore Deleted Document"));
	$sToRender .= "<table width=100%>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . renderFolderPath($iFolderID, generateControllerUrl("restoreDeletedDocument", "fDocumentID=$iDocumentID", false)) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table width=\"100%\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . renderFolderList($iFolderID, generateControllerUrl("restoreDeletedDocument", "fDocumentID=$iDocumentID", false)) . "</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<tr><td><table><tr><td colspan=\"3\">" . _("Select the folder you would like to restore deleted document") . " <strong>" . Document::getDocumentName($iDocumentID) . "</strong> to.</td></tr>";
	$sToRender .= "<tr/><tr><td><input type=\"image\" src=\"" . KTHtml::getRestoreHereButton() . "\" border=\"0\"/>&nbsp;" . generateControllerLink("deletedDocuments", "", "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\">") . "</td></tr></table></td>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}
?>
