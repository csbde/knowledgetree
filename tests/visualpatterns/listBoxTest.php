<?php

require_once("../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");

/**
* Unit test code from PatternListBox class in /lib/visualpatterns/PatternListBox.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 16 January 2003
* @package tests.visualpatterns
*/

	
$oPatternListBox = & new PatternListBox("folders", "name", "id", "folders");
echo "<html><head></head><body>" . $oPatternListBox->render() . "</body></html>";


?>
