<?php
/**
 * $Id$
 *
 * HTML information for document stuff.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
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
		$sDocumentName = $oDocument->getName();
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

    $sLinkText = $oDocument->getName();
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

    $sLinkText = $oDocument->getName();
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
