<?php

/**
 * $Id$
 *
 * Stores the defaults for the DMS application
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 */

// include the environment settings
require_once("environment.php");

//****************************************************
// Pick your language system default language
// now each user can pick his language
// if they are allowed by the admin to change their
// preferences.
//****************************************************
// b5
// Chinese
// Danish
// Deutsch
// Dutch
// English
// Francais
// Hungarian
// Italian
// NewEnglish <-  NEW LOOK, English will be obsoleted in a future version
// Norwegian
// Portuguese
// Spanish

// Table mappings
// session information
$default->owl_sessions_table = "active_sessions";
// document type fields
$default->owl_fields_table = "document_fields";
// links document
$default->owl_document_fields_table = "document_fields_link";
// meta data value lookup table
$default->owl_document_fields_lookup_tables = "document_fields_lookup";
// document subscriptions
$default->owl_document_subscriptions_table = "document_subscriptions";
// document transaction types
$default->owl_transaction_types_table = "document_transaction_types_lookup";
// document transactions
$default->owl_document_transactions_table = "document_transactions";
// links document types to document type fields
$default->owl_document_type_fields_table = "document_type_fields_link";
// document type information
$default->owl_document_types_table = "document_types_lookup";
// links documents to words
$default->owl_document_words_table = "document_words_link";
// stores documents
$default->owl_documents_table = "documents";
// stores folder subscriptions
$default->owl_folder_subscriptions_table = "folder_subscriptions";
// stores folders
$default->owl_folders_table = "folders";
// links folders to users (and roles) for approval collaboration
$default->owl_folders_user_roles_table = "folders_users_roles_link";
// stores approval collaboration information- approval roles mapped to folders with order
$default->owl_groups_folders_approval_table = "groups_folders_approval_link";
// links groups to folders
$default->owl_groups_folders_table = "groups_folders_link";
// stores group information
$default->owl_groups_table = "groups_lookup";
// links groups to units
$default->owl_groups_units_table = "groups_units_link";
// links
$default->owl_links_table = "links";
// Table with mime info
$default->owl_mime_table = "mime_types";
// organisation information
$default->owl_organisations_table = "organisations_lookup";
// stores role information (name and access)
$default->owl_roles_table = "roles";
// sitemap access classes
$default->owl_site_access_table = "site_access_lookup";
// sitemap sections
$default->owl_site_sections_table = "site_sections_lookup";
// sitemap definition
$default->owl_sitemap_table = "sitemap";
// stores document subscription information
$default->owl_subscriptions_table = "subscriptions";
// stores deleted files
$default->owl_sys_deleted_table = "sys_deleted";
// stores default system settings
$default->owl_system_settings_table = "system_settings";
// Table with unit information
$default->owl_units_table = "units_lookup";
// Table with unit organisation link tables
$default->owl_units_organisations_table = "units_organisations_link";
// Table with user info
$default->owl_users_table = "users";
// links groups to users
$default->owl_users_groups_table = "users_groups_link";
// Table with web documents info for web publishing
$default->owl_web_documents_table = "web_documents";
// Table with web documents info for web publishing
$default->owl_web_documents_status_table = "web_documents_status_lookup";
// stores websites for web publishing
$default->owl_web_sites_table = "web_sites";
// stores indexed words
$default->owl_words_lookup_table = "words_lookup";

// Change this to reflect the authentication method you are using
//require_once("$default->fileSystemRoot/lib/LDAPAuthenticator.inc");
//require_once("$default->fileSystemRoot/lib/Authenticator.inc");
$default->authenticationClass = "DBAuthenticator";
/*
echo "<pre>";
print_r($default);
echo "</pre>";
exit;
*/
require_once("$default->fileSystemRoot/lib/authentication/$default->authenticationClass.inc");

// logo file that must reside inside lang/graphics directory
$default->logo = "kt.jpg";

$default->version = "owl-dms 1.0 @build-date@";
$default->phpversion = "4.0.2";

