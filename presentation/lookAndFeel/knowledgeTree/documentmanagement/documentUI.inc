<?php
/**
 * $Id$
 *
 * Common document UI functions. 
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
 * @package documentmanagement
 */
 
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");

/**
 *
 */
function displayDocumentPath($iDocumentID) {
	global $default;
	$oDocument = Document::get($iDocumentID);
	if ($oDocument) {
		$iFolderID = $oDocument->getFolderID();
		// #3425 for consistency
		$sDocumentName = $oDocument->getFileName();
		$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
		$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	    
    	$sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse") . " > " . $sDocumentName;
		return "<table border=\"0\" width=\"600\"><tr><td bgcolor=\"$sTDBGColour\">$sDocumentPath</td></tr></table>\n";
	} else {
		return "";
	}	
}

/* only used when users choose a template document when setting up document linking
 * on the folder collaboration page.  Formats the link so that the child window
 * lanuched by the javascript feeds back to the parent window 
*/ 
function displayDocumentLinkForTemplateBrowsing($oDocument, $bDisplayFullPath = false) {
	global $default;
    
    $sIconUrl = $oDocument->getMimeTypeIconUrl();
    $sIconPath = ($sIconUrl ? generateImage($sIconUrl) : generateImage("$default->graphicsUrl/unknown.gif"));

    // #3425 the title is now the filename, and the description is the title
    $sLinkText = $oDocument->getFileName();
    if ($bDisplayFullPath) {
        // prepend folder path
        $sLinkText = Folder::getFolderDisplayPath($oDocument->getFolderID()) . " > " . $sLinkText;
    }
     

    return generateLinkForTemplateDocumentBrowse("$default->rootUrl/control.php", 
                        "action=viewDocument&fDocumentID=" . $oDocument->getID(),
                        $sIconPath . "&nbsp;" . $sLinkText, $oDocument->getName(), $oDocument->getID());
	
	
}

/**
 * Displays a link to view document details
 * 
 * @param object the document to display
 * @param boolean whether to display the entire path to the document or not
 */
 
function displayDocumentLink($oDocument, $bDisplayFullPath = false) {
    global $default;
    
    $sIconUrl = $oDocument->getMimeTypeIconUrl();
    $sIconPath = ($sIconUrl ? generateImage($sIconUrl) : generateImage("$default->graphicsUrl/unknown.gif"));

	// #3425 the title is now the filename, and the description is the title 
    $sLinkText = $oDocument->getFileName();
    if ($bDisplayFullPath) {
        // prepend folder path
        $sLinkText = Folder::getFolderDisplayPath($oDocument->getFolderID()) . " > " . $sLinkText;
    }

    return generateLink("$default->rootUrl/control.php", 
                        "action=viewDocument&fDocumentID=" . $oDocument->getID(),
                        $sIconPath . "&nbsp;" . $sLinkText);
}

function renderErrorPage($sErrorMessage, $iDocumentID = -1) {
    global $default;
    return "<p class=\"errorText\">$sErrorMessage</p>\n" .
           (($iDocumentID == -1) ? 
           "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=$iDocumentID\">" :
           "<a href=\"javascript:history.go(-1)\">") .
           "<img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"/></a>\n";
}
?>
