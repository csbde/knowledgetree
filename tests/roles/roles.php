<?php

require_once("../../config/dmsDefaults.php");

/**
* @package tests.roles
*
* Unit tests for Role class found in /lib/roles/Role.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
* @package tests.roles
*/


if (checkSession) {
	require_once("$default->fileSystemRoot/lib/roles/Role.inc");
	
	$oRole = & new Role("test role",true, true);
	echo "Create ? " . ($oRole->create() ? "Yes" : "No") . "<br>";
	$oRole = & new Role("test role",true, false);
	$oRole->create();
	$oRole = & new Role("test role",false, true);
	$oRole->create();
	$oRole = & new Role("test role",false, true);
	$oRole->create();
	$oRole = & new Role("test role",true, true);
	$oRole->create();
	echo "Update ? " . ($oRole->update() ? "Yes" : "No") . "<br>";
	echo "Delete ? " . ($oRole->delete() ? "Yes" : "No") . "<br>";
	$oNewRole = Role::get(1);
	echo "Get ? <pre>" . print_r($oNewRole) . "</pre>";
	$oNewRole = Role::getList();
	echo "GetList ? <pre>" . print_r($oNewRole) . "</pre>";
	$oNewRole = Role::getList("WHERE can_read = 1");
	echo "GetList ? <pre>" . print_r($oNewRole) . "</pre>";
	
}

?>
