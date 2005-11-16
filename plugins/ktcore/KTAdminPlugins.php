<?php

require_once(KT_DIR . "/plugins/ktcore/KTAdminNavigation.php");

$oAdminRegistry =& KTAdminNavigationRegistry::getSingleton();

// set up the categories.
$oAdminRegistry->registerCategory("principals", "Users and Groups", " Control which users can log in, and are part of which groups and organisational units from these management panels.");
$oAdminRegistry->registerCategory("security", "Security Management", " Assign permissions to users and groups, and specify which permissions are required to interact with various parts of the Document Management System.");
$oAdminRegistry->registerCategory("storage", "Document Storage", "Manage how and where the actual documents will be stored, and work with document archives.");
$oAdminRegistry->registerCategory("documents", "Document Type Configuration", "Configure the information that needs to be collected about different kinds of documents.");
$oAdminRegistry->registerCategory("collections", "Collections", "Specify how groups of documents are displayed in browse and search mode.");



// set up the items
/* there are two ways to do this: 
 *
 *  - by url [LEGACY ONLY]
 *  - by class & path (relative to KT_LIB_DIR)
 */

//registerLocation($sName, $sClass, $sCategory, $sTitle, $sDescription, $sDispatcherFilePath = null, $sURL = null) {

// FIXME url traversal DOESN'T WORK

// users and groups
$oAdminRegistry->registerLocation("principals/admin",null,"principals", "Add/Remove Users","Add or remove users from the system.",null, "/control.php?action=userManagement")



?>