<?php

/**
* Unit tests for class Lookup in /lib/Lookup.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
*
*/

require_once("../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/DefaultLookup.inc");
	
	$oLookup = & new DefaultLookup("document_transaction_types_lookup", "View");
	echo "Store? " . ($oLookup->create() ? "Yes" : "No: " . $_SESSION["errorMessage"]) . "<br>";
	echo "Update? " . ($oLookup->update() ? "Yes" : "No: " . $_SESSION["errorMessage"]) . "<br>";
	echo "Delete? " . ($oLookup->delete() ? "Yes" : "No: " . $_SESSION["errorMessage"]) . "<br>";
	$oNewLookup = DefaultLookup::get("document_transaction_types_lookup", 1);	
	if (!($oNewLookup === false)) {
		echo "Get? Yes -> <pre> " . var_dump($oNewLookup) . "</pre><br>";		
	} else {
		echo "Get? No: " . $_SESSION["errorMessage"];
	}
}

?>
