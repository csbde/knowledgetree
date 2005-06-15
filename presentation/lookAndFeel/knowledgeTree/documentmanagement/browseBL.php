<?php

require_once("../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fActions', 'fBrowseType', 'fDocumentIDs', 'fFolderID', 'fSortBy', 'fSortDirection');

require_once("$default->fileSystemRoot/lib/browse/BrowserFactory.inc");
require_once("$default->fileSystemRoot/lib/browse/Browser.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->uiDirectory/documentmanagement/browseUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
/**
 * $Id$
 *
 * This page controls browsing for documents- this can be done either by
 * folder, category or document type.
 * The relevant permission checking is performed, calls to the business logic
 * layer to retrieve the details of the documents to view are made and the user
 * interface is contructed.
 *
 * Querystring variables
 * ---------------------
 * fBrowseType - determines whether to browse by (folder, category, documentType) [mandatory]
 * fFolderID - the folder to browse [optional depending on fBrowseType]
 * fCategoryName - the category to browse [optional depending on fBrowseType]
 * fDocumentTypeID - the document type id to browse [optional depending on fBrowseType]
 * fSortBy - the document attribute to sort the browse results by
 * fSortDirection - the direction to sort
 * fActions - action for group operations
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

// only if we have a valid session
if (!checkSession()) {
    exit(0);
}

if (isset($fActions)) {
    // tack on POSTed document ids and redirect to the expunge deleted documents page
    $sUniqueID = KTUtil::randomString();
    $_SESSION["documents"][$sUniqueID] = $fDocumentIDs;
    $sQueryString = "fRememberDocumentID=$sUniqueID&";
    $sQueryString .= "fReturnFolderID=$fFolderID&";

    switch ($fActions) {
    case "delete":
        // delete all selected docs
        controllerRedirect("deleteDocument", $sQueryString);
        exit(0);
        break;
    case "move":
        // Move selected docs to root folder
        controllerRedirect("moveDocument", $sQueryString . "fFolderID=1");
        exit(0);
        break;
    }
}

// retrieve variables
if (!$fBrowseType) {
    // required param not set- internal error or user querystring hacking
    // set it to default= folder
    $fBrowseType = "folder";
}

// retrieve field to sort by
if (!$fSortBy) {
    // no sort field specified- default is document name
    $fSortBy = "filename";
}
// retrieve sort direction
if (!$fSortDirection) {
    $fSortDirection = "asc";
}
   
// fire up the document browser 
$oBrowser =& BrowserFactory::create($fBrowseType, $fSortBy, $fSortDirection);
$sectionName = $oBrowser->getSectionName();
 
// instantiate my content pattern
$oContent = new PatternCustom();	
$aResults = $oBrowser->browse();

require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    

if (PEAR::isError($aResults)) {
    $oContent->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\" /></a>\n");
    $main->setErrorMessage($aResults->getMessage());
    $main->setCentralPayload($oContent);
    $main->setFormAction($_SERVER["PHP_SELF"]);
    $main->setSubmitMethod("GET");    
    $main->render();    
    exit(0);
}

if (($fBrowseType == "folder") && (!isset($fFolderID))) {
    // FIXME: check that the first folder in the array exists, no permission otherwise
    if ($default->browseToRoot) {
        controllerRedirect("browse", "fFolderID=1");
    } else {
        controllerRedirect("browse", "fFolderID=" . $aResults["folders"][0]->getID());
    }
}

// display the browse results
$oContent->addHtml(renderPage($aResults, $fBrowseType, $fSortBy, $fSortDirection));
$main->setCentralPayload($oContent);
$main->setFormAction($_SERVER["PHP_SELF"]);
$main->setSubmitMethod("GET");
$main->render();    

?>
