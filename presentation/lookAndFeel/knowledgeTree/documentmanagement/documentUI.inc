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
 
/**
 * Displays a link to view document details
 * 
 * @param object the document to display
 * @param boolean whether to display the entire path to the document or not
 */
function displayDocumentLink($oDocument, $bDisplayFullPath = false) {
    global $default;
    
    $sIconUrl = $oDocument->getMimeTypeIconUrl();
    $sIconPath = ($sIconUrl ? generateImage($sIconUrl) : generateImage("$default->graphicsUrl/star.gif"));

    $sLinkText = $oDocument->getName();
    if ($bDisplayFullPath) {
        // prepend folder path
        $sLinkText = Folder::getFolderDisplayPath($oDocument->getFolderID()) . " > " . $sLinkText;
    }

    return generateLink("$default->rootUrl/control.php", 
                        "action=viewDocument&fDocumentID=" . $oDocument->getID(),
                        $sIconPath . 
                        $sLinkText);
}

/**
 * Prompts the user for a check in comment and revision type
 *
 * @param object the document we're checking in
 */
function renderCheckInPage($oDocument) {
	global $default;
	
	$sToRender  = "<table>\n";
	$sToRender .= "<tr><td>Select the file to check in:</td></tr>\n";    
	$sToRender .= "<tr><td><input type=\"file\" name=\"fFile\"></td></tr>\n";
    $sToRender .= "</table>\n";
    
	$sToRender .= "<table>\n";  
	$sToRender .= "<tr><td>Enter a check in comment for '" . $oDocument->getName() . "'</td></tr>\n";
	$sToRender .= "<tr/>\n";
	$sToRender .= "<tr><td><input type=\"text\" name=\"fCheckOutComment\" size=\"30\"/></td></tr>\n";
    $sToRender .= "<tr><td><input type=\"hidden\" name=\"fForStore\" value=\"1\"/></td></tr>\n";
    $sToRender .= "<tr><td>\n";
    $sToRender .= "<input type=\"radio\" name=\"fCheckInType\" value=\"major\" checked /> Major CheckIn\n";
    $sToRender .= "<input type=\"radio\" name=\"fCheckInType\" value=\"minor\" checked /> Minor CheckIn\n";
    $sToRender .= "</tr></td>\n";    
    $sToRender .= "</table>\n";
    
    $sToRender .= "<table>\n";    
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><input type=\"image\" src =\"$default->graphicsUrl/widgets/checkin.gif\" value=\"Submit\" />\n";
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td>\n";	    
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}


/**
 * Prompts the user for a checkout comment
 *
 * @param object the document we're checking out
 */
function renderCheckOutPage($oDocument) {
	global $default;
	
	$sToRender  = "<table>\n";
	$sToRender .= "<tr><td>Enter a check out comment for '" . $oDocument->getName() . "'</td></tr>\n";
	$sToRender .= "<tr/>\n";
	$sToRender .= "<tr><td><input type=\"text\" name=\"fCheckOutComment\" size=\"30\"/></td></tr>\n";
    $sToRender .= "<tr><td><input type=\"hidden\" name=\"fForStore\" value=\"1\"/></td></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
    $sToRender .= "<td><input type=\"image\" src =\"$default->graphicsUrl/widgets/checkout.gif\" value=\"Submit\" />\n";
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td>\n";	    
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;    
}
?>
