<?php

require_once("../../config/dmsDefaults.php");

/**
* Unit test for class GroupFolderApprovalLink found in /lib/groups/GroupFolderApprovalLink.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
* @package tests.groups
*
*/


if (checkSession) {
	require_once("$default->fileSystemRoot/lib/groups/GroupFolderApprovalLink.inc");
	
	$oGroupFolderApprovalLink = & new GroupFolderApprovalLink(1,2,3,4);
	echo "Create ? " . ($oGroupFolderApprovalLink->create() ? "Yes" : "No") . "<br>";
	$oGroupFolderApprovalLink = & new GroupFolderApprovalLink(1,2,3,4);
	$oGroupFolderApprovalLink->create();
	$oGroupFolderApprovalLink = & new GroupFolderApprovalLink(1,2,3,4);
	$oGroupFolderApprovalLink->create();
	$oGroupFolderApprovalLink = & new GroupFolderApprovalLink(1,2,3,4);
	$oGroupFolderApprovalLink->create();
	$oGroupFolderApprovalLink = & new GroupFolderApprovalLink(1,2,3,4);
	$oGroupFolderApprovalLink->create();
	echo "Update ? " . ($oGroupFolderApprovalLink->update() ? "Yes" : "No") . "<br>";
	echo "Delete ? " . ($oGroupFolderApprovalLink->delete() ? "Yes" : "No") . "<br>";
	$oNewGroupFolderApprovalLink = GroupFolderApprovalLink::get(1);
	echo "Get ? <pre>" . print_r($oNewGroupFolderApprovalLink) . "</pre>";
	$oNewGroupFolderApprovalLink = GroupFolderApprovalLink::getList();
	echo "GetList ? <pre>" . print_r($oNewGroupFolderApprovalLink) . "</pre>";
	$oNewGroupFolderApprovalLink = GroupFolderApprovalLink::getList("WHERE id > 3");
	echo "GetList ? <pre>" . print_r($oNewGroupFolderApprovalLink) . "</pre>";
	
}

?>
