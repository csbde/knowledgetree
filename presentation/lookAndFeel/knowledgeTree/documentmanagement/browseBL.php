<?php

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentBrowser.inc");
require_once("$default->uiDirectory/documentmanagement/browseUI.inc");

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
    ob_start();
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
    
    switch ($fBrowseType) {
        case "folder" : // retrieve folderID if present
                        if (!$fFolderID) {
                            $aResults = $oDocBrowser->browseByFolder();
                            controllerRedirect("browse", "fFolderID=" . $aResults["folders"][0]->getID());
                        } else {
                            ob_end_flush();
                            $aResults = $oDocBrowser->browseByFolder($fFolderID);
                        }
                        break;
                        
        case "category" :
                        if (!$fCategoryName) {
                            $aResults = $oDocBrowser->browseByCategory();
                        } else {
                            $aResults = $oDocBrowser->browseByCategory($fCategoryName);
                        }
                        break;
                        
        case "documentType" :
                        if (!$fDocumentTypeID) {
                            $aResults = $oDocBrowser->browseByDocumentType();
                        } else {
                            $aResults = $oDocBrowser->browseByDocumentType($fDocumentTypeID);
                        }
                        break;
    }
    
    if ($aResults) {
        // display the list of categories
        $oContent->addHtml(renderPage($aResults, $fBrowseType));
        
    } else {
        $main->setErrorMessage("There are no document types to display");
    }
    
    $main->setCentralPayload($oContent);
    $main->setFormAction($_SERVER["PHP_SELF"]);
    $main->render();
    
} else {
    // redirect to no permission page
    redirect("$default->uiUrl/noAccess.php");
}
?>
