<?php
/**
 * $Id$
 * 
 * Unit Tests for lib/documentmanagement/DocumentBrowser.inc
 *
 * @version $Revision$ 
 * @author <a href="mailto:michael@jamwarehouse.com>Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package dmslib
 */

require_once ("../../config/dmsDefaults.php");
require_once ("$default->owl_root_url/lib/owl.lib.php");
require_once ("$default->owl_root_url/lib/lib/documentmanagement/DocumentBrowser.inc");

// -------------------------------
// page start
// -------------------------------

// TODO: need to start the session

$db = new DocumentBrowser();
// default browse- should resolve to root folder
$artifacts = $db->browseByFolder();
print_r($artifacts);
// now supply a folderid
$folderID = 2;
$artifacts = $db->browseByFolder($folderID);
print_r($artifacts);

// browse by category
$categories = $db->browseByCategory();
print_r($categories);
// pick a random category
srand ((float) microtime() * 10000000); 
$rand_keys = array_rand ($categories, 1); 
$category = $categories[$rand_keys[0]];
echo "browsing by category = $category<br>";
$artifacts = $db->browseByCategory($category);
print_r($artifacts);

// document type browsing
$documentTypes = $db->browseByDocumentType();
print_r($documentTypes);
// pick a random document type
srand ((float) microtime() * 10000000); 
$rand_keys = array_rand ($documentTypes, 1); 
$documentType = $documentTypes[$rand_keys[0]];
echo "browsing by category = $documentType<br>";
$artifacts = $db->browseByDocumentType($documentType);
print_r($artifacts);
?>
