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

function browseTypeSelect($sBrowseType) {
    return "View documents by: <input type=\"radio\" name=\"fBrowseType\" value=\"folder\"" .
                               ((($sBrowseType=="folder") || (strlen($sBrowseType)==0)) ? "checked" : "") . "> Folders" .
                               "<input type=\"radio\" name=\"fBrowseType\" value=\"category\"" .
                               (($sBrowseType=="category") ? "checked" : "") . "> Category" .
                               "<input type=\"radio\" name=\"fBrowseType\" value=\"documentType\"" .
                               (($sBrowseType=="documentType") ? "checked" : "") . "> Document Type";
}

function displayCategoryPathLink($sCategoryName) {
   return "Categories > " . displayCategoryLink($sCategoryName);                                              
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
    return "Document Types > " . displayDocumentTypeLink($aDocumentType);
}

function displayDocumentLink($oDocument, $bDisplayFullPath = false) {
    global $default;
    $sIconPath = generateImage($oDocument->getMimeTypeIconUrl());
    return generateLink("$default->owl_ui_url/documentmanagement/documentViewBL.php", 
                        "fDocumentID=" . $oDocument->getID(),
                        ($sIconPath ? $sIconPath : "") . 
                        ($bDisplayFullPath ? implode(" > ", $oDocument->getDocumentPathAsArray($oDocument->getID())) : $oDocument->getName()));
}
?>
