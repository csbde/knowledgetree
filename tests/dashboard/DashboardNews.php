<?php
require_once("../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/dashboard/DashboardNews.inc");

/**
 * $Id$
 * 
 * Unit Tests for lib/dashboard/DashboardNews.inc
 * includes tests for:
 *	o creation of document object
 *	o setting/getting of values
 *	o storing of object
 *	o updating of object
 *	o deletion of object
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package tests.dashboard
 */

//if (checkSession()) {
	
	// test creation of a dashboard news item
	echo "<b>Testing dashboard news creation</b><br>";
	// convert the image to a string
	$sTestImage = "c:\test.jpg";
	// TODO: implement
	//$sImgStr = DashboardNews::imageToString($sTestImage);
	$sImgStr = $sTestImage;
	$oNews = & new DashboardNews("test synopsis", "test body", 1, $sImgStr);
	if (isset($oNews)) {
		echo "Passed dashboard news creation test<br><br>";
		
		echo "<b>Testing setting and getting of dashboard news values</b><br>";		
		echo "Current dashboard synopsis: " . $oNews->getSynopsis() . "<br>";		
		echo "Setting synopsis to: blah<br>";
		$oNews->setSynopsis("blah");		
		echo "New synopsis: " . $oNews->getSynopsis() . "<br><br>";
		
		echo "Current news body: " . $oNews->getBody() . "<br>";
		echo "Setting news body to: 'blurg'<br>";
		$oNews->setBody("blurg");
		echo "New news body: " . $oNews->getBody() . "<br><br>";
	
		echo "Current news img: " . $oNews->getImage() . "<br>";
		echo "Setting news img to 'foo'<br>";
		$oNews->setImage('foo');
		echo "New news img: " . $oNews->getImage() . "<br><br>";
		
		echo "Current new rank: " . $oNews->getRank() . "<br>";
		echo "Set new rank to 5<br>";
		$oNews->setRank(5);
		echo "New new rank: " . $oNews->getRank() . "<br><br>";
				
		echo "<b>Testing dashboard news storage</b><br>";
		if ($oNews->create()) {
			echo "Passed dashboard news storage test<br><br>";

			echo "<b>Testing dashboard news update</b><br>";
			echo "setting attributes to ('a','b','c',10)";
			$oNews->setSynopsis("a");
			$oNews->setBody("b");
			$oNews->setImage("c");
			$oNews->setRank(10);
			if ($oNews->update()) {
				echo "Passed dashboard news update test<br>";
			} else {
				echo "Failed dashboard news update test: " . $_SESSION["errorMessage"];
			}

			echo "<b>Testing dashboard news deletion</b><br>";
			if ($oNews->delete()) {
				echo "Passed dashboard news deletion test<br>";
			} else {
				echo "Failed dashboard news deletion test: " . $_SESSION["errorMessage"];
			}

		} else {
			echo "Failed dashboard news storage test: " . $_SESSION["errorMessage"] . "<br>";
			echo "Tests NOT run: (a)dashboard news deletion (b) dashboard news update<br>";
		}
	} else {
		echo "Failed dashboard news creation test<br>";
		echo "Tests NOT run: (a)getting and setting (b)dashboard news storage (c)dashboard news deletion<br>";
	}
//}
?>