// Change this to reflect the database you are using
//require("$default->fileSystemRoot/phplib/db_pgsql.inc");
require_once("$default->fileSystemRoot/phplib/db_mysql.inc");

// single db instantiation
require_once("$default->fileSystemRoot/lib/database/db.inc");
$default->db = new Database();

// define site mappings
require_once("$default->fileSystemRoot/lib/session/SiteMap.inc");
$default->siteMap = new SiteMap(false);

// action, page, section, group with access, link text

// general pages
$default->siteMap->addPage("login", "/presentation/login.php?loginAction=login", "General", None, "");
$default->siteMap->addPage("loginForm", "/presentation/login.php?loginAction=loginForm", "General", None, "login");
$default->siteMap->addPage("dashboard", "/presentation/dashboardBL.php", "General", Guest, "dashboard");

//pages for manage documents section
$default->siteMap->addDefaultPage("browse", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/browseBL.php", "Manage Documents", Guest, "browse documents");
$default->siteMap->addPage("viewDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php", "Manage Documents", Guest, "View Document", false);
$default->siteMap->addPage("deleteDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/deleteDocumentBL.php", "Manage Documents", User, "Delete document", false);
$default->siteMap->addPage("viewHistory", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewHistoryBL.php", "Manage Documents", User, "View Document History", false);
$default->siteMap->addPage("modifyDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/modifyBL.php", "Manage Documents", User, "Modify Document", false);
$default->siteMap->addPage("emailDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/emailBL.php", "Manage Documents", User, "Email A Document", false);

$default->siteMap->addPage("addFolder", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/addFolderBL.php", "Manage Documents", User, "Add A Folder");
$default->siteMap->addPage("editFolder", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/editBL.php", "Manage Documents", UnitAdmin, "Modify Folder Properties");
$default->siteMap->addPage("deleteFolder", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/deleteFolderBL.php", "Manage Documents", UnitAdmin, "Delete A Folder");
$default->siteMap->addPage("moveFolder", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/moveFolder.php", "Manage Documents", UnitAdmin, "Move A Folder", false);

$default->siteMap->addPage("addDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/addDocumentBL.php", "Manage Documents", User, "Add A Document");
$default->siteMap->addPage("modifyDocumentRouting", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/collaborationBL.php", "Manage Documents", User, "");
$default->siteMap->addPage("modifyFolderCollaboration", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/collaborationBL.php", "Manage Documents", User, "", false);
$default->siteMap->addPage("addFolderCollaboration", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/addFolderCollaborationBL.php", "Manage Documents", User, "", false);
$default->siteMap->addPage("deleteFolderCollaboration", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/deleteFolderCollaborationBL.php", "Manage Documents", User, "", false);

// pages for administration section
$default->siteMap->addDefaultPage("administration", "/presentation/lookAndFeel/knowledgeTree/administration/admin.php", "Administration", UnitAdmin, "Administration");
$default->siteMap->addPage("userManagement", "/presentation/lookAndFeel/knowledgeTree/administration/admin.php?sectionName=userAdministration", "Administration", UnitAdmin, "User Management");
$default->siteMap->addPage("groupManagement", "/presentation/lookAndFeel/knowledgeTree/administration/admin.php?sectionName=groupAdministration", "Administration", UnitAdmin, "Group Management");
$default->siteMap->addPage("unitManagement", "/presentation/lookAndFeel/knowledgeTree/administration/admin.php?sectionName=unitAdministration", "Administration", SysAdmin, "Unit Management");
$default->siteMap->addPage("orgManagement", "/presentation/lookAndFeel/knowledgeTree/administration/admin.php?sectionName=orgAdministration", "Administration", SysAdmin, "Organisation Management");
$default->siteMap->addPage("roleManagement", "/presentation/lookAndFeel/knowledgeTree/administration/admin.php?sectionName=roleAdministration", "Administration", SysAdmin, "Role Management");
$default->siteMap->addPage("systemAdministration", "/presentation/lookAndFeel/knowledgeTree/administration/systemsettings/systemSettingsBL.php", "Administration", SysAdmin, "System Settings");

/////////// pages for administration section
//$default->siteMap->addDefaultPage("unitadministration", "/presentation/unitadmin.php", "UnitAdministration", UnitAdmin, "groAdministration");
//group management
$default->siteMap->addPage("addGroup", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/addGroupBL.php", "groupAdministration", UnitAdmin, "Add A Group");
$default->siteMap->addPage("editGroup", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/editGroupBL.php", "groupAdministration", UnitAdmin, "Edit Group Properties");
$default->siteMap->addPage("editGroupSuccess", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/editGroupSuccess.php", "groupAdministration", UnitAdmin, "Updated Group Successfully",false);
$default->siteMap->addPage("removeGroup", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/removeGroupBL.php", "groupAdministration", UnitAdmin, "Remove a Group");
$default->siteMap->addPage("assignGroupToUnit", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/assignGroupToUnitBL.php", "groupAdministration", UnitAdmin, "Assign Group to Unit");
$default->siteMap->addPage("removeGroupFromUnit", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/removeGroupFromUnitBL.php", "groupAdministration", UnitAdmin, "Remove Group From Unit");

//Unit management
$default->siteMap->addPage("addUnit", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/addUnitBL.php", "unitAdministration", SysAdmin, "Add A Unit");
$default->siteMap->addPage("editUnit", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/editUnitBL.php", "unitAdministration", SysAdmin, "Edit Unit Properties");
$default->siteMap->addPage("addUnitSuccess", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/addUnitSuccess.php", "unitAdministration", SysAdmin, "Unit added Successfully",false);
$default->siteMap->addPage("removeUnit", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/removeUnitBL.php", "unitAdministration", SysAdmin, "Remove a Unit");
//$default->siteMap->addPage("assignGroupToUnit", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/assignGroupToUnitBL.php", "groupAdministration", UnitAdmin, "Assign Group to Unit");
//$default->siteMap->addPage("removeGroupFromUnit", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/removeGroupFromUnitBL.php", "groupAdministration", UnitAdmin, "Remove Group From Unit");

//Organisation management
$default->siteMap->addPage("addOrg", "/presentation/lookAndFeel/knowledgeTree/administration/orgmanagement/addOrgBL.php", "orgAdministration", SysAdmin, "Add An Organisation");
$default->siteMap->addPage("editOrg", "/presentation/lookAndFeel/knowledgeTree/administration/orgmanagement/editOrgBL.php", "orgAdministration", SysAdmin, "Edit Organisation Properties");
$default->siteMap->addPage("addOrgSuccess", "/presentation/lookAndFeel/knowledgeTree/administration/orgmanagement/addOrgSuccess.php", "orgAdministration", SysAdmin, "Organisation added Successfully",false);
$default->siteMap->addPage("removeOrg", "/presentation/lookAndFeel/knowledgeTree/administration/orgmanagement/removeOrgBL.php", "orgAdministration", SysAdmin, "Remove an Organisation");

//user management
$default->siteMap->addPage("addUser", "/tests/groups/adduser.php", "UserAdministration", UnitAdmin, "Add User to System");
$default->siteMap->addPage("editUser", "/tests/groups/adduser.php", "UserAdministration", UnitAdmin, "Edit User Properties");
$default->siteMap->addPage("removeUser", "/tests/groups/adduser.php", "UserAdministration", UnitAdmin, "Remove User from System");
$default->siteMap->addPage("addUsersToGroup", "/tests/groups/adduser.php", "UserAdministration", UnitAdmin, "Add User to A Group");

//rolemanagement
$default->siteMap->addPage("addRole", "/presentation/lookAndFeel/knowledgeTree/administration/rolemanagement/addRoleBL.php", "roleAdministration", SysAdmin, "Add New Role");
$default->siteMap->addPage("editRole", "/presentation/lookAndFeel/knowledgeTree/administration/rolemanagement/editRoleBL.php", "roleAdministration", SysAdmin, "Edit Role Properties");
$default->siteMap->addPage("editRoleSuccess", "/presentation/lookAndFeel/knowledgeTree/administration/rolemanagement/editRoleSuccess.php", "roleAdministration", SysAdmin, "Edit Role Properties", false);
$default->siteMap->addPage("removeRole", "/presentation/lookAndFeel/knowledgeTree/administration/rolemanagement/removeRoleBL.php", "roleAdministration", SysAdmin, "Remove a Role");

/////// pages for subscriptions section
$default->siteMap->addDefaultPage("subscriptions", "/subscriptions.php", "Subscriptions", Guest, "SubScriptions", false);
$default->siteMap->addDefaultPage("viewAlert", "/presentation/lookAndFeel/knowledgeTree/subscriptions/viewAlertBL.php", "Subscriptions", User, "Subscriptions", false);

// pages for advanced search section
// $default->siteMap->addDefaultPage("advancedSearch", "/email.php", "Advanced Search", "Anonymous", "Advanced Search");
$default->siteMap->addDefaultPage("advancedSearch", "/search.php", "Advanced Search", Guest, "Advanced Search", false);

// pages for prefs section
//$default->siteMap->addDefaultPage("preferences", "/presentation/lookAndFeel/knowledgeTree/Help/emailHelp.php", "Preferences", User, "Preferences",false);
$default->siteMap->addPage("viewPreferences", "/preferences.php", "Preferences", User, "View Preferences", false);
$default->siteMap->addPage("editPreferences", "/preferences.php", "Preferences", User, "Edit Preferences", false);

// pages for Help section
$default->siteMap->addDefaultPage("help", "/presentation/lookAndFeel/knowledgeTree/Help/emailHelp.php", "Help", Guest, "Help");

// pages for logout section section
$default->siteMap->addDefaultPage("logout", "/presentation/logout.php", "Logout", Guest, "Logout");

// test pages
$default->siteMap->addPage("scratchPad", "/tests/scratchPad.php", "Tests", Guest, "scratch", false);
$default->siteMap->addPage("sitemap", "/tests/session/SiteMap.php", "Tests", Guest, "sitemap", false);
$default->siteMap->addPage("documentBrowserTest", "/tests/documentmanagement/DocumentBrowser.php", "Tests", Guest, "test the document browser", false);
$default->siteMap->addPage("scroll", "/tests/scroll/textScrollTest.php", "Tests", Guest, "test scrolling", false);
$default->siteMap->addPage("subTest", "/tests/subscriptions/subscription.php", "Tests", Guest, "subscription unit test", false);
$default->siteMap->addPage("subManager", "/tests/subscriptions/subscriptionManager.php", "Tests", Guest, "manage subscription unit test", false);
$default->siteMap->addPage("subEngine", "/tests/subscriptions/subscriptionEngine.php", "Tests", Guest, "subscription firing unit test", false);
$default->siteMap->addPage("auth", "/tests/authentication/authentication.php", "Tests", Guest, "authentication unit test", false);

// default requires
require_once("$default->fileSystemRoot/phpmailer/class.phpmailer.php");
require_once("$default->fileSystemRoot/lib/session/Session.inc");
require_once("$default->fileSystemRoot/lib/session/control.inc");
require_once("$default->fileSystemRoot/phpSniff/phpSniff.class.php");

// instantiate phpsniffer
$default->phpSniff = new phpSniff($_SERVER["HTTP_USER_AGENT"]);

require_once("$default->fileSystemRoot/lib/Log.inc");
$default->log = new Log($default->fileSystemRoot . "/log.txt", INFO);

for ($i=0; $i<count($aSettings); $i++) {
    $default->log->debug($aSettings[$i] . "=" . $default->$aSettings[$i]);
}

// import request variables and setup language
require_once("$default->fileSystemRoot/lib/dms.inc");
?>
