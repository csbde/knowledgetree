<?php

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/browse/BrowserFactory.inc");
require_once("$default->fileSystemRoot/lib/browse/Browser.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
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
 * fSortBy - the document attribute to sort the browse results by
 * fSortDirection - the direction to sort
 */

// -------------------------------
// page start
// -------------------------------

// only if we have a valid session
if (checkSession()) {
    // retrieve variables
    if (!$fBrowseType) {
        // required param not set- internal error or user querystring hacking
        // set it to default= folder
        $fBrowseType = "folder";
    }
    
    // retrieve field to sort by
    if (!$fSortBy) {
    	// no sort field specified- default is document name
    	$fSortBy = "name";
    }
    // retrieve sort direction
    if (!$fSortDirection) {
    	$fSortDirection = "asc";
    }
       
    // fire up the document browser 
    $oBrowser = BrowserFactory::create($fBrowseType, $fSortBy, $fSortDirection);
    $sectionName = $oBrowser->getSectionName();
     
    // instantiate my content pattern
    $oContent = new PatternCustom();
	
	$aResults = $oBrowser->browse();
    
    require_once("../../../webpageTemplate.inc");    
	// display the browse results
    $oContent->addHtml(renderPage($aResults, $fBrowseType, $fSortBy, $fSortDirection));    
    $main->setCentralPayload($oContent);
    $main->setFormAction($_SERVER["PHP_SELF"]);    
    $main->render();    
}
?>