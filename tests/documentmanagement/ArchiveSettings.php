<?php

require_once("../../config/dmsDefaults.php");

/**
 * Contains unit tests for class ArchiveSettings in /lib/documentmanagement/ArchiveSettings.inc
 *
 * Tests are:
 *	o creation of archive settings object
 *	o setting/getting of values
 *	o storing of object
 *	o updating of object
 * 	o deletion of object
 *   o retrieval of object by primary key
 *   o retrieval of an array of objects
 * @package tests.documentmanagement
 */

require_once("$default->fileSystemRoot/lib/documentmanagement/ArchiveSettings.inc");
echo "<pre>";	
//test creation of archive settings
echo "<b>Testing archive settings creation</b><br>";
$oArchiveSettings = & new ArchiveSettings(6, "2003-06-14", -1);
if (isset($oArchiveSettings)) {
	echo "Passed archive settings creation test<br><br>";
	
	echo "<b>Testing setting and getting of values</b><br>";
	
	echo "Current document ID: " . $oArchiveSettings->getDocumentID() . "<br>";		
	echo "Setting document ID to: 5<br>";
	$oArchiveSettings->setDocumentID(5);		
	echo "New document id: " . $oArchiveSettings->getDocumentID() . "<br><br>";
	
	echo "Current expiration date: " . $oArchiveSettings->getExpirationDate() . "<br>";
	echo "Setting expiration date to: '2003-07-14'<br>";
	$oArchiveSettings->setExpirationDate("2003-07-14");
	echo "New expiration date: " . $oArchiveSettings->getExpirationDate() . "<br><br>";
	
	echo "Current utilisation threshold : " . $oArchiveSettings->getUtilisationThreshold() . "<br>";
	echo "Setting expiration date to: '2003-07-14'<br>";
	$oArchiveSettings->setExpirationDate("2003-07-14");
	echo "New expiration date: " . $oArchiveSettings->getExpirationDate() . "<br><br>";
	
	echo "<b>Testing archive settings storage</b><br>";
	if ($oArchiveSettings->create()) {
		echo "Passed archive settings storage test<br><br>";
		
		echo "<b>Testing archive settings retrieval</b><br>";
		$oNewArchiveSettings = ArchiveSettings::get(1);
		if ($oNewArchiveSettings) {
			echo "Passed archive settings retrieval test:" . arrayToString($oNewArchiveSettings) . "<br>";
		} else {
			echo "Failed archive settings retrieval test.<br>";
		}
						
		echo "<b>Testing archive settings array retrieval</b><br>";
		$aArchiveSettings = ArchiveSettings::getList();
		echo "Archive Settings array=" . arrayToString($aArchiveSettings) . "<br><br>";
					
		echo "<b>Testing archive settings deletion</b><br>";
		if ($oArchiveSettings->delete()) {
			echo "Passed archive settings deletion test<br>";
		} else {
			echo "Failed archive settings deletion test";
		}
	} else {
		echo "Failed archive settings storage test<br>";
		echo "Tests NOT run: (a)archive settings deletion<br>";
	}
} else {
	echo "Failed archive settings creation test<br>";
	echo "Tests NOT run: (a)getting and setting (b)archive settings storage (c)archive settings deletion<br>";
}
echo "</pre>";

?>
