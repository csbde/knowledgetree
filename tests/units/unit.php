<?php
/**
* Unit test for class Unit in /lib/units
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
* @package tests.units
*
*/

require_once("../../config/dmsDefaults.php");

if (checkSession) {
	require_once("$default->owl_fs_root/lib/units/Unit.inc");
	
	$oUnit = & new Unit("test unit", 1, 2);
	echo "Create ? " . ($oUnit->create() ? "Yes" : "No") . "<br>";
	$oUnit = & new Unit("test unit", 1, 2);
	$oUnit->create();
	$oUnit = & new Unit("test unit", 1, 2);
	$oUnit->create();
	$oUnit = & new Unit("test unit", 1, 2);
	$oUnit->create();
	$oUnit = & new Unit("test unit", 1, 2);
	$oUnit->create();
	echo "Update ? " . ($oUnit->update() ? "Yes" : "No") . "<br>";
	echo "Delete ? " . ($oUnit->delete() ? "Yes" : "No") . "<br>";
	$oNewUnit = Unit::get(1);
	echo "Get ? <pre>" . print_r($oNewUnit) . "</pre>";
	$oNewUnit = Unit::getList();
	echo "GetList ? <pre>" . print_r($oNewUnit) . "</pre>";
	$oNewUnit = Unit::getList("WHERE id > 2");
	echo "GetList ? <pre>" . print_r($oNewUnit) . "</pre>";
	
}

?>
