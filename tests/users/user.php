<?php
/**
*
* Unit tests for User class found in /lib/users/User.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
* @package tests.users
*/

require_once("../../config/dmsDefaults.php");

if (checkSession) {
	require_once("$default->owl_fs_root/lib/users/User.inc");
	
	$oUser = & new User("test login name", "test name", "test password", 200, "ropb@jamwarehouse.com", "+27 82 422 3685", true, true, "!@#%^&*()", 3, 1);
	echo "Create ? " . ($oUser->create() ? "Yes" : "No") . "<br>";
	$oUser = & new User("test login name", "test name", "test password", 200, "ropb@jamwarehouse.com", "+27 82 422 3685", true, true, "!@#%^&*()", 3, 1);
	$oUser->create();
	$oUser = & new User("test login name", "test name", "test password", 200, "ropb@jamwarehouse.com", "+27 82 422 3685", true, true, "!@#%^&*()", 3, 1);
	$oUser->create();
	$oUser = & new User("test login name", "test name", "test password", 200, "ropb@jamwarehouse.com", "+27 82 422 3685", true, true, "!@#%^&*()", 3, 1);
	$oUser->create();
	$oUser = & new User("test login name", "test name", "test password", 200, "ropb@jamwarehouse.com", "+27 82 422 3685", true, true, "!@#%^&*()", 3, 1);
	$oUser->create();
	echo "Update ? " . ($oUser->update() ? "Yes" : "No") . "<br>";
	echo "Delete ? " . ($oUser->delete() ? "Yes" : "No") . "<br>";
	$oNewUser = User::get(1);
	echo "Get ? <pre>" . print_r($oNewUser) . "</pre>";
	$oNewUser = User::getList();
	echo "GetList ? <pre>" . print_r($oNewUser) . "</pre>";
	$oNewUser = User::getList("WHERE id > 3");
	echo "GetList ? <pre>" . print_r($oNewUser) . "</pre>";
	
}

?>
