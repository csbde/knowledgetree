<?php

/**
* Contains unit tests for class Document in /lib/documentmanagement/Document.inc
*
* Tests:
*	o creation of document object
*	o setting/getting of values
*	o storing of object
*	o updating of object
*	o deletion of object
*/

require_once("../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->owl_root_url/lib/documentmanagement/Document.inc");
	require_once("$default->owl_root_url/lib/foldermanagement/FolderManager.inc");
	
	//test creation of a document
	echo "<b>Testing document creation</b><br>";
	$oDoc = & new Document("Test document", "Test document", 100, $_SESSION["userID"], "Test of document object", 1, 10);
	if (isset($oDoc)) {
		echo "Passed document creation test<br><br>";
		
		echo "<b>Testing setting and getting of document values</b><br>";
		
		echo "Current document type ID: " . $oDoc->getDocumentTypeID() . "<br>";		
		echo "Setting document type ID to: 5<br>";
		$oDoc->setDocumentTypeID(5);		
		echo "New document type id: " . $oDoc->getDocumentTypeID() . "<br><br>";
		
		echo "Current document name: " . $oDoc->getName() . "<br>";
		echo "Setting document name to: 'Another document name'<br>";
		$oDoc->setName("Another document name");
		echo "New document name: " . $oDoc->getName() . "<br><br>";
		
		echo "Current document fileName: " . $oDoc->getFileName() . "<br>";
		echo "This value CANNOT be set manually, but must be derived<br><br>";
		
		echo "Current document file size: " . $oDoc->getFileSize() . "<br>";
		echo "Setting file to 500<br>";
		$oDoc->setFileSize(500);
		echo "New document file size: " . $oDoc->getFileSize() . "<br><br>";
		
		echo "Current document creator id: " . $oDoc->getCreatorID() . "<br>";
		echo "Setting creator id to 100<br>";
		$oDoc->setCreatorID(100);
		echo "New creator id: " . $oDoc->getCreatorID() . "<br><br>";
		
		echo "Current document last modified date: " . $oDoc->getLastModifiedDate() . "<br>";
		echo "Set last modified date to now<br>";
		$oDoc->setLastModifiedDate(getCurrentDateTime());
		echo "New last modified date: " . $oDoc->getLastModifiedDate() . "<br><br>";
		
		echo "Current document description: " . $oDoc->getDescription() . "<br>";
		echo "Setting description to 'A new description'<br>";
		$oDoc->setDescription("A new description");
		echo "New document description: " . $oDoc->getDescription() . "<br><br>";
		
		echo "Current document mime type id: " . $oDoc->getMimeTypeID() . "<br>";
		echo "Setting mime type id to 3<br>";
		$oDoc->setMimeTypeID(3);
		echo "New document mime type id: " . $oDoc->getMimeTypeID() . "<br><br>";
		
		echo "Current document major version number: " . $oDoc->getMajorVersionNumber() . "<br>";
		echo "Setting the major version number to 1<br>";
		$oDoc->setMajorVersionNumber(1);
		echo "New document major version number: " . $oDoc->getMajorVersionNumber() . "<br><br>";
		
		echo "Current document minor version number: " . $oDoc->getMinorVersionNumber() . "<br>";
		echo "Setting the minor version number to 2<br>";
		$oDoc->setMinorVersionNumber(2);
		echo "New document major version number: " . $oDoc->getMinorVersionNumber() . "<br><br>";
		
		echo "Current document checked out status: " . $oDoc->getIsCheckedOut() . "<br>";
		echo "Setting checked out status to true<br>";
		$oDoc->setIsCheckedOut(true);
		echo "New document checked out status: " . $oDoc->getIsCheckedOut() . "<br><br>";
		
		echo "<b>Testing document storage</b><br>";
		if ($oDoc->create()) {
			echo "Passed document storage test<br><br>";
			
			echo "<b>Testing document deletion</b><br>";
			if ($oDoc->delete()) {
				echo "Passed document deletion test<br>";
			} else {
				echo "Failed document deletion test: " . $_SESSION["errorMessage"];
			}
		} else {
			echo "Failed document storage test: " . $_SESSION["errorMessage"] . "<br>";
			echo "Tests NOT run: (a)document deletion<br>";
		}
	} else {
		echo "Failed document creation test<br>";
		echo "Tests NOT run: (a)getting and setting (b)document storage (c)document deletion<br>";
	}
	
	
	
	
	
}





?>
