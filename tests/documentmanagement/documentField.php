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
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
	
	$oDocumentField = & new DocumentField("Test", "S*#(*##@#% Arb data type");
	echo "Create ? " . ($oDocumentField->create() ? "Yes" : "No") . "<br>";
	echo "Update ? " . ($oDocumentField->update() ? "Yes" : "No") . "<br>";
	echo "Delete ? " . ($oDocumentField->delete() ? "Yes" : "No") . "<br>";
	$oNewDocumentField = DocumentField::get(1);
	echo "Get ? <pre>" . var_dump($oNewDocumentField) . "</pre>";
}

?>
