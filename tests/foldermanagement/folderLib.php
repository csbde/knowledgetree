<?php
/**
* Contains unit tests for /lib/foldermanagement/FolderLib.inc
* Tests the following:
*	o isPublicFolder function
*	o getParentFolderID function
*	o 
*/

require_once("../../config/dmsDefaults.php");

if (checkSession()) {	
	require_once($default->owl_root_url . "/lib/documentmanagement/foldermanagement/FolderLib.inc");
	//check if the function isPublicFolder() works correctly
	echo "<b>Performing isPublicFolder() function test</b> (requires creation of new folder for test purposes<br>";
	if (FolderManagement::insertFolder("test folder", "test description", -1, $_SESSION["userID"], 1, 1, true)) {
		if (FolderLib::isPublicFolder(FolderManager::getFolderID("test folder", -1))) {
			echo "Passed isPublicFolder test<br>"; 
		} else {
			echo "Failed isPublicFolder test: " . $_SESSION["errorMessage"] . "<br>";
		}
	} else {
		echo "Failed to create folder: " . $_SESSION["errorMessage"] . ".  Please run tests in /tests/foldermanagement/folderManager.php";
	}
}


?>
