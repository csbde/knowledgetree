<?php
/**
 * $Id$
 *
 * Collaboration rollback UI functions.
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */
function getDocumentPath($iFolderID, $iDocumentID, $sDocumentName) {
    global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	
    $sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID), "$default->rootUrl/control.php?action=browse") . " > <a href=\"$default->rootUrl/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php?fDocumentID=" . $iDocumentID . "&fForInlineView=1\" target=\"View document\">" . $sDocumentName . "</a>";
    return "<table border=0 cellpadding=\"5\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sDocumentPath</td></tr></table>\n";
}

function getPage($iFolderID, $iDocumentID, $sDocumentName) {
	global $default;
	$sToRender = getDocumentPath($iFolderID, $iDocumentID, $sDocumentName);	
	$sToRender .= "<p>" . _("You have chosen to reject the previous collaboration step for the document.  Please provide a reason") . "</p>\n";	
	$sToRender .= "<br>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><b>" . _("Comment") . "<b></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><textarea cols=\"40\" rows=\"5\" name=\"fComment\"></textarea></td>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	$sToRender .= "<input type=\"image\" src=\"" . KTHtml::getDoneButton() . "\" border=\"0\" />\n";
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=$iDocumentID\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\" /></a>\n";
	
	
	
	return $sToRender . getValidationJavaScript();
}

function getValidationJavaScript() {
	$sToRender = "\n\n<SCRIPT LANGUAGE=\"javascript\">\n ";
	$sToRender .= "<!--\n";		
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(document.MainForm.fComment, 'Comment'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "\tdocument.MainForm.submit();\n}\n";
	$sToRender .= "-->\n";
	$sToRender .= "</SCRIPT>\n\n";
	return $sToRender;

}

?>
