<?php

/**
* Unit test for functions in /lib/foldermanagement/FolderManager.inc
*
* Tests performed:
*	o Insert folder
*	o Insert duplicate folder
*	o Get folder primary key using folder name and parent folder id
*	o Delete existing folder
*	o Delete non-existant folder
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 13 January 2003
*/

require_once("../../config/dmsDefaults.php");

if (checkSession()) {
	require_once($default->owl_root_url . "/lib/foldermanagement/FolderManager.inc");
	//check creation of a folder
	echo "<b>Testing creating of a new folder</b><br>";
	if (FolderManager::createFolder("Test folder", "This is just a test' %//^&* folder", -1, $_SESSION["userID"], 1, 1, true)) {
		echo "Passed creation of new folder test<br>";
	} else {
		echo "Failed creation of a new folder test: " . $_SESSION["errorMessage"] . "<br>";
	}
	
	
	//check creation of a duplicate folder
	echo "<b>Testing creation of duplicate folder</b><br>";
	if (!FolderManager::createFolder("Test folder", "This is another a test' %//^&* folder", -1, $_SESSION["userID"], 1, 1, true)) {
		echo "Passed creation of duplicate folder test<br>";
	} else {
		echo "Failed creation of a dupliate folder test: " . $_SESSION["errorMessage"] . "<br>";
	}
	
	//check getting a folder id
	echo "<b>Testing getting a folder id</b><br>";
	$iFolderID;
	if (($iFolderID = FolderManager::getFolderID("Test folder", -1)) === false) {
		echo "Failed get folder id test<br>";	
	} else {
		echo "Passed get folder id test<br>";
	}
	
	//check deletion of an existing folder
	echo "<b>Testing deletion of an existing folder</b><br>";
	if (FolderManager::deleteFolder($iFolderID)) {
		echo "Passed deletion of an existing folder test<br>";
	} else {
		echo "Failed deletion of an existing folder test: " . $_SESSION["errorMessage"] . "<br>";
	}
	
	//check deletion of a non-existant folder
	echo "<b>Testing deletion of a non-existant folder</b><br>";
	if (!(FolderManager::deleteFolder($iFolderID))) {
		echo "Passed deletion of an non-existant folder test<br>";
	} else {
		echo "Failed deletion of an non-existant folder test: " . $_SESSION["errorMessage"] . "<br>";
	}

}



?>
