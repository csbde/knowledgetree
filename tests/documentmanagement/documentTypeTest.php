<?php

/**
* Unit tests for ./lib/documentmanagement/DocumentManager class, 
* document type functionality
*
*/

require_once ("../../config/owl.php");
require_once ($default->owl_fs_root . "/config/environment.php");
require_once ($default->owl_fs_root . "/config/dmsDefaults.php");
require_once ($default->owl_fs_root . "/lib/owl.lib.php");
require_once ($default->owl_fs_root . "/lib/documentmanagement/documentManager.inc");

/**
* Database backend unit tests for:
* 	o Document type (document_types)
* 	o Field (fields)
*
* @author Rob Cherry, Jam Warehouse (Pty Ltd), South Africa
* @date 9 January 2003 
*/

//test creation of document type

global $default;

$docManager = new DocumentManager();

$docManager->deleteDocumentType("Test Document");
$docManager->deleteDocumentTypeField("Test Field");

echo "<b>Testing creation of document types</b><br>";
if ($docManager->createDocumentType("Test")) {
	echo "Passed document type creation test<br>";	
} else {
	echo "Failed 'document type creation' test: " . $default->errorMessage . "<br>";	
}

echo "<b>Testing creation of duplicate document types</b><br>";
//test creation of duplicate document types
if (!$docManager->createDocumentType("Test")) {
	echo "Passed 'duplicate document type creation' test<br>";	
} else {
	echo  "Failed duplicate document type creation test<br>";
}

echo "<b>Testing deletion of document types</b><br>";
//test deletion of an existing document type
if ($docManager->deleteDocumentType("Test")) {
	echo "Passed 'existing document type deletion' test<br>";
} else {
	echo "Failed existing document type deletion test<br>";
}

echo "<b>Testing deletion of non-existant document types</b><br>";
//test deletion of a document type that doesn't exist
if (!$docManager->deleteDocumentType("Does not exist")) {
	echo "Passed 'deletion of non-existing document type' test<br>";
} else {
	echo "Failed 'deletion of non-existant document type' test<br>";
}

/**
* 
* Field type tests
* 
*/

echo "<b>Testing creation of document field types</b><br>";
//test creation of a field
if ($docManager->createDocumentTypeField("Test Field","VARCHAR")) {
	echo "Passed 'creation of document type field' test<br>";
} else {
	echo "Failed 'creation of document type field' test<br>";	
}

echo "<b>Testing creation of duplicate document field types</b><br>";
//test creation of duplicate field
if (!($docManager->createDocumentTypeField("Test Field","VARCHAR"))) {
	echo "Passed 'creation of duplicate document type field ' test<br>";
} else {
	echo "Failed 'creation of duplicated document type field ' test<br>: $default->errorMessage";
}

echo "<b>Testing deletion of document field types</b><br>";
//test deletion of a field
if ($docManager->deleteDocumentTypeField("Test Field")) {
	echo "Passed 'deletion of document type field ' test<br>";
} else {
	echo "Failed 'deletion of document type field ' test<<br>";
}

echo "<b>Testing deletion of non-existant document types</b><br>";
//test deletion of a non-existant field
if (!$docManager->deleteDocumentTypeField("Test Field that doesn't exist")) {
	echo "Passed 'deletion of non-existant document type field ' test<br>";
} else {
	echo "Failed 'deletion of non-existant document type field ' test<<br>";
}

/**
*
* Document type, document field type link test
*
*/

echo "<b>Testing linking of document types and document field types</b><br>";
//test the linking of a document to a document field type
$docManager->createDocumentType("Test Document");
$docManager->createDocumentTypeField("Test Field", "VARCHAR");

$documentTypeID = $docManager->getDocumentTypeID("Test Document");
$documentTypeFieldID = $docManager->getDocumentTypeFieldID("Test Field");
if (!(is_bool($documentTypeID)) && !(is_bool($documentTypeField))) {
	echo "Passed document type and document type field id retreival test<br>";
	if ($docManager->createDocumentTypeFieldLink($documentTypeID, $documentTypeFieldID, true)) {
		echo "Passed linking of document types and document field types test<br>";
	} else {
		echo "Passed linking of document types and document field types test: " . $default->errorMessage . "<br>";
	}
} else {
	echo "Failed document type and document type field id retreival test: " . $default->errorMessage . "<br>";	
}

echo "<b>Testing deletion of link between document types and document field types</b><br>";
if ($docManager->deleteDocumentTypeFieldLink($documentTypeID, $documentTypeFieldID)) {
	echo "Passed deletion of link between document types and document field types<br>";
} else {
	echo "Failed deletion of link between document types and document field types<br>";
}

$docManager->deleteDocumentType("Test Document");
$docManager->deleteDocumentTypeField("Test Field");

?>
