<?php

/**
 * $Id$
 *  
 * This page holds all presentation code for displaying deleted documents management pages. 
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.administration.documentmanagement
 */

/**
 * Lists all deleted documents
 */
function renderListDeletedDocumentsPage($aDocuments) {
	global $default;

	$sToRender  = renderHeading("Expunge Deleted Documents");
	$sToRender .= "<table>";
	if (count($aDocuments) > 0) {
	    $sToRender .= "<tr><td>The following documents have been deleted from the DMS.</td></tr>\n";
	    $sToRender .= "<tr><td>Select the documents you would like to completely remove and click 'Expunge', or click on the document name link to restore the document.</td></tr>\n";
	    $sToRender .= "<tr/><tr/>";
	    // loop through them    
	    for ($i = 0; $i < count($aDocuments); $i++) {
	        $sToRender .= "\t<tr>\n";
	        $sToRender .= "\t\t<td bgcolor=\"" . getColour($i) . "\">\n";
	        $sToRender .= "<input type=\"checkbox\" name=\"fDocumentIDs[]\" value=\"" . $aDocuments[$i]->getID() . "\"/>\n";
	        // get an existing folder to restore the document to
	        $oFolder = Folder::get($aDocuments[$i]->getFolderID());
	        // if the folder no longer exists, use the root folder to start browsing from
	        $iFolderID = ($oFolder ? $aDocuments[$i]->getFolderID() : User::getUserRootFolderID());
	        $sToRender .= generateControllerLink("restoreDeletedDocument", 
	        									 "fDocumentID=" . $aDocuments[$i]->getID() . "&fFolderID=$iFolderID", 
	        									 ($aDocuments[$i]->getFolderID() == $iFolderID) ? $aDocuments[$i]->getDisplayPath(true) : $aDocuments[$i]->getIcon() . $aDocuments[$i]->getName()) . "\n";
	        $sToRender .= "\t\t</td>\n";
	        $sToRender .= "\t</tr>\n";
	    }
	    $sToRender .= "<tr/><tr/>";
	    $sToRender .= "<tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/expunge.gif\" border=\"0\"/></td></tr>\n";	    
	} else {
		$sToRender .= "<tr><td>There are currently no deleted documents in the system</td></tr>";
	    $sToRender .= "<tr/><tr/>";		
		$sToRender .= "<tr><td><a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"/></a></td></tr>\n";		
		
	}
	$sToRender .= "</table>";
    return $sToRender;	
}
?>