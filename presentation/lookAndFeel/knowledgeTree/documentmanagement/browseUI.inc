<?php

require_once("$default->owl_fs_root/presentation/Html.inc");
require_once("$default->owl_ui_directory/foldermanagement/folderUI.inc");
require_once("$default->owl_ui_directory/documentmanagement/documentUI.inc");

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
function browseTypeSelect($sBrowseType) {
    return "<span class=\"browseTypeSelect\">\n
            \tView documents by: <input type=\"radio\" name=\"fBrowseType\" onChange=\"document.forms[0].submit()\" value=\"folder\"" .
                               ((($sBrowseType=="folder") || (strlen($sBrowseType)==0)) ? "checked" : "") . "> Folders\n" .
                               "<input type=\"radio\" name=\"fBrowseType\" onChange=\"document.forms[0].submit()\" value=\"category\"" .
                               (($sBrowseType=="category") ? "checked" : "") . "> Category\n" .
                               "<input type=\"radio\" name=\"fBrowseType\" onChange=\"document.forms[0].submit()\" value=\"documentType\"" .
                               (($sBrowseType=="documentType") ? "checked" : "") . "> Document Type\n</span>";
}

/**
 * Displays the passed category path as a link
 * 
 * @param string the category name to display
 */
function displayCategoryPathLink($sCategoryName) {
   return displayCategoryLink("Categories") . " > " . displayCategoryLink($sCategoryName);                                              
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

/**
 * Displays the passed document type path as a link
 * 
 * @param string the document type to display 
 */
function displayDocumentTypePathLink($aDocumentType) {
    return displayDocumentTypeLink(array("name"=>"Document Types")) . " > " . displayDocumentTypeLink($aDocumentType);    
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

?>
