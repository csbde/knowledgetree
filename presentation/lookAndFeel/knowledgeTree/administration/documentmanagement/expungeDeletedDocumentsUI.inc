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
 * Gives the user a last chance to bail out before expunging the documents
 */
function renderConfirmDocuments($aDocuments) {
	global $default;
	
	$sToRender  = renderHeading("Expunge Deleted Documents");
	$sToRender .= "<table>";
    $sToRender .= "<tr><td>The documents you have chosen to expunge are listed below.</td></tr>\n";
    $sToRender .= "<tr><td>Click 'Expunge' to confirm deletion, or 'Cancel' to abort.</td></tr>\n";
    $sToRender .= "<tr/><tr/>";

    // loop through them    
    for ($i = 0; $i < count($aDocuments); $i++) {
        $sToRender .= "\t<tr>\n";
        $sToRender .= "\t\t<td bgcolor=\"" . getColour($i) . "\">\n";
        $sToRender .= "<input type=\"hidden\" name=\"fDocumentIDs[]\" value=\"" . $aDocuments[$i]->getID() . "\"/>\n";        
        $sToRender .= $aDocuments[$i]->getDisplayPath(true) . "\n";
        $sToRender .= "\t\t</td>\n";
        $sToRender .= "\t</tr>\n";
    }
    $sToRender .= "<tr/><tr/>";
    $sToRender .= "<input type=\"hidden\" name=\"fConfirm\" value=\"1\">";
    $sToRender .= "<tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/expunge.gif\" border=\"0\"/></a>\n";
    $sToRender .= generateControllerLink("deletedDocuments", "", "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></td></tr>");
	$sToRender .= "</table>";
    return $sToRender;	
}

/**
 * Displays the status of expunged documents
 */
function renderStatusPage($aSuccessDocuments, $aErrorDocuments) {
	global $default;
	
	$sToRender  = renderHeading("Expunge Deleted Documents Status");
	$sToRender .= "<table>";
	if (count($aSuccessDocuments) > 0) {
	    $sToRender .= "<tr><td>The following documents were successfully expunged:</td></tr>\n";
	    $sToRender .= "<tr/><tr/>";
	    for ($i=0; $i<count($aSuccessDocuments); $i++) {
			$sToRender .= "<tr><td>" . $aSuccessDocuments[$i] . "</td></tr>\n";
	    }
	}
	if (count($aErrorDocuments) > 0) {
    	$sToRender .= "<tr><td>There were errors restoring the following documents:</td></tr>\n";
	    $sToRender .= "<tr/><tr/>";    	
	    for ($i=0; $i<count($aErrorDocuments); $i++) {    	
    		$sToRender .= "<tr><td>" . $aErrorDocuments[$i] . "</td></tr>\n";
	    }
	}
	$sToRender .= "<tr/><tr/>";	
    $sToRender .= "<tr><td>" . generateControllerLink("deletedDocuments", "", "<img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"></td></tr>");
	$sToRender .= "</table>";
    return $sToRender;	
}
?>