<?php
/**
* @package tests.groups
*
* Unit tests for class GroupUserLink in /lib/groups/GroupUserLink.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
*/

require_once("../../config/dmsDefaults.php");

if (checkSession) {
	require_once("$default->owl_fs_root/lib/groups/GroupUserLink.inc");
	
	$oGroupUserLink = & new GroupUserLink(1,1);	
	echo "Create ? " . ($oGroupUserLink->create() ? "Yes" : "No" . $_SESSION["errorMessage"]) . "<br>";
	$oGroupUserLink = & new GroupUserLink(1,1);
	$oGroupUserLink->create();
	$oGroupUserLink = & new GroupUserLink(1,1);
	$oGroupUserLink->create();
	$oGroupUserLink = & new GroupUserLink(1,1);
	$oGroupUserLink->create();
	$oGroupUserLink = & new GroupUserLink(1,1);
	$oGroupUserLink->create();
	echo "Update ? " . ($oGroupUserLink->update() ? "Yes" : "No") . "<br>";
	echo "Delete ? " . ($oGroupUserLink->delete() ? "Yes" : "No") . "<br>";
	$oNewGroupUserLink = GroupUserLink::get(1);
	echo "Get ? <pre>" . print_r($oNewGroupUserLink) . "</pre>";
	$oNewGroupUserLink = GroupUserLink::getList();
	echo "GetList ? <pre>" . print_r($oNewGroupUserLink) . "</pre>";
	$oNewGroupUserLink = GroupUserLink::getList("WHERE id > 2");
	echo "GetList ? <pre>" . print_r($oNewGroupUserLink) . "</pre>";
	
}
?>
