<?php

require_once("../../config/dmsDefaults.php");

/**
* @package tests.groups
*
* Unit tests for class GroupUnitLink in /lib/groups/GroupUnitLink.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
* @package tests.groups
*/


if (checkSession) {
	require_once("$default->fileSystemRoot/lib/groups/GroupUnitLink.inc");
	
	$oGroupUnitLink = & new GroupUnitLink(1,1);
	echo "Create ? " . ($oGroupUnitLink->create() ? "Yes" : "No") . "<br>";
	$oGroupUnitLink = & new GroupUnitLink(1,1);
	$oGroupUnitLink->create();
	$oGroupUnitLink = & new GroupUnitLink(1,1);
	$oGroupUnitLink->create();
	$oGroupUnitLink = & new GroupUnitLink(1,1);
	$oGroupUnitLink->create();
	$oGroupUnitLink = & new GroupUnitLink(1,1);
	$oGroupUnitLink->create();
	echo "Update ? " . ($oGroupUnitLink->update() ? "Yes" : "No") . "<br>";
	echo "Delete ? " . ($oGroupUnitLink->delete() ? "Yes" : "No") . "<br>";
	$oNewGroupUnitLink = GroupUnitLink::get(1);
	echo "Get ? <pre>" . print_r($oNewGroupUnitLink) . "</pre>";
	$oNewGroupUnitLink = GroupUnitLink::getList();
	echo "GetList ? <pre>" . print_r($oNewGroupUnitLink) . "</pre>";
	$oNewGroupUnitLink = GroupUnitLink::getList("id > 2");
	echo "GetList ? <pre>" . print_r($oNewGroupUnitLink) . "</pre>";
	
}
?>
