<?php

/**
* Unit tests for ./lib/documentmanagement/DocumentManager class, 
* document type functionality
*
*/

require_once ("../../../config/dmsDefaults.php");
require_once ("$default->owl_root_url/lib/owl.lib.php");
require_once ("../../../lib/documentmanagement/documentManager.inc");

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

if ($docManager->createDocumentType("Test")) {
	echo "Passed document type creation test<br>";	
} else {
	echo "Failed 'document type creation' test: " . $default->errorMessage . "<br>";	
}

//test creation of duplicate document types
if (!$docManager->createDocumentType("Test")) {
	echo "Passed 'duplicate document type creation' test<br>";	
} else {
	echo  "Failed duplicate document type creation test<br>";
}

//test deletion of an existing document type
if ($docManager->deleteDocumentType("Test")) {
	echo "Passed 'existing document type deletion' test<br>";
} else {
	echo "Failed existing document type deletion test<br>";
}

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

//test creation of a field
if ($docManager->createDocumentTypeField("Test Field","VARCHAR")) {
	echo "Passed 'creation of document type field' test<br>";
} else {
	echo "Failed 'creation of document type field' test<br>";	
}

//test creation of duplicate field
if (!($docManager->createDocumentTypeField("Test Field","VARCHAR"))) {
	echo "Passed 'creation of duplicate document type field ' test<br>";
} else {
	echo "Failed 'creation of duplicated document type field ' test<br>: $default->errorMessage";
}

//test deletion of a field
if ($docManager->deleteDocumentTypeField("Test Field")) {
	echo "Passed 'deletion of document type field ' test<br>";
} else {
	echo "Failed 'deletion of document type field ' test<<br>";
}

//test deletion of a non-existant field
if (!$docManager->deleteDocumentTypeField("Test Field that doesn't exist")) {
	echo "Passed 'deletion of non-existant document type field ' test<br>";
} else {
	echo "Failed 'deletion of non-existant document type field ' test<<br>";
}





?>
