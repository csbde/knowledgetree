<?php

require_once("../../config/dmsDefaults.php");

/**
*
* Unit tests for class Subscription found in /lib/web/Subscription.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
* @package tests.web
*/


if (checkSession) {
	require_once("$default->owl_fs_root/lib/web/Subscription.inc");
	
	$oSubscription = & new Subscription(1,1);
	echo "Create ? " . ($oSubscription->create() ? "Yes" : "No") . "<br>";
	$oSubscription = & new Subscription(1,1);
	$oSubscription->create();
	$oSubscription = & new Subscription(1,1);
	$oSubscription->create();
	$oSubscription = & new Subscription(2,1);
	$oSubscription->create();
	$oSubscription = & new Subscription(1,1);
	$oSubscription->create();
	echo "Update ? " . ($oSubscription->update() ? "Yes" : "No") . "<br>";
	echo "Delete ? " . ($oSubscription->delete() ? "Yes" : "No") . "<br>";
	$oNewSubscription = Subscription::get(1);
	echo "Get ? <pre>" . print_r($oNewSubscription) . "</pre>";
	$oNewSubscription = Subscription::getList();
	echo "GetList ? <pre>" . print_r($oNewSubscription) . "</pre>";
	$oNewSubscription = Subscription::getList("WHERE user_id = 2");
	echo "GetList ? <pre>" . print_r($oNewSubscription) . "</pre>";
	
}
?>
