<?php

require_once("$default->owl_fs_root/presentation/Html.inc");

/**
 * $Id$
 *
 * Document browsing page html UI building functions.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.documentmanagement
 */

/**
 * Generates radio button selects for document browse by type
 * Javascript refreshes the form when it changes
 *
 * @param string the selected browse by option
 */
function browseTypeSelect($sBrowseType) {
    return "<span class=\"browseTypeSelect\">\n
            \tView documents by: <input type=\"radio\" name=\"fBrowseType\" onChange=\"document.forms[0].submit()\" value=\"folder\"" .
                               ((($sBrowseType=="folder") || (strlen($sBrowseType)==0)) ? "checked" : "") . "> Folders\n" .
                               "<input type=\"radio\" name=\"fBrowseType\" onChange=\"document.forms[0].submit()\" value=\"category\"" .
                               (($sBrowseType=="category") ? "checked" : "") . "> Category\n" .
                               "<input type=\"radio\" name=\"fBrowseType\" onChange=\"document.forms[0].submit()\" value=\"documentType\"" .
                               (($sBrowseType=="documentType") ? "checked" : "") . "> Document Type\n</span>";
}

function displayCategoryPathLink($sCategoryName) {
   return "<span class=\"browseTypeSelect\">Categories</span> > " . displayCategoryLink($sCategoryName);                                              
}

function displayCategoryLink($sCategoryName) {
    return generateLink($_SERVER["PHP_SELF"],
                        "fBrowseType=category&fCategoryName=$sCategoryName",
                        $sCategoryName);
}

function displayDocumentTypeLink($aDocumentType) {
    return generateLink($_SERVER["PHP_SELF"],
                        "fBrowseType=documentType&fDocumentTypeID=" . $aDocumentType["id"],
                        $aDocumentType["name"]);
}

function displayDocumentTypePathLink($aDocumentType) {
    return "<span class=\"browseTypeSelect\">Document Types</span> > " . displayDocumentTypeLink($aDocumentType);
}

function displayDocumentLink($oDocument, $bDisplayFullPath = false) {
    global $default;
    $sIconPath = generateImage($oDocument->getMimeTypeIconUrl());
    return generateLink("$default->owl_root_url/control.php?action=viewDocument", 
                        "fDocumentID=" . $oDocument->getID(),
                        ($sIconPath ? $sIconPath : "") . 
                        ($bDisplayFullPath ? implode(" > ", $oDocument->getDocumentPathAsArray($oDocument->getID())) : $oDocument->getName()));
}
?>
