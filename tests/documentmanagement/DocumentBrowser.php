<?php
/**
 * $Id$
 * 
 * Unit Tests for lib/documentmanagement/DocumentBrowser.inc
 * includes tests for:
 *      browseByFolder($folderID)
 *      browseByCategory($category)
 *      browseByDocumentType($documentTypeID) 
 *
 * @version $Revision$ 
 * @author <a href="mailto:michael@jamwarehouse.com">Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package tests/documentmanagement
 */

require_once ("../../config/dmsDefaults.php");
require_once ("$default->owl_fs_root/lib/documentmanagement/DocumentBrowser.inc");

// -------------------------------
// page start
// -------------------------------
if (checkSession()) {

    $default->log->debug("DocumentBrowser.php:: authorised flag:" . $_SESSION["authorised"]);
    // check if this page is authorised, ie. has come from control.php
    if ($_SESSION["authorised"]) {
        
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
        $categories = $db->browseByCategory();
        if (!is_null($_SESSION["errorMessage"])) {
            echo "error: " . $_SESSION["errorMessage"] . "<br>";
            $_SESSION["errorMessage"] = NULL;
        }        
        print_r($categories);
        
        // pick a random category
        srand ((float) microtime() * 10000000); 
        $rand_keys = array_rand ($categories, 1);
        $category = $categories[$rand_keys];
        echo "browsing by category = $category<br>";
        $artifacts = $db->browseByCategory($category);
        if (!is_null($_SESSION["errorMessage"])) {
            echo "error: " . $_SESSION["errorMessage"] . "<br>";
            $_SESSION["errorMessage"] = NULL;
        }         
        print_r($artifacts);

        // document type browsing
        echo "document type browse- get list of doc types<br>";
        $documentTypes = $db->browseByDocumentType();
        if (!is_null($_SESSION["errorMessage"])) {
            echo "error: " . $_SESSION["errorMessage"] . "<br>";
            $_SESSION["errorMessage"] = NULL;
        }        
        print_r($documentTypes);
        
        // pick a random document type
        srand ((float) microtime() * 10000000); 
        $documentTypeID = array_rand ($documentTypes, 1);
                
        echo "browsing by document type = " . $documentTypeID . "<br>";
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
}
?>
