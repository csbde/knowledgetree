<?php

require_once("../../config/dmsDefaults.php");

/**
 * Contains unit test code for class FolderSubscription in /lib/subscription/FolderSubscription.inc
 *
 * Tests are:
 * - creation of document subscription object
 * - setting/getting of values
 * - storing of object
 * - updating of object
 * - deletion of object
 * @package tests.subscriptions
 */
if (checkSession()) {
	require_once("$default->owl_fs_root/lib/subscriptions/FolderSubscription.inc");
	
	echo "<b>Testing creation of new folder subscription object</b><br>";
	$oFolderSubscription = & new FolderSubscription(1, 1);
	if (isset($oFolderSubscription)) {
		echo "Passed folder subscription creation test<br><br>";
		
		echo "<b>Testing getting and setting of values</b><br><br>";
		
		echo "Current value of primary key: " . $oFolderSubscription->getID() . "<br>";
		echo "This value CANNOT be altered manually<br><br>";
		
		echo "Current value of folder subscription user id: " . $oFolderSubscription->getUserID() . "<br>";
		echo "Setting folder subscription user id to: 23<br>";
		$oFolderSubscription->setUserID(23);
		echo "New value of folder subscription user id: " . $oFolderSubscription->getUserID() . "<br><br>";
		
		echo "Current value of folder subscription folder id: " . $oFolderSubscription->getFolderID() . "<br>";
		echo "Setting folder subscription folder id to 56<br>";
		$oFolderSubscription->setFolderID(56);
		echo "New folder subscription folder id: " . $oFolderSubscription->getFolderID() . "<br><br>";
		
		echo "<b>Testing storing of object in database</b><br>";
		if ($oFolderSubscription->create()) {
			echo "Passed storing of object in database test<br><br>";
			
			echo "<b>Testing object updating</b><br>";
			if ($oFolderSubscription->update()) {
				echo "Passed object updating test<br><br>";
				
				echo "<b>Testing getting of object from database using primary key</b><br>";
				$oNewFolderSubscription = & FolderSubscription::get($oFolderSubscription->getID());
				if (isset($oNewFolderSubscription)) {
					echo "<pre> " . arrayToString($oNewFolderSubscription) . "</pre><br>";
					echo "Passed getting of object from db using primary key<br><br>";
					
					echo "<b>Testing deletion of object from database</b><br>";
					if ($oFolderSubscription->delete()) {
						echo "Passed deletion of object from database test.<br><br>END OF UNIT TEST";
					} else {
						echo "Failed deletion of object from database test(" . $_SESSION["errorMessage"] . ")";
					}						
				} else {
					echo "Failed getting of object test(" . $_SESSION["errorMessage"] . ").<br> Tests not run (a)deletion of object<br>";
				}
			} else {
				echo "Failed object updating test(" . $_SESSION["errorMessage"] . ").<br> Tests not run (a)deletion of object (b)getting of object using id<br>";
			}
		} else {
			echo "Failed storing of object in database test (" . $_SESSION["errorMessage"] . ").<br> Tests not run (a)updating of object (b)deletion of object (c)getting of object using id<br>";
		}		
	} else {
		echo "Failed folder subscription creation tests(" . $_SESSION["errorMessage"] . ").<br>Tests not run: (a)setting/getting of values (b)storing of object (c)updating of object (d)deletion of object (e)getting of object using id<br>";
	}
}

?>
