<?php

require_once("../../config/dmsDefaults.php");

/**
* Contains unit test code for class Folder in /lib/foldermanagement/Folder.inc
*
* Tests are:
* 	o creation of folder object
*	o setting/getting of values
*	o storing of object
*	o updating of object
*	o deletion of object
* @package tests.foldermanagement
*/


if (checkSession()) {
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	
	echo "<b>Testing creation of new folder object</b><br>";
	$oFol = & new Folder("Test folder object", "Test folder's #%&4#@% object", 1, $_SESSION["userID"], 1, 1, false);
	if (isset($oFol)) {
		echo "Passed folder creation test<br><br>";
		
		echo "<b>Testing getting and setting of values</b><br><br>";
		
		echo "Current value of primary key: " . $oFol->getID() . "<br>";
		echo "This value CANNOT be altered manually<br><br>";
		
		echo "Current value of folder name: " . $oFol->getName() . "<br>";
		echo "Setting folder name to: 'This is the new !#@@%^U&*()' folder name<br>";
		$oFol->setName("This is the new !#@@%^U&*() folder name");
		echo "New value of folder name: " . $oFol->getName() . "<br><br>";
		
		echo "Current value of folder description: " . $oFol->getDescription() . "<br>";
		echo "Setting description to '!@?|%&'''^*)*&%#@'<br>";
		$oFol->setDescription("!@?|%&'''^*)*&%#@");
		echo "New folder description: " . $oFol->getDescription() . "<br><br>";
		
		echo "Current folder parent id: " . $oFol->getParentID() . "<br>";
		echo "Setting the parentid to: 5<br>";
		$oFol->setParentID(5);
		echo "New folder parent id: " . $oFol->getParentID() . "<br><br>";
		
		echo "Current folder document type id: " . $oFol->getDocumentTypeID() . "<br>";
		echo "Setting folder document type to: 6<br>";
		$oFol->setDocumentTypeID(6);
		echo "New folder document type id: " . $oFol->getDocumentTypeID() . "<br><br>";
		
		echo "Current folder unit id: " . $oFol->getUnitID() . "<br>";
		echo "Setting the unit type to: 34<br>";
		$oFol->setUnitID(34);
		echo "New folder unit id: " . $oFol->getUnitID() . "<br><br>";
		
		echo "Current folder public status: " . ($oFol->getIsPublic() == false ? "false<br>" : "true<br>");
		echo "Setting the folder public status to: true<br>";
		$oFol->setIsPublic(true);
		echo "New folder public status: " . $oFol->getIsPublic() . "<br><br>";
		
		echo "<b>Testing storing of object in database</b><br>";
		if ($oFol->create()) {
			echo "Passed storing of object in database test<br><br>";
			
			echo "<b>Testing object updating</b><br>";
			if ($oFol->update()) {
				echo "Passed object updating test<br><br>";
				
				echo "<b>Testing getting of object from database using primary key</b><br>";
				$oNewFol = & Folder::get($oFol->getID());
				if (isset($oNewFol)) {
					echo "<pre> " . print_r($oNewFol) . "</pre><br>";
					echo "Passed getting of object from db using primary key<br><br>";
					
					echo "<b>Testing deletion of object from database</b><br>";
					if ($oFol->delete()) {
						echo "Passed deletion of object from database test.<br><br>END OF UNIT TEST";
					} else {
						echo "Failed deletion of object from database test";
					}						
				} else {
					echo "Failed getting of objec test.<br> Tests not run (a)deletion of object<br>";
				}
			} else {
				echo "Failed object updating test.<br> Tests not run (a)deletion of object (b)getting of object using id<br>";
			}
		} else {
			echo "Failed storing of object in database test.<br> Tests not run (a)updating of object (b)deletion of object (c)getting of object using id<br>";
		}		
	} else {
		echo "Failed folder creation tests.<br>Tests not run: (a)setting/getting of values (b)storing of object (c)updating of object (d)deletion of object (e)getting of object using id<br>";
	}
}

?>
