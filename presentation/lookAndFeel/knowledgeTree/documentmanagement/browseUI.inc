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

function displayFolderPathLink($aPathArray) {
   // display a separate link to each folder in the path
   for ($i=0; $i<count($aPathArray); $i++) {
       // retrieve the folder id for this folder name
       $iFolderID = Folder::getFolderID($aPathArray[$i]);
       // generate a link back to this page setting fFolderID
       $sLink = generateLink("/presentation/documentmanagement/browseBL.php", 
                            "fBrowseType=folder&fFolderID=$iFolderID", 
                             $aPathArray[$i]);
       $sPathLinks = (strlen($sPathLinks) > 0) ? $sPathLinks . " > " . $sLink : $sLink; 
   }
   return $sPathLinks;
}

function displayFolderLink($oFolder) {
    return generateLink("/presentation/documentmanagement/browseBL.php", 
                        "fBrowseType=folder&fFolderID=" . $oFolder->getID(),
                        // TODO: display folder icon
                        $oFolder->getName());
}

function displayCategoryPathLink($sCategoryName) {
   return "Categories > " . displayCategoryLink($sCategoryName);                                              
}

function displayCategoryLink($sCategoryName) {
    return generateLink("/presentation/documentmanagement/browseBL.php",
                        "fBrowseType=category&fCategoryName=$sCategoryName",
                        $sCategoryName);
}

function displayDocumentTypeLink($aDocumentType) {
    return generateLink("/presentation/documentmanagement/browseBL.php",
                        "fBrowseType=documentType&fDocumentTypeID=" . $aDocumentType["id"],
                        $aDocumentType["name"]);
}

function displayDocumentTypePathLink($aDocumentType) {
    return "Document Types > " . displayDocumentTypeLink($aDocumentType);
}

function displayDocumentLink($oDocument) {//, $bDisplayFullPath) {
    global $default;
    $sIconPath = generateImage($oDocument->getMimeTypeIconUrl());
    return generateLink("/presentation/documentmanagement/documentViewBL.php", 
                        "fDocumentID=" . $oDocument->getID(),
                        // TODO: display file type icon
                        ($sIconPath ? $sIconPath : "") . 
                        //($bDisplayFullPath ? Document::getDocumentPathAsArray($oDocument->getID()) : $oDocument->getName());
                        $oDocument->getName());
}
?>
