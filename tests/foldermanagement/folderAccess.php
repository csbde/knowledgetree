<?php

require_once("../../config/dmsDefaults.php");

/**
 * $Id$
 *
 * Unit tests for FolderAccess class found in /lib/foldermanagement/FolderAccess.inc
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package tests.foldermanagement
 */

if (checkSession) {
	require_once("$default->fileSystemRoot/lib/foldermanagement/FolderAccess.inc");
	echo "<pre>";
	$oFolderAccess = & new FolderAccess(1, 1, true, true);
	echo "Create ? " . ($oFolderAccess->create() ? "Yes" : "No") . "<br>";
    $oFolderAccess->delete();
	$oFolderAccess = & new FolderAccess(1, 2, true, false);
	$oFolderAccess->create();$oFolderAccess->delete();
	$oFolderAccess = & new FolderAccess(1, 3, false, true);
	$oFolderAccess->create();$oFolderAccess->delete();
	$oFolderAccess = & new FolderAccess(1, 4, false, true);
	$oFolderAccess->create();$oFolderAccess->delete();
	$oFolderAccess = & new FolderAccess(1, 5, true, true);
	$oFolderAccess->create();
	echo "Update ? " . ($oFolderAccess->update() ? "Yes" : "No") . "<br>";
	echo "Delete ? " . ($oFolderAccess->delete() ? "Yes" : "No") . "<br>";
	$oNewFolderAccess = FolderAccess::get(1);
	echo "Get ? " . print_r($oNewFolderAccess) . "\n";
	$oNewFolderAccess = FolderAccess::getList();
	echo "GetList ? " . print_r($oNewFolderAccess) . "\n";
	$oNewFolderAccess = FolderAccess::getList("WHERE can_read = 1");
	echo "GetList ? " . print_r($oNewFolderAccess) . "\n";
    echo "</pre>";
}
?>
