<?php

require_once("$default->owl_fs_root/presentation/Html.inc");
require_once("$default->owl_ui_directory/foldermanagement/folderUI.inc");
require_once("$default->owl_ui_directory/documentmanagement/documentUI.inc");
require_once("$default->owl_ui_directory/foldermanagement/addFolderUI.inc");

/**
 * $Id$
 *
 * Document browsing page html UI building functions.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */

/**
 * Generates radio button selects for document browse by type
 * Javascript refreshes the form when it changes
 *
 * @param string the selected browse by option
 */
function renderBrowseTypeSelect($sBrowseType) {
    // TODO: write function for onChange that checks if the new value
    return "<span class=\"browseTypeSelect\">\n
            \tView documents by: <input type=\"radio\" name=\"fBrowseType\" onclick=\"document.MainForm.submit()\" value=\"folder\"" .
                               ((($sBrowseType=="folder") || (strlen($sBrowseType)==0)) ? " checked" : "") . "> Folders\n" .
                               "<input type=\"radio\" name=\"fBrowseType\" onclick=\"document.MainForm.submit()\" value=\"category\"" .
                               (($sBrowseType=="category") ? " checked" : "") . "> Category\n" .
                               "<input type=\"radio\" name=\"fBrowseType\" onclick=\"document.MainForm.submit()\" value=\"documentType\"" .
                               (($sBrowseType=="documentType") ? " checked" : "") . "> Document Type\n</span>";
}

/**
 * Displays the passed category path as a link
 * 
 * @param string the category name to display
 */
function displayCategoryPathLink($aCategories) {
    // if the first value in arr["categories"] == Categories then we've got a list of categories
    if ($aCategories[0] == "Categories") {
        return displayCategoryLink($aCategories[0]);
    } else {
        // else the first entry is the category name, so build a little path
        return displayCategoryLink("Categories") . " > " . displayCategoryLink($aCategories[0]);
    }                                              
}

/**
 * Displays the passed category as a link
 * 
 * @param string the category name to display
 */
function displayCategoryLink($sCategoryName) {
    return generateLink($_SERVER["PHP_SELF"],
                        "fBrowseType=category" .
                        // if the category title is passed in, link back to the list of categories
                        (($sCategoryName == "Categories") ? "" : "&fCategoryName=$sCategoryName"),
                        $sCategoryName);
}

function renderCategoryResults($aResults) {   
    $sToRender = "";
    
    // if the first value in arr["categories"] == Categories then we've got a list of categories
    if ($aResults["categories"][0] == "Categories") {
        // loop through categories and display them
        for ($i=1; $i<count($aResults["categories"]); $i++) {
            $sToRender .= "<tr><td>" . displayCategoryLink($aResults["categories"][$i]) . "</td></tr>";
        }
    } else {
        // else the first entry is the category name, so display the documents in the category
        // with full paths
        $sToRender .= renderDocumentList($aResults["documents"], true);
    }
    return $sToRender;
}

/**
 * Displays the passed document type path as a link
 * 
 * @param string the document type to display 
 */
function displayDocumentTypePathLink($aDocumentTypes) {
    // if the first value in arr["categories"] == Categories then we've got a list of categories
    if ($aDocumentTypes[0]["name"] == "Document Types") {
        return displayDocumentTypeLink($aDocumentTypes[0]);
    } else {
        // else the first entry is the category name, so build a little path
        return displayDocumentTypeLink(array("name"=>"Document Types")) . " > " . displayDocumentTypeLink($aDocumentTypes[0]);
    }
}

/**
 * Displays the passed document type as a link
 * 
 * @param string the document type to display 
 */
function displayDocumentTypeLink($aDocumentType) {
    return generateLink($_SERVER["PHP_SELF"],
                        "fBrowseType=documentType" .
                        // if the document type title is passed in, link back to the list of document types
                        (($aDocumentType["name"] == "Document Types") ? "" : "&fDocumentTypeID=" . $aDocumentType["id"]),
                        $aDocumentType["name"]);
}


function renderDocumentTypeResults($aResults) {
    $sToRender = "";
    
    // if the first value in arr["documentTypes"] == Document Types then we've got a list of document types
    if ($aResults["documentTypes"][0]["name"] == "Document Types") {
        // loop through document types and display them
        for ($i=1; $i<count($aResults["documentTypes"]); $i++) {
            $sToRender .= "<tr><td>" . displayDocumentTypeLink($aResults["documentTypes"][$i]) . "</td></tr>";
        }
    } else {
        // else the first entry is the document type name, so display the documents in the document type
        // with full paths
        $sToRender .= renderDocumentList($aResults["documents"], true);
    }
    return $sToRender;
}


function renderFolderResults($aResults) {
    global $default;
    $sToRender = "";
    // now loop through the rest of the folders and display links
    for ($i=1; $i<count($aResults["folders"]); $i++) {
        $sRow = displayFolderLink($aResults["folders"][$i]);
        $sToRender .= "<tr><td>" . $sRow . "</td></tr>";
    }
    
    $sToRender .= "<tr><td>&nbsp;</td></tr>";
    $sToRender .= "<tr><td>" . renderDocumentList($aResults["documents"]) . "</td></tr>";
    
    return $sToRender;
}

function renderDocumentList($aDocuments, $bDisplayFullPath = false) {
    // loop through the files and display links
    for ($i=0; $i<count($aDocuments); $i++) {
        $sToRender .= "<tr><td>" . displayDocumentLink($aDocuments[$i], $bDisplayFullPath) . "</td></tr>";
    }
    return $sToRender;
}

function renderPage($aResults, $sBrowseType) {
    // browse type select
   	$sToRender =  "<table border=\"0\" width=\"100%\">\n";
    $sToRender .= "\t<tr><td>" . renderBrowseTypeSelect($sBrowseType) . "</tr></td>";
    $sToRender .= "\t</table>";
    
    // current path
    $sToRender .=  "<table border=\"1\" width=\"100%\">\n";
	$sToRender .= "\t<tr><td>";    
    switch ($sBrowseType) {
        case "folder"       : $sToRender .= displayFolderPathLink(Folder::getFolderPathAsArray($aResults["folders"][0]->getID())); break;
        case "category"     : $sToRender .= displayCategoryPathLink($aResults["categories"]); break;
        case "documentType" : $sToRender .= displayDocumentTypePathLink($aResults["documentTypes"]); break;
    }
    $sToRender .= "</tr></td>\n";
    $sToRender .= "\t</table>";
    
    // display folders|documents
	$sToRender .= "<table border=\"0\">\n";
	$sToRender .= "<tr><td>\n";    
    switch ($sBrowseType) {
        case "folder"       : $sToRender .= renderFolderResults($aResults); break;
        case "category"     : $sToRender .= renderCategoryResults($aResults); break;
        case "documentType" : $sToRender .= renderDocumentTypeResults($aResults); break;
    }
    $sToRender .= "</tr></td>\n";
    $sToRender .= "\t</table>";
    
    return $sToRender;
}

?>
