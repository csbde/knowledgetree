<?php

require_once("../../../../config/dmsDefaults.php");
require_once("$default->owl_fs_root/lib/documentmanagement/DocumentBrowser.inc");
require_once("$default->owl_ui_directory/documentmanagement/browseUI.inc");

/**
 * $Id$
 *  
 * This page controls browsing for documents- this can be done either by
 * folder, category or document type.
 * The relevant permission checking is performed, calls to the business logic
 * layer to retrieve the details of the documents to view are made and the user
 * interface is contructed.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */
 
/*
 * Querystring variables
 * ---------------------
 * fBrowseType - determines whether to browse by (folder, category, documentType) [mandatory]
 * fFolderID - the folder to browse [optional depending on fBrowseType]
 * fCategoryName - the category to browse [optional depending on fBrowseType]
 * fDocumentTypeID - the document type id to browse [optional depending on fBrowseType]
 */

// -------------------------------
// page start
// -------------------------------

// only if we have a valid session
if (checkSession()) {
    require_once("../../../webpageTemplate.inc");
    
    // retrieve variables
    if (!$fBrowseType) {
        // required param not set- internal error or user querystring hacking
        // set it to default= folder
        $fBrowseType = "folder";
    }
    
    // fire up the document browser 
    $oDocBrowser = new DocumentBrowser();
    // instantiate my content pattern
    $oContent = new PatternCustom();

    $oContent->addHtml(
                 startTable("0", "100%") .
                 // pending documents
                 startTableRowCell() .
                     startTable("0", "100%") .
                         tableRow("left", "", tableData(browseTypeSelect($fBrowseType))) .
                         tableRow("", "", tabledata("")) .
                         tableRow("", "", tabledata("")));
    // instantiate data arrays
    $folders = NULL;
    $categories = NULL;
    $documentTypes = NULL;
    
    switch ($fBrowseType) {
        case "folder" : // retrieve folderID if present                
                        if (!$fFolderID) {
                            $results = $oDocBrowser->browseByFolder();
                        } else {
                            $results = $oDocBrowser->browseByFolder($fFolderID);
                        }
                        if ($results) {
                            $default->log->debug("browseBL.php: retrieved results from folder browse=" . arrayToString($results));
                            $folderID = $results["folders"][0]->getID();
                        
                            // the first folder in the list is the folder we're in so display the path to this folder
                            // as the heading
                            $default->log->debug("browseBL.php: folder path array for folderID=$folderID=" . arrayToString(Folder::getFolderPathAsArray($folderID)));
                            $oContent->addHtml(tableRow("", "", tableData(displayFolderPathLink(Folder::getFolderPathAsArray($folderID)))));
                            
                            // empty row for spacing
                            $oContent->addHtml(tableRow("", "", tableData("&nbsp;")));
                           
                            // now loop through the rest of the folders and display links
                            for ($i=1; $i<count($results["folders"]); $i++) {
                                $sRow = displayFolderLink($results["folders"][$i]);
                                $oContent->addHtml(tableRow("", "", tableData($sRow)));
                            }
                            
                            // loop through the files and display links
                            for ($i=0; $i<count($results["documents"]); $i++) {
                                $sDocumentLink = displayDocumentLink($results["documents"][$i]);
                                $oContent->addHtml(tableRow("", "", tableData($sDocumentLink))); 
                            }
                        } else {
                            // empty row for spacing
                            $oContent->addHtml(tableRow("", "", tableData("&nbsp;")));
                            $oContent->addHtml(tablerow("", "", tableData($_SESSION["errorMessage"])));
                        }
                                                   
                        break;
                        
        case "category" :
                        if (!$fCategoryName) {
                            $results = $oDocBrowser->browseByCategory();
                            
                            // we have a list of categories
                            // so loop through them and display
                            $oContent->addHtml(tableRow("", "", tableData(displayCategoryLink("Categories"))));
                            
                            // empty row for spacing
                            $oContent->addHtml(tableRow("", "", tableData("&nbsp;")));
                            
                            for ($i=0; $i<count($results["categories"]); $i++) {
                                $oContent->addHtml(tableRow("", "", tableData(displayCategoryLink($results["categories"][$i])))); 
                            }
                            
                        } else {
                            $results = $oDocBrowser->browseByCategory($fCategoryName);
                            // display category heading
                            $oContent->addHtml(tableRow("", "", tableData(displayCategoryPathLink($results["categories"][0]))));
                            
                            // empty row for spacing
                            $oContent->addHtml(tableRow("", "", tableData("&nbsp;")));
                            
                            // now loop through the documents in the category (TODO: if any)
                            // and display them
                            for ($i=0; $i<count($results["documents"]); $i++) {
                                $sDocumentLink = displayDocumentLink($results["documents"][$i], true);
                                $oContent->addHtml(tableRow("", "", tableData($sDocumentLink)));                                    
                            }
                        }
                        
                        break;
                        
        case "documentType" :
                        if (!$fDocumentTypeID) {
                            $results = $oDocBrowser->browseByDocumentType();
                            
                            // we have a list of document types
                            // so loop through them and display
                            $oContent->addHtml(tableRow("", "", tableData(displayDocumentTypeLink(array("name"=>"Document Types")))));
                            
                            // empty row for spacing
                            $oContent->addHtml(tableRow("", "", tableData("&nbsp;")));
                            
                            for ($i=0; $i<count($results["documentTypes"]); $i++) {
                                $oContent->addHtml(tableRow("", "", tableData(displayDocumentTypeLink($results["documentTypes"][$i])))); 
                            }
                            
                        } else {
                            $results = $oDocBrowser->browseByDocumentType($fDocumentTypeID);
                            // display document type heading
                            $oContent->addHtml(tableRow("", "", tableData(displayDocumentTypePathLink($results["documentTypes"][0]))));
                            
                            // empty row for spacing
                            $oContent->addHtml(tableRow("", "", tableData("&nbsp;")));
                            
                            // now loop through the documents in the category (TODO: if any)
                            // and display them
                            for ($i=0; $i<count($results["documents"]); $i++) {
                                $sDocumentLink = displayDocumentLink($results["documents"][$i], true);
                                $oContent->addHtml(tableRow("", "", tableData($sDocumentLink)));                                    
                            }                                
                        }
                        break;
    }

    $oContent->addHtml( 
                     stopTable() . 
                 endTableRowCell() .
             stopTable());
    
    $main->setCentralPayload($oContent);
    
    $main->render();
    
} else {
    // redirect to no permission page
    redirect("$default->owl_ui_url/noAccess.php");
}
?>
