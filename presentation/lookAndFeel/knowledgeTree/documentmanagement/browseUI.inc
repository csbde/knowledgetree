<?php

/// XXX: extractGPC in UI?(!)
KTUtil::extractGPC('fBrowseType', 'fCategoryName', 'fDocumentTypeID', 'fFolderID');

require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->uiDirectory/foldermanagement/folderUI.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");
require_once("$default->uiDirectory/foldermanagement/addFolderUI.inc");

/**
 * $Id$
 *
 * Document browsing page html UI building functions.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
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
        \t" . _("View documents by") . ": <input type=\"radio\" name=\"fBrowseType\" onclick=\"document.MainForm.submit()\" value=\"folder\"" .
        ((($sBrowseType=="folder") || (strlen($sBrowseType)==0)) ? " checked=\"checked\"" : "") . "/> " . _("Folders") ."\n" .
        "<input type=\"radio\" name=\"fBrowseType\" onclick=\"document.MainForm.submit()\" value=\"category\"" .
        (($sBrowseType=="category") ? " checked=\"checked\"" : "") . "/> " . _("Category") . "\n" .
        "<input type=\"radio\" name=\"fBrowseType\" onclick=\"document.MainForm.submit()\" value=\"documentType\"" .
        (($sBrowseType=="documentType") ? " checked=\"checked\"" : "") . "/> " . _("Document Type") . "\n</span>";
}

// <category browse helper methods>

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
        return displayCategoryLink("Categories") . ($aCategories[0] ? " > " . displayCategoryLink($aCategories[0]) : "");
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

/**
 * Displays the results for category browsing
 *
 * @param array the category browse results
 */
function renderCategoryResults($aResults) {    
    $sToRender = "";

    //var_dump($aResults["categories"][0]);
    // if the first value in arr["categories"] == Categories then we've got a list of categories
    if ($aResults["categories"][0] == "Categories") {
        // loop through categories and display them
        for ($i=1; $i<count($aResults["categories"]); $i++) {
            $sToRender .= "<tr bgcolor=\"" . getColour($i-1) . "\">";

            // category name
            $sToRender .= "<td>" . displayCategoryLink($aResults["categories"][$i]) . "</td>";
            // blank filename
            $sToRender .= "<td>&nbsp;</td>";
            // creator name
            $sToRender .= "<td>&nbsp;</td>";
            // modified date
            $sToRender .= "<td>&nbsp;</td>";
            // document type
            $sToRender .= "<td>&nbsp;</td>";

            $sToRender .= "</tr>\n";
        }
    } else {
        if (count($aResults["categories"]) > 0) {
            // else the first entry is the category name, so display the documents in the category
            $sToRender .= renderDocumentList($aResults, _("This category contains no documents"), _("You don't have access to the documents in this category"));
        } else {
            $sToRender .= "<tr><td colspan=\"5\"><font color=\"red\">" . _("There is no Category Document Field- contact a System Administrator.") . "</font></td></tr>";
        }
    }
    return $sToRender;
}

// </category browse helper methods>

// <document type display helper methods>
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

/**
 * Displays the results for document type browsing
 *
 * @param array the document type browse results
 */
function renderDocumentTypeResults($aResults) {
    $sToRender = "";

    // if the first value in arr["documentTypes"] == Document Types then we've got a list of document types
    if ($aResults["documentTypes"][0]["name"] == "Document Types") {
        // loop through document types and display them
        for ($i=1; $i<count($aResults["documentTypes"]); $i++) {
            $sToRender .= "<tr bgcolor=\"" . getColour($i-1) . "\">";            
            // document type name
            $sToRender .= "<td>" . displayDocumentTypeLink($aResults["documentTypes"][$i]) . "</td>";
            // blank filename
            $sToRender .= "<td>&nbsp;</td>";
            // creator name
            $sToRender .= "<td>&nbsp;</td>";
            // modified date
            $sToRender .= "<td>&nbsp;</td>";
            // document type
            $sToRender .= "<td>&nbsp;</td>";
            $sToRender .= "</tr>\n";                       
        }
    } else {
        // else the first entry is the document type name, so display the documents in the document type
        $sToRender .= renderDocumentList($aResults, _("This document type contains no documents"), _("You don't have access to the documents in this document type"));
    }
    return $sToRender;
}
// </document type browse helper methods>


// <folder browse helper methods>
/**
 * Displays the folders in the browse results
 *
 * @param array the browse result objects
 */
function renderFolderResults($aResults, $bTemplateBrowsing = false) {
    global $default;
    $sToRender = "";

    // now loop through the rest of the folders and display links
    if (count($aResults["folders"]) > 1) {
        for ($i=1; $i<count($aResults["folders"]); $i++) {
            $sFolderLink = displayFolderLink($aResults["folders"][$i]);
            $oCreator = User::get($aResults["folders"][$i]->getCreatorID());

            // the first element of the array contains the current folder name
            $sToRender .= "<tr bgcolor=\"" . getColour($i-1) . "\">";
            // folder name
            $sToRender .= "<td valign=\"bottom\">" . $sFolderLink . "</td>";
            // blank filename (folder description?)
            $sToRender .= "<td valign=\"bottom\">" . $aResults["folders"][$i]->getDescription() . "</td>";
            // creator name
            $sToRender .= "<td valign=\"bottom\">" . ($oCreator ? $oCreator->getName() : "&nbsp;") . "</td>";
            // modified date (TODO: add to db)
            $sToRender .= "<td valign=\"bottom\">&nbsp;</td>";
            // document type (??: display one of the mapped document types? which one?)
            $sToRender .= "<td valign=\"bottom\">&nbsp;</td>";
            $sToRender .= "</tr>\n";
        }
    } else {
        $sToRender .= "<tr><td colspan=\"5\">" . _("This folder contains no sub folders") . "</td></tr>";
    }

    $sToRender .= "<tr><td>" . renderDocumentList($aResults, _("This folder contains no documents"),
        _("You don't have access to the documents in this folder"),
        false, $bTemplateBrowsing) .
        "</td></tr>\n";

    return $sToRender;
}
// </folder browse helper methods>

/**
 * Displays the headings for the displayed document details and enables
 * resorting the contents
 *
 * @param string the field currently sorting by
 * @param string the direction currently sorted in
 */
function renderSortHeadings($sSortBy, $sSortDirection) {
     global $default, $oBrowser, $fBrowseType, $fFolderID, $fCategoryName, $fDocumentTypeID;
     
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");

    // need list of display criteria and sort name
    $aSortCriteria = $oBrowser->getSortCriteria();                                                                     
    
    $sToRender .= "<tr bgcolor=\"$sTDBGColour\">";
    while (list($key, $value) = each ($aSortCriteria)) {        
        $sCurrentSortDirection = "asc";
        $displayText = $value["display"];
        // if the current heading is being sorted then flip the sort direction
        if ($sSortBy == $key) {
            $sCurrentSortDirection = ($sSortDirection == "asc" ? "desc" : "asc");
            $displayText = "<img border=\"0\" src=\"" . imgSrc($sCurrentSortDirection . ".gif") . "\">" . $displayText;
        }
        switch ($fBrowseType) {
            case "folder" :         
                $queryString = "fFolderID=$fFolderID";
                break;
            case "category" :
                $queryString = "fCategoryName=$fCategoryName";
                break;        
            case "documentType" :
                $queryString = "fDocumentTypeID=$fDocumentTypeID";            
                break;
        }        
        $sToRender .= "<td>" . generateLink($_SERVER["PHP_SELF"], "fBrowseType=$fBrowseType&$queryString&fSortBy=$key&fSortDirection=$sCurrentSortDirection", $displayText) . "</td>";                                    
    }

    $sToRender .= "</tr>\n";
    return $sToRender;
}

/**
 * Displays the documents in the browse results
 *
 * @param array the browse result objects
 * @param string the message to display if there are no documents
 * @param string the message to display if the current user doesn't have permission to view the documents
 * @param boolean whether to display the complete path to the document or not
 */
function renderDocumentList($aResults, $sNoDocumentsMessage, $sNoPermissionMessage, $bDisplayFullPath = false, $bTemplateBrowsing = false) {
    global $default;

    $iFolderCount = count($aResults["folders"]) - 1;
    // loop through the files and display links
    if (count($aResults["documents"]) > 0) {
        for ($i=0; $i<count($aResults["documents"]); $i++) {
            // in order for candy striping to work we need to take the number of folders
            // into account when alternating
            $sToRender .= "<tr bgcolor=\"" . getColour($i+$iFolderCount) . "\" width=\"100%\"><td valign=\"bottom\">" .
                "<input type=\"checkbox\" name=\"fDocumentIDs[]\" value=\"" . $aResults["documents"][$i]->getID() . "\"/>" .
                          ($bTemplateBrowsing ? displayDocumentLinkForTemplateBrowsing($aResults["documents"][$i], $bDisplayFullPath) :
                                                  displayDocumentLink($aResults["documents"][$i], $bDisplayFullPath)) . "</td>";

            // #3425 the title is now the filename, and the description is the title
            $sToRender .= "<td valign=\"bottom\">" . $aResults["documents"][$i]->getName() . "</td>";
            $oCreator = User::get($aResults["documents"][$i]->getCreatorID());
            $sToRender .= "<td valign=\"bottom\">" . ($oCreator ? $oCreator->getName() : "") . "</td>";
            $aDocumentTransaction = DocumentTransaction::getList("transaction_id=1 AND document_id=" . $aResults["documents"][$i]->getID());
            $sToRender .= "<td valign=\"bottom\">" . $aDocumentTransaction[0]->dDateTime . "</td>";
            $oDocumentType = DocumentType::get($aResults["documents"][$i]->getDocumentTypeID());
            if ($oDocumentType) {
                $sToRender .= "<td valign=\"bottom\">" . $oDocumentType->getName() . "</td>";
            }
            $sToRender .= "</tr>\n";
        }

        $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
        $sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");

        $sToRender .= "<tr bgcolor=\"". $sTDBGColour . "\"><td colspan=\"5\" valign=\"bottom\">" .
                      "<input type=\"checkbox\" name=\"selectall\" onClick=\"selectAll()\"/> " . _("Select all documents") . "</td></tr>";

        $sToRender .= "<tr><td colspan=\"5\" >";
        $sToRender .= "<input type=\"hidden\" name=\"fActions\">";

        $sToRender .= "<input type=\"image\" onclick=\"document.MainForm.fActions.value='move'; document.MainForm.submit()\" src=\"" . KTHtml::getMoveButton() . "\" border=\"0\"/>";
        $sToRender .= "<input type=\"image\" onclick=\"document.MainForm.fActions.value='delete'; document.MainForm.submit()\" src=\"" . KTHtml::getDeleteButton() . "\" border=\"0\"/>";
        $sToRender .= "</td></tr>\n";
        // Change for group Operations


    } else if ($aResults["accessDenied"]) {
        $sToRender .= "<tr><td>$sNoPermissionMessage</td></tr>";
    } else {
        $sToRender .= "<tr><td>$sNoDocumentsMessage</td></tr>";
    }
    return $sToRender;
}

/**
 * #3426
 * Appends folder and document counts to the last folder path
 */
function appendCounts($aFolderPath, $iFolderCount, $iDocumentCount) {
    // append to the last path component and return
    $aFolderPath[count($aFolderPath)-1] = $aFolderPath[count($aFolderPath)-1] .
        " ($iFolderCount folder" . (($iFolderCount > 1) || ($iFolderCount == 0) ? "s" : "") .
        ", $iDocumentCount document" . (($iDocumentCount > 1) || ($iDocumentCount == 0) ? "s" : "") . ")";
    return $aFolderPath;
}

/**
 * Displays the browse page- lists folders and documents
 *
 * @param array the browse results to display
 * @param string the browse view (folder, category, document type)
 * @param string the field to sort the results by
 * @param string the direction to sort
 */

function renderPage($aResults, $sBrowseType, $sSortBy, $sSortDirection, $bTemplateBrowsing = false) {
    global $default;

    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");

    $sToRender = renderHeading(_("Browse collection"));

    // Script function to select all documents
    $sToRender = "\n\n<SCRIPT LANGUAGE=\"javascript\">\n ";
    $sToRender .= "<!--\n";
    $sToRender .= "function selectAll() {\n";
    $sToRender .= "\tf = document.MainForm;\n";
    $sToRender .= "\tc = f.selectall.checked;\n";
    $sToRender .= "\tif (f.elements['fDocumentIDs[]']) {\n";
    $sToRender .= "\t\tif (f.elements['fDocumentIDs[]'].length > 1) {\n";
    $sToRender .= "\t\t\tfor (i = 0; i < f.elements['fDocumentIDs[]'].length; i++) f.elements['fDocumentIDs[]'][i].checked = c;\n";
    $sToRender .= "\t\t} else {\n";
    $sToRender .= "\t\t\tf.elements['fDocumentIDs[]'].checked = c;\n";
    $sToRender .= "\t}}}\n";
    $sToRender .= "//-->\n";
    $sToRender .= "</SCRIPT>\n\n";

    $sToRender .= "<table border=\"0\" cellpadding=\"5\" width=\"100%\"><tr><td bgcolor=\"$sTDBGColour\">";
    switch ($sBrowseType) {
        case "folder":
            $sToRender .= displayFolderPathLink(Folder::getFolderPathAsArray($aResults["folders"][0]->getID()),
                appendCounts(Folder::getFolderPathNamesAsArray($aResults["folders"][0]->getID()),
                count($aResults["folders"])-1, count($aResults["documents"])));
                break;
        case "category":
            $sToRender .= displayCategoryPathLink($aResults["categories"]);
            break;
        case "documentType":
            $sToRender .= displayDocumentTypePathLink($aResults["documentTypes"]);
            break;
    }
    $sToRender .= "</td></tr></table>\n";

    // browse type select
    $sToRender .=  "<table border=\"0\" width=\"100%\">\n";
    $sToRender .= "\t<tr><td bgcolor=\"$sTDBGColour\">" . renderBrowseTypeSelect($sBrowseType) . "</td></tr>";
    $sToRender .= "\t</table>";
        
    // display folders|documents
    $sToRender .= "<table border=\"0\" width=\"100%\">\n";
    $sToRender .= renderSortHeadings($sSortBy, $sSortDirection);    
    $sToRender .= "<tr><td>\n";
    switch ($sBrowseType) {
        case "folder":
            $sToRender .= renderFolderResults($aResults, $bTemplateBrowsing);
            break;
        case "category":
            $sToRender .= renderCategoryResults($aResults);
            break;
        case "documentType":
            $sToRender .= renderDocumentTypeResults($aResults);
            break;
    }
    $sToRender .= "</tr></td>\n";
    $sToRender .= "\t</table>";

    return $sToRender;
}

?>
