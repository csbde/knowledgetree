<?php

/**
* Unit test for class DocumentField found in /lib/documentmanagement/DocumentField.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
*
*/

require_once("../../config/dmsDefaults.php");

if (checkSession) {
	require_once("$default->owl_fs_root/lib/documentmanagement/DocumentField.inc");
	
	$oDocumentField = & new DocumentField("Test", "S*#(*##@#% Arb data type");
	echo "Create ? " . ($oDocumentField->create() ? "Yes" : "No") . "<br>";
	echo "Update ? " . ($oDocumentField->update() ? "Yes" : "No") . "<br>";
	echo "Delete ? " . ($oDocumentField->delete() ? "Yes" : "No") . "<br>";
	$oNewDocumentField = DocumentField::get(1);
	echo "Get ? <pre>" . var_dump($oNewDocumentField) . "</pre>";
}

?>
