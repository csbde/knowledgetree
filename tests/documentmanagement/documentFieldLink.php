<?php

require_once("../../config/dmsDefaults.php");

/**
* Unit test for class DocumentField found in /lib/documentmanagement/DocumentField.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
* @package tests.documentmanagement
*/

if (checkSession) {
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentFieldLink.inc");
	
	$oDocumentFieldLink = & new DocumentFieldLink(1, 1, "test");
	echo "Create ? " . ($oDocumentFieldLink->create() ? "Yes" : "No") . "<br>";
	$oDocumentFieldLink = & new DocumentFieldLink(1, 1, "test");
	$oDocumentFieldLink->create();
	echo "Update ? " . ($oDocumentFieldLink->update() ? "Yes" : "No") . "<br>";
	echo "Delete ? " . ($oDocumentFieldLink->delete() ? "Yes" : "No") . "<br>";
	$oNewDocumentFieldLink = DocumentFieldLink::get(1);
	echo "Get ? <pre>" . var_dump($oNewDocumentFieldLink) . "</pre>";
}

?>
