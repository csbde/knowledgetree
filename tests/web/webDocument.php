<?php

require_once("../../config/dmsDefaults.php");

/**
* Unit test for class DocumentField found in /lib/documentmanagement/DocumentField.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
* @package tests.web
*/


if (checkSession) {
	require_once("$default->fileSystemRoot/lib/web/WebDocument.inc");
	
	$oWebDocument = & new WebDocument(1, 1, 1, 1, getCurrentDateTime());
	echo "Create ? " . ($oWebDocument->create() ? "Yes" : "No") . "<br>";
	$oWebDocument = & new WebDocument(1, 1, 1, 1, getCurrentDateTime());
	$oWebDocument->create();
	echo "Update ? " . ($oWebDocument->update() ? "Yes" : "No") . "<br>";
	echo "Delete ? " . ($oWebDocument->delete() ? "Yes" : "No") . "<br>";
	$oNewDocumentField = WebDocument::get(1);
	echo "Get ? <pre>" . var_dump($oNewDocumentField) . "</pre>";
}

?>
