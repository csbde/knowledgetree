<?php

/**
 * $Id$
 *  
 * This page controls browsing for documents- this can be done either by
 * folder, category or document type.
 * The relevant permission checking is performed, calls to the business logic
 * layer to retrieve the details of the documents to view are made and the user
 * interface is contructed.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation
 *
 * Querystring variables
 * ---------------------
 * fBrowseType - determines whether to browse by (folder, category, documentType) [mandatory]
 * fFolderID - the folder to browse [optional depending on fBrowseType]
 * fCategoryName - the category to browse [optional depending on fBrowseType]
 * fDocumentTypeID - the document type id to browse [optional depending on fBrowseType]
 */
 
// main library routines and defaults
require_once("./config/dmsDefaults.php");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternImage.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableLinks.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");

// -------------------------------
// page start
// -------------------------------

// only if we have a valid session
if (checkSession()) {

    // check if this page is authorised, ie. has come from control.php
    if ($_SESSION["authorised"]) {
        // retrieve variables
        if (!$fBrowseType) {
            // required param not set- internal error or user querystring hacking
            // TODO: something intelligent
            $_SESSION["errorMessage"] = "Required parameter missing, cannot proceed";
        } else {
            // fire up the document browser 
            $oDocBrowser = new DocumentBrowser();
            
            // TODO: instantiate the visual components
            
            // instantiate data arrays
            $folders = NULL;
            $categories = NULL;
            $documentTypes = NULL;
            
            switch ($fBrowseType) {
                case "folder" : // retrieve folderID if present                
                                if (!$fFolderID) {
                                    $folders = $oDocBrowser->browseByFolder();
                                } else {
                                    $folders = $oDocBrowser->browseByFolder($fFolderID);
                                }
                                break;
                case "category" :
                                if (!$fCategoryName) {
                                    $categories = $oDocBrowser->browseByCategory();
                                } else {
                                    $documents = $oDocBrowser->browseByCategory($fCategoryName);
                                }
                                break;                
                case "documentType" :
                                if (!$fDocumentTypeID) {
                                    $documentTypes = $oDocBrowser->browseByDocumentType();
                                } else {
                                    $documents = $oDocBrowser->browseByDocumentType($fDocumentTypeID);
                                }
                                break;                
            }
        }
            
        /*

      - loop through things, displaying appropriately
        - documentmanagement/browseUI.inc
          - displayDocumentLink
          - displayFolderLink
          - display
          - displayFileActions($permissionArray)

      - docManagement
        - getFileFolderPerms
          - folder modification links (if perms)

      - link to files / folders
        - displayLinkWithPath; displayLink
      - expand table?
      */
        
    } else {      
        // FIXME: redirect to no permission page
        print "you do not have access to view this page!  please go away, and come back when you do.<br>";
        echo generateLink("logout") . "logout</a>";
        // controllerRedirect("permissionDenied", "accessDeniedMsg=$lang_noPermission");
    }
}
?>
