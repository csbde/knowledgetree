<?php

require_once ("../../config/dmsDefaults.php");
require_once ("$default->fileSystemRoot/lib/documentmanagement/DocumentBrowser.inc");

/**
 * $Id$
 * 
 * Unit Tests for lib/documentmanagement/DocumentBrowser.inc
 * includes tests for:
 *      browseByFolder($folderID)
 *      browseByCategory($category)
 *      browseByDocumentType($documentTypeID) 
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package tests.documentmanagement
 */

// -------------------------------
// page start
// -------------------------------

if (checkSession()) {
        
    echo "<pre>";
    $db = new DocumentBrowser();

    // default browse- should resolve to root folder
    echo "default browse- starts at this users root folder<br>";
    $artifacts = $db->browseByFolder();
    if (!is_null($_SESSION["errorMessage"])) {
        echo "error: " . $_SESSION["errorMessage"] . "<br>";
        $_SESSION["errorMessage"] = NULL;
    }        
    print_r($artifacts);

    // now supply a folderid
    $folderID = 3;
    echo "browse- starting at folder (folderID=$folderID)<br>";
    $artifacts = $db->browseByFolder($folderID);
    if (!is_null($_SESSION["errorMessage"])) {
        echo "error: " . $_SESSION["errorMessage"] . "<br>";
        $_SESSION["errorMessage"] = NULL;
    }        
    print_r($artifacts);

    // browse by category
    echo "category browse- return a list of categories:<br>";        
    $results = $db->browseByCategory();
    if (!is_null($_SESSION["errorMessage"])) {
        echo "error: " . $_SESSION["errorMessage"] . "<br>";
        $_SESSION["errorMessage"] = NULL;
    }        
    print_r($results);
    
    // pick the first category
    $category = $results["categories"][0];
    echo "browsing by category = $category<br>";
    $artifacts = $db->browseByCategory($category);
    if (!is_null($_SESSION["errorMessage"])) {
        echo "error: " . $_SESSION["errorMessage"] . "<br>";
        $_SESSION["errorMessage"] = NULL;
    }         
    print_r($artifacts);

    // document type browsing
    echo "document type browse- get list of doc types<br>";
    $results = $db->browseByDocumentType();
    if (!is_null($_SESSION["errorMessage"])) {
        echo "error: " . $_SESSION["errorMessage"] . "<br>";
        $_SESSION["errorMessage"] = NULL;
    }        
    print_r($results);
    
    // pick the first document type id
    srand ((float) microtime() * 10000000); 
    $documentTypeID = $results["documentTypes"][0]["id"];
    $documentTypeName = $results["documentTypes"][0]["name"];        
    echo "browsing by document type = $documentTypeID; name=$documentTypeName<br>";
    $artifacts = $db->browseByDocumentType($documentTypeID);
    if (!is_null($_SESSION["errorMessage"])) {
        echo "error: " . $_SESSION["errorMessage"] . "<br>";
        $_SESSION["errorMessage"] = NULL;
    }        
    print_r($artifacts);
    
    echo "</pre>";
} else {
    // FIXME: redirect to no permission page
    print "you do not have access to view this page!  please go away, and come back when you do.<br>";
    echo generateLink("logout") . "logout</a>";    
}
?>
