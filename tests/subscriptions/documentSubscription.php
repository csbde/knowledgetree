<?php

require_once("../../config/dmsDefaults.php");

/**
 * Contains unit test code for class DocumentSubscription in /lib/subscription/DocumentSubscription.inc
 *
 * Tests are:
 * - creation of document subscription object
 * - setting/getting of values
 * - storing of object
 * - updating of object
 * - deletion of object
 *
 * @package tests.subscriptions
 */
if (checkSession()) {
	require_once("$default->owl_fs_root/lib/subscriptions/DocumentSubscription.inc");
	
	echo "<b>Testing creation of new document subscription object</b><br>";
	$oDocumentSubscription = & new DocumentSubscription(1, 1);
	if (isset($oDocumentSubscription)) {
		echo "Passed document subscription creation test<br><br>";
		
		echo "<b>Testing getting and setting of values</b><br><br>";
		
		echo "Current value of primary key: " . $oDocumentSubscription->getID() . "<br>";
		echo "This value CANNOT be altered manually<br><br>";
		
		echo "Current value of document subscription user id: " . $oDocumentSubscription->getUserID() . "<br>";
		echo "Setting document subscription user id to: 12<br>";
		$oDocumentSubscription->setUserID(12);
		echo "New value of document subscription user id: " . $oDocumentSubscription->getUserID() . "<br><br>";
		
		echo "Current value of document subscription document id: " . $oDocumentSubscription->getDocumentID() . "<br>";
		echo "Setting document subscription document id to 34<br>";
		$oDocumentSubscription->setDocumentID(34);
		echo "New document subscription document id: " . $oDocumentSubscription->getDocumentID() . "<br><br>";
		
		echo "<b>Testing storing of object in database</b><br>";
		if ($oDocumentSubscription->create()) {
			echo "Passed storing of object in database test<br><br>";
			
			echo "<b>Testing object updating</b><br>";
			if ($oDocumentSubscription->update()) {
				echo "Passed object updating test<br><br>";
				
				echo "<b>Testing getting of object from database using primary key</b><br>";
				$oNewDocumentSubscription = & DocumentSubscription::get($oDocumentSubscription->getID());
				if (isset($oNewDocumentSubscription)) {
					echo "<pre> " . arrayToString($oNewDocumentSubscription) . "</pre><br>";
					echo "Passed getting of object from db using primary key<br><br>";
					
					echo "<b>Testing deletion of object from database</b><br>";
					if ($oDocumentSubscription->delete()) {
						echo "Passed deletion of object from database test.<br><br>END OF UNIT TEST";
					} else {
						echo "Failed deletion of object from database test";
					}						
				} else {
					echo "Failed getting of object test.<br> Tests not run (a)deletion of object<br>";
				}
			} else {
				echo "Failed object updating test.<br> Tests not run (a)deletion of object (b)getting of object using id<br>";
			}
		} else {
			echo "Failed storing of object in database test.<br> Tests not run (a)updating of object (b)deletion of object (c)getting of object using id<br>";
		}		
	} else {
		echo "Failed document subscription creation tests.<br>Tests not run: (a)setting/getting of values (b)storing of object (c)updating of object (d)deletion of object (e)getting of object using id<br>";
	}
}

?>
