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
    $sToRender .= "<tr><td><input type=\"hidden\" name=\"fDocumentID\" value=\"" . $oDocument->getID() . "\"/></td></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
    //$sToRender .= "<td><input onclick=\"alert('You have now checked out this document. No one else can make updates to the document while you have it checked out. Save the latest version of the document and please check it back in as soon as you finish working on it.');\" type=\"image\" border=\"0\" src =\"$default->graphicsUrl/widgets/checkout.gif\" value=\"Submit\"/>\n";
    $sToRender .= "<td><input type=\"image\" border=\"0\" src =\"$default->graphicsUrl/widgets/checkout.gif\" value=\"Submit\"/>\n";
	$sToRender .= generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID(), "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\">") . "</td>\n";	    
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;    
}

/**
 * Displays the document view page with additional input controls
 * for checking out the document
 *
 * @param object the document to view and check out
 */
function getCheckOutPage($oDocument, $bCheckedOut = false) {
    global $default;

	$sToRender .= renderHeading("Document Detail");
	$sToRender .= renderDocumentPath($oDocument, false) . "\n\n";
    if ($bCheckedOut) {
    	$sToRender .= "<table>";
        $sToRender .= "<tr><td>You have now checked out this document. No one else can make updates to the document while you have it checked out. Click the Download button to get the latest version of the file. Please check it back in, as soon as you finish working on it.</td></tr>\n";
        $sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=downloadDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/download.gif\" border=\"0\" /></a>\n";    
        $sToRender .= "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a></td>\n";
        $sToRender .= "</table>";        
    } else {
        $sToRender .= renderCheckOutPage($oDocument);
    }
   	$sToRender .= renderDocumentData($oDocument, $bEdit);       	
    return $sToRender;
}
?>