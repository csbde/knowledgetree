<?php

require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->uiDirectory/foldermanagement/folderUI.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");
require_once("$default->uiDirectory/foldermanagement/addFolderUI.inc");
/**
 * $Id$
 *
 * Template document browsing page html UI building functions.
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
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
            \tView documents by: <input type=\"radio\" name=\"fBrowseType\" onclick=\"document.MainForm.submit()\" value=\"folder\"" .
                               ((($sBrowseType=="folder") || (strlen($sBrowseType)==0)) ? " checked=\"checked\"" : "") . "/> Folders\n" .
                               "<input type=\"radio\" name=\"fBrowseType\" onclick=\"document.MainForm.submit()\" value=\"category\"" .
                               (($sBrowseType=="category") ? " checked=\"checked\"" : "") . "/> Category\n" .
                               "<input type=\"radio\" name=\"fBrowseType\" onclick=\"document.MainForm.submit()\" value=\"documentType\"" .
                               (($sBrowseType=="documentType") ? " checked=\"checked\"" : "") . "/> Document Type\n</span>";
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

/**
 * Displays the results for category browsing
 *
 * @param array the category browse results
 */
function renderCategoryResults($aResults) {   
    $sToRender = "";
    
    // if the first value in arr["categories"] == Categories then we've got a list of categories
    if ($aResults["categories"][0] == "Categories") {
        // loop through categories and display them
        for ($i=1; $i<count($aResults["categories"]); $i++) {
            $sToRender .= "<tr bgcolor=\"" . getColour($i) . "\">";
                     
            // category name
            $sToRender .= "<td>" . displayCategoryLink($aResults["categories"][$i]) . "</td>";
            // blank filename
            $sToRender .= "<td></td>";
            // creator name
            $sToRender .= "<td></td>";
            // modified date
            $sToRender .= "<td></td>";
            // document type
            $sToRender .= "<td></td>";                      

            $sToRender .= "</tr>\n";
        }
    } else {
        // else the first entry is the category name, so display the documents in the category
        // with full paths
        $sToRender .= renderDocumentList($aResults, _("This category contains no documents"), _("You don't have access to the documents in this category"), true);
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
            $sToRender .= "<tr bgcolor=\"" . getColour($i) . "\">";			
            // document type name
            $sToRender .= "<td>" . displayDocumentTypeLink($aResults["documentTypes"][$i]) . "</td>";
            // blank filename
            $sToRender .= "<td></td>";
            // creator name
            $sToRender .= "<td></td>";
            // modified date
            $sToRender .= "<td></td>";
            // document type
            $sToRender .= "<td></td>";                      
            $sToRender .= "</tr>\n";                   	
        }
    } else {
        // else the first entry is the document type name, so display the documents in the document type
        // with full paths
        $sToRender .= renderDocumentList($aResults, _("This document type contains no documents"), _("You don't have access to the documents in this document type"), true);
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
function renderFolderResults($aResults) {
    global $default;
    $sToRender = "";
	    
    // now loop through the rest of the folders and display links
    if (count($aResults["folders"]) > 1) {
        for ($i=1; $i<count($aResults["folders"]); $i++) {
            $sFolderLink = displayFolderLink($aResults["folders"][$i]);
            $oCreator = User::get($aResults["folders"][$i]->getCreatorID());
            
            $sToRender .= "<tr bgcolor=\"" . getColour($i) . "\">";            
            // folder name
            $sToRender .= "<td>" . $sFolderLink . "</td>";
            // blank filename (??: folder description?)
            $sToRender .= "<td></td>";
            // creator name
            $sToRender .= "<td>" . $oCreator->getName() . "</td>";
            // modified date (TODO: add to db)
            $sToRender .= "<td></td>";
            // document type (??: display one of the mapped document types?)
            $sToRender .= "<td></td>";                        
            $sToRender .= "</tr>\n";
        }
    } else {
        $sToRender .= "<tr><td colspan=\"5\">" . _("This folder contains no sub folders") . "</td></tr>";
    }
        
    $sToRender .= "<tr><td>" . renderDocumentList($aResults, _("This folder contains no documents"), _("You don't have access to the documents in this folder")) . "</td></tr>\n";
    
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
    		$displayText = "<img border=\"0\" src=\"$default->graphicsUrl/" . $sCurrentSortDirection . ".gif\">" . $displayText;
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
function renderDocumentList($aResults, $sNoDocumentsMessage, $sNoPermissionMessage, $bDisplayFullPath = false) {
    // loop through the files and display links
    if (count($aResults["documents"]) > 0) {
        for ($i=0; $i<count($aResults["documents"]); $i++) {
            $sToRender .= "<tr bgcolor=\"" . getColour($i) . "\" width=\"100%\"><td>" . displayDocumentLinkForTemplateBrowsing($aResults["documents"][$i], $bDisplayFullPath) . "</td>";
            //$sToRender .= "<tr>";
            $sToRender .= "<td>" . $aResults["documents"][$i]->getFileName() . "</td>";
            $oCreator = User::get($aResults["documents"][$i]->getCreatorID());
            $sToRender .= "<td>" . $oCreator->getName() . "</td>";
            $sToRender .= "<td>" . $aResults["documents"][$i]->getLastModifiedDate() . "</td>";
            $oDocumentType = DocumentType::get($aResults["documents"][$i]->getDocumentTypeID());
            if ($oDocumentType) {
            	$sToRender .= "<td>" . $oDocumentType->getName() . "</td>";
            }
            $sToRender .= "</tr>\n";
        }
    } else if ($aResults["accessDenied"]) {
        $sToRender .= "<tr><td>$sNoPermissionMessage</td></tr>";
    } else {
        $sToRender .= "<tr><td>$sNoDocumentsMessage</td></tr>";
    }
    return $sToRender;
}

/**
 * Displays the browse page- lists folders and documents
 *
 * @param array the browse results to display
 * @param string the browse view (folder, category, document type)
 * @param string the field to sort the results by
 * @param string the direction to sort
 */
function renderPage($aResults, $sBrowseType, $sSortBy, $sSortDirection) {
    global $default;
    
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");    

    $sToRender = renderHeading(_("Browse Collection"));
    
    $sToRender .= "<table border=\"0\" cellpadding=\"5\" width=\"100%\"><tr><td bgcolor=\"$sTDBGColour\">"; 
    switch ($sBrowseType) {
        case "folder"       : $sToRender .= displayFolderPathLink(Folder::getFolderPathAsArray($aResults["folders"][0]->getID()), Folder::getFolderPathNamesAsArray($aResults["folders"][0]->getID())); break;
        case "category"     : $sToRender .= displayCategoryPathLink($aResults["categories"]); break;
        case "documentType" : $sToRender .= displayDocumentTypePathLink($aResults["documentTypes"]); break;
    }
    $sToRender .= "</td></tr></table>\n";
    
    // browse type select 
   	$sToRender .=  "<table border=\"0\" width=\"100%\">\n";
    //$sToRender .= "\t<tr><td bgcolor=\"$sTDBGColour\">" . renderBrowseTypeSelect($sBrowseType) . "</td></tr>";
    $sToRender .= "\t</table>";
    	
    // display folders|documents
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";
	$sToRender .= renderSortHeadings($sSortBy, $sSortDirection);	
	$sToRender .= "<tr><td>\n";    
    switch ($sBrowseType) {
        case "folder"       : $sToRender .= renderFolderResults($aResults, $sSortBy, $sSortDirection); break;
        case "category"     : $sToRender .= renderCategoryResults($aResults); break;
        case "documentType" : $sToRender .= renderDocumentTypeResults($aResults); break;
    }
    $sToRender .= "</tr></td>\n";
    $sToRender .= "\t</table>";
    
    return $sToRender;
}

?>
