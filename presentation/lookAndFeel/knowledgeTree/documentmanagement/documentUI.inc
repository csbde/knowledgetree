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
?>
