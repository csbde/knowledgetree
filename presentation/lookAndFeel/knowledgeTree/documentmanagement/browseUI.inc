<?php

require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->uiDirectory/foldermanagement/folderUI.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");
require_once("$default->uiDirectory/foldermanagement/addFolderUI.inc");

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

 
function renderHeading($sHeading) {
    global $default;
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"600\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">$sHeading</font></th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}
 
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
                               ((($sBrowseType=="folder") || (strlen($sBrowseType)==0)) ? " checked=\"checked\"" : "") . "/> Folders\n" .
                               "<input type=\"radio\" name=\"fBrowseType\" onclick=\"document.MainForm.submit()\" value=\"category\"" .
                               (($sBrowseType=="category") ? " checked=\"checked\"" : "") . "/> Category\n" .
                               "<input type=\"radio\" name=\"fBrowseType\" onclick=\"document.MainForm.submit()\" value=\"documentType\"" .
                               (($sBrowseType=="documentType") ? " checked=\"checked\"" : "") . "/> Document Type\n</span>";
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
    if ($sCategoryName != "") {
        return generateLink($_SERVER["PHP_SELF"],
                            "fBrowseType=category" .
                            // if the category title is passed in, link back to the list of categories
                            (($sCategoryName == "Categories") ? "" : "&fCategoryName=" . urlencode($sCategoryName)),
                            $sCategoryName);
    } else {
        return false;
    }
}

function renderCategoryResults($aResults) {   
    $sToRender = "";
    
    // if the first value in arr["categories"] == Categories then we've got a list of categories
    if ($aResults["categories"][0] == "Categories") {
        // loop through categories and display them
        for ($i=1; $i<count($aResults["categories"]); $i++) {
            $sToRender .= "<tr><td bgcolor=\"" . getColour($i) . "\">" . displayCategoryLink($aResults["categories"][$i]) . "</td></tr>\n";
        }
    } else {
        // else the first entry is the category name, so display the documents in the category
        // with full paths
        $sToRender .= renderDocumentList($aResults["documents"], "This category contains no documents", true);
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
            $sToRender .= "<tr><td bgcolor=\"" . getColour($i) . "\">" . displayDocumentTypeLink($aResults["documentTypes"][$i]) . "</td></tr>";
        }
    } else {
        // else the first entry is the document type name, so display the documents in the document type
        // with full paths
        $sToRender .= renderDocumentList($aResults["documents"], "This document type contains no documents", true);
    }
    return $sToRender;
}


function renderFolderResults($aResults) {
    global $default;
    $sToRender = "";
    // now loop through the rest of the folders and display links
    if (count($aResults["folders"]) > 1) {
        for ($i=1; $i<count($aResults["folders"]); $i++) {
            $sRow = displayFolderLink($aResults["folders"][$i]);
            $sToRender .= "<tr><td nowrap width=\"100%\" bgcolor=\"" . getColour($i) . "\">" . $sRow . "</td></tr>\n";
        }
    } else {
        $sToRender .= "<tr><td>This folder contains no sub folders</td></tr>";
    }
    
    $sToRender .= "<tr><td>&nbsp;</td></tr>";
    $sToRender .= "<tr><td>" . renderDocumentList($aResults, "This folder contains no documents") . "</td></tr>\n";
    
    return $sToRender;
}

function renderDocumentList($aResults, $sNoDocumentsMessage, $bDisplayFullPath = false) {
    // loop through the files and display links
    if (count($aResults["documents"]) > 0) {
        for ($i=0; $i<count($aResults["documents"]); $i++) {
            $sToRender .= "<tr><td bgcolor=\"" . getColour($i) . "\">" . displayDocumentLink($aResults["documents"][$i], $bDisplayFullPath) . "</td></tr>";
        }
    } else if ($aResults["accessDenied"]) {
        $sToRender .= "<tr><td>You don't have access to the documents in this folder</td></tr>";
    } else {
        $sToRender .= "<tr><td>$sNoDocumentsMessage</td></tr>";
    }
    return $sToRender;
}

function renderPage($aResults, $sBrowseType) {
    global $default;
    
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
    

    $sToRender = renderHeading("Browse Collection");
    
    $sToRender .= "<table border=\"0\" cellpadding=\"5\" width=\"100%\"><tr><td bgcolor=\"$sTDBGColour\">"; 
    switch ($sBrowseType) {
        case "folder"       : $sToRender .= displayFolderPathLink(Folder::getFolderPathAsArray($aResults["folders"][0]->getID()), Folder::getFolderPathNamesAsArray($aResults["folders"][0]->getID())); break;
        case "category"     : $sToRender .= displayCategoryPathLink($aResults["categories"]); break;
        case "documentType" : $sToRender .= displayDocumentTypePathLink($aResults["documentTypes"]); break;
    }
    $sToRender .= "</td></tr></table>\n";
    
    // browse type select 
   	$sToRender .=  "<table border=\"0\" width=\"600\">\n";
    $sToRender .= "\t<tr><td bgcolor=\"$sTDBGColour\">" . renderBrowseTypeSelect($sBrowseType) . "</td></tr>";
    $sToRender .= "\t</table>";
        
    // display folders|documents
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";
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

function getColour($iColourCount) {
		return ($iColourCount%2 == 0) ? "F5F6EE" : "FFFFFF";
}

?>
