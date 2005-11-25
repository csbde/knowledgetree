<?php
/**
 * $Id$
 *
 * Defines site mappings- actions, pages and permissions.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */ 
 
require_once("$default->fileSystemRoot/lib/session/SiteMap.inc");
$default->siteMap = new SiteMap(false);

// action, page, section, group with access, link text

// general pages
$default->siteMap->addPage("login", "/presentation/login.php?loginAction=login", "General", None, "");
$default->siteMap->addPage("loginForm", "/presentation/login.php?loginAction=loginForm", "General", None, "login");

// dashboard
$default->siteMap->addPage("dashboard", "/presentation/lookAndFeel/knowledgeTree/dashboard.php", "General", Guest, "dashboard");
// dashboard news
$default->siteMap->addPage("viewNewsItem", "/presentation/lookAndFeel/knowledgeTree/dashboard/news/displayNewsItem.php", "General", Guest, "");
$default->siteMap->addPage("viewNewsImage", "/presentation/lookAndFeel/knowledgeTree/dashboard/news/displayNewsImage.php", "General", Guest, "");

//pages for manage documents section
$default->siteMap->addDefaultPage("browse", "/presentation/lookAndFeel/knowledgeTree/browse.php", "Manage Documents", Guest, _("browse documents"));
$default->siteMap->addDefaultPage("templateBrowse", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/templateDocumentBrowseBL.php", "Manage Documents", Guest, _("browse documents"));
$default->siteMap->addPage("viewDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/view.php", "Manage Documents", Guest, _("View Document"), false);
$default->siteMap->addPage("downloadDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/downloadBL.php", "Manage Documents", Guest, "Download Document", false);
$default->siteMap->addPage("deleteDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/deleteDocumentBL.php", "Manage Documents", User, "Delete document", false);
$default->siteMap->addPage("moveDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/moveDocumentBL.php", "Manage Documents", User, "Move document", false);
$default->siteMap->addPage("viewHistory", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewHistoryBL.php", "Manage Documents", User, "View Document History", false);
$default->siteMap->addPage("modifyDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/modifyBL.php", "Manage Documents", User, "Modify Document", false);
$default->siteMap->addPage("emailDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/emailBL.php", "Manage Documents", User, "Email A Document", false);
$default->siteMap->addPage("modifyDocumentGenericMetaData", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/modifyGenericMetaDataBL.php", "Manage Documents", User, "Modify Document Generic MetaData", false);
$default->siteMap->addPage("archiveDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/archiving/archiveDocumentBL.php", "Manage Documents", User, "Archive Document", false);
$default->siteMap->addPage("addDocumentArchiveSettings", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/archiving/addArchiveSettingsBL.php", "Manage Documents", User, "Add Document Archive Settings", false);
$default->siteMap->addPage("modifyDocumentArchiveSettings", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/archiving/modifyArchiveSettingsBL.php", "Manage Documents", User, "Modify Document Archive Settings", false);
$default->siteMap->addPage("requestDocumentRestore", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/archiving/requestDocumentRestoreBL.php", "Manage Documents", User, "Request Document Restoration", false);

$default->siteMap->addPage("addDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/addDocument.php", "Manage Documents", User, _("Add A Document"));
$default->siteMap->addPage("bulkUpload", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/bulkUploadBL.php", "Manage Documents", User, _("Bulk Upload"));
$default->siteMap->addPage("bulkImport", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/bulkImport.php", "Manage Documents", User, _("Bulk Import"));

// folder management
$default->siteMap->addPage("addFolder", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/addFolderBL.php", "Manage Documents", User, _("Add A Folder"));
$default->siteMap->addPage("editFolderDocType", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderDocumentTypes.php", "Manage Documents", User, "");
$default->siteMap->addPage("editFolder", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/editBL.php", "Manage Documents", User, _("Modify Folder Properties"));
$default->siteMap->addPage("editFolderPermissions", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderPermissions.php", "Manage Documents", User, _("Folder Permissions"));
$default->siteMap->addPage("copyFolderPermissions", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/copyInheritedPermissionsBL.php", "Manage Documents", User, _("Copy Inherited Folder Permissions"), false);
$default->siteMap->addPage("modifyFolderData", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/modifyFolderDataBL.php", "Manage Documents", User, "Modify Folder Data", false);
$default->siteMap->addPage("deleteFolder", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/deleteFolderBL.php", "Manage Documents", User, _("Delete Current Folder"));

// document actions
$default->siteMap->addPage("modifyDocumentTypeMetaData", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/modifySpecificMetaDataBL.php", "Manage Documents", User, "");
$default->siteMap->addPage("removeDocumentLink", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/removeDocumentLinkBL.php", "Manage Documents", User, "", false);
$default->siteMap->addPage("addDocumentLink", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/addDocumentLinkBL.php", "Manage Documents", User, "", false);

$default->siteMap->addPage("addSubscription", "/presentation/lookAndFeel/knowledgeTree/subscriptions/addSubscriptionBL.php", "Manage Documents", User, _("Add Folder Subscription"));
$default->siteMap->addPage("removeSubscription", "/presentation/lookAndFeel/knowledgeTree/subscriptions/removeSubscriptionBL.php", "Manage Documents", User, _("Remove Folder Subscription"));

// check in / check out
$default->siteMap->addPage("checkOutDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/checkOutDocumentBL.php", "Manage Documents", User, "Check Out Document", false);
$default->siteMap->addPage("checkInDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/checkInDocumentBL.php", "Manage Documents", User, "Check In Document", false);

$default->siteMap->addPage("editDocumentPermissions", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentPermissions.php", "Manage Documents", User, _("Document Permissions"));
$default->siteMap->addPage("documentWorkflow", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentWorkflow.php", "Manage Documents", User, _("Document Workflow"));

$default->siteMap->addSectionColour("Manage Documents", "td", "BDDFE0");
$default->siteMap->addSectionColour("Manage Documents", "th", "57AFAE");

// web documents
$default->siteMap->addPage("webDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/webDocumentBL.php", "Manage Documents", Guest, "View Web Document", false);

// category management
$default->siteMap->addPage("manageCategories", "/presentation/lookAndFeel/knowledgeTree/administration/docfieldmanagement/editDocFieldBL.php?fDocFieldID=1", "Manage Categories", SysAdmin, _("Manage Categories"));
$default->siteMap->addSectionColour("Manage Categories", "td", "BDDFE0");
$default->siteMap->addSectionColour("Manage Categories", "th", "57AFAE");
// document type management
$default->siteMap->addPage("manageDocumentTypes", "/control.php?action=doctype", "Manage Document Types", SysAdmin, _("Manage Document Types"));
$default->siteMap->addSectionColour("Manage Document Types", "td", "BDDFE0");
$default->siteMap->addSectionColour("Manage Document Types", "th", "57AFAE");

// pages for administration section
$default->siteMap->addDefaultPage("administration", "/presentation/lookAndFeel/knowledgeTree/administration/admin.php", "Administration", UnitAdmin, _("Administration"));

$default->siteMap->addPage("userManagement", "/presentation/lookAndFeel/knowledgeTree/administration/admin.php/principals/users", "Administration", UnitAdmin, _("User Management"), true, 1);
$default->siteMap->addPage("userPasswordManagement", "/presentation/lookAndFeel/knowledgeTree/administration/usermanagement/passwordManagementBL.php", "Administration", SysAdmin, _("User Management"), false, 1);
$default->siteMap->addPage("groupManagement", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/listGroupsBL.php", "Administration", UnitAdmin, _("Group Management"), true, 2);
$default->siteMap->addPage("unitManagement", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/listUnitsBL.php", "Administration", SysAdmin, _("Unit Management"), true, 3);
$default->siteMap->addPage("orgManagement", "/presentation/lookAndFeel/knowledgeTree/administration/orgmanagement/listOrgBL.php", "Administration", SysAdmin, _("Organisation Management"), true, 4);

$default->siteMap->addPage("doctype", "presentation/lookAndFeel/knowledgeTree/administration/doctypemanagement/documentTypes.php", "Administration", SysAdmin, _("Document Type Management"), true, 5);
$default->siteMap->addPage("docfield", "/presentation/lookAndFeel/knowledgeTree/administration/admin.php/documents/fieldmanagement", "Administration", SysAdmin, _("Document Field Management"), true, 6);

$default->siteMap->addPage("archivedDocuments", "/presentation/lookAndFeel/knowledgeTree/administration/admin.php/storage/archived", "Administration", UnitAdmin, _("Restore Archived Documents"), true, 7);
$default->siteMap->addPage("deletedDocuments", "/presentation/lookAndFeel/knowledgeTree/administration/admin.php/storage/expunge", "Administration", UnitAdmin, _("Deleted Documents"), true, 8);
$default->siteMap->addPage("doccheckoutManagement", "/presentation/lookAndFeel/knowledgeTree/administration/doccheckoutmanagement/listDocumentsBL.php", "Administration", UnitAdmin, _("Document Checkout Management"), true, 9);

$default->siteMap->addPage("newsManagement", "/presentation/lookAndFeel/knowledgeTree/administration/news/listNewsBL.php", "Administration", SysAdmin, _("Dashboard News Management"), true, 10);
$default->siteMap->addPage("linkManagement", "/presentation/lookAndFeel/knowledgeTree/administration/linkmanagement/listLinksBL.php", "Administration", SysAdmin, _("QuickLink Management"), true, 11);

$default->siteMap->addPage("roleManagement", "/presentation/lookAndFeel/knowledgeTree/administration/admin.php/security/roles", "Administration", SysAdmin, _("Role Management"), true, 12);
$default->siteMap->addPage("websiteManagement", "/presentation/lookAndFeel/knowledgeTree/administration/websitemanagement/listWebsitesBL.php", "Administration", SysAdmin, _("Website Management"), true, 13);

$default->siteMap->addPage("systemAdministration", "/presentation/lookAndFeel/knowledgeTree/administration/systemsettings/systemSettingsBL.php", "Administration", SysAdmin, _("System Settings"), true, 14);

$default->siteMap->addPage("browseAdministration", "/presentation/lookAndFeel/knowledgeTree/administration/browsemanagement/editBrowserBL.php", "Administration", SysAdmin, _("Browse Management"), true, 15);

$default->siteMap->addPage("docLinkManagement", "/presentation/lookAndFeel/knowledgeTree/administration/doclinkmanagement/listLinkTypesBL.php", "Administration", SysAdmin, _("Link Type Management"), true, 16);

$default->siteMap->addPage("manageHelp", "/presentation/lookAndFeel/knowledgeTree/administration/help/manageHelp.php", "Administration", SysAdmin, _("Help Administration"), true, 17);

$default->siteMap->addPage("managePermissions", "/presentation/lookAndFeel/knowledgeTree/administration/permissions/managePermissions.php", "Administration", SysAdmin, _("Permissions Administration"), true, 18);
$default->siteMap->addPage("manageLookupTrees", "/presentation/lookAndFeel/knowledgeTree/administration/docfieldmanagement/manageLookupTrees.php", "Administration", SysAdmin, _("Lookup Tree Administration"), true, 19);
$default->siteMap->addPage("manageWorkflows", "/presentation/lookAndFeel/knowledgeTree/administration/admin.php/documents/workflows", "Administration", SysAdmin, _("Workflow Management"), true, 20);
$default->siteMap->addPage("manageCleanup", "/presentation/lookAndFeel/knowledgeTree/administration/documentmanagement/manageCleanup.php", "Administration", SysAdmin, _("Repository Cleanup"), true, 21);
$default->siteMap->addPage("manageSavedSearches", "/presentation/lookAndFeel/knowledgeTree/administration/searches/savedSearch.php", "Administration", SysAdmin, _("Saved Searches"), true, 22);
$default->siteMap->addPage("manageDocumentConditions", "/presentation/lookAndFeel/knowledgeTree/administration/searches/conditions.php", "Administration", SysAdmin, _("Document Conditions"), true, 23);

$default->siteMap->addSectionColour("Administration", "th", "056DCE");
$default->siteMap->addSectionColour("Administration", "td", "6699FF");

// user management
$default->siteMap->addDefaultPage("editUserGroups", "/presentation/lookAndFeel/knowledgeTree/administration/usermanagement/editUserGroupsBL.php", "Administration", UnitAdmin, "Edit User Groups", false);
$default->siteMap->addPage("listUsers", "/presentation/lookAndFeel/knowledgeTree/administration/usermanagement/listUsersBL.php", "Administration", UnitAdmin, "List Users", false);
$default->siteMap->addPage("addUser", "/presentation/lookAndFeel/knowledgeTree/administration/usermanagement/addUserBL.php", "Administration", SysAdmin, "Add User", false);
$default->siteMap->addDefaultPage("editUser", "/presentation/lookAndFeel/knowledgeTree/administration/usermanagement/editUserBL.php", "Administration", UnitAdmin, _("Edit User Properties"));
$default->siteMap->addDefaultPage("removeUser", "/presentation/lookAndFeel/knowledgeTree/administration/usermanagement/removeUserBL.php", "Administration", SysAdmin, "Remove User from System", false);

// group management
$default->siteMap->addDefaultPage("editGroupUnit", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/editGroupUnitBL.php", "Administration", UnitAdmin, "editGroupUnit", false);
$default->siteMap->addPage("listGroups", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/listGroupsBL.php", "Administration", UnitAdmin, "List Groups", false);
$default->siteMap->addPage("addGroup", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/addGroupBL.php", "Administration", UnitAdmin, "Add A Group", false);
$default->siteMap->addDefaultPage("editGroup", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/editGroupBL.php", "Administration", UnitAdmin, "Edit Group Properties", false);
$default->siteMap->addDefaultPage("editGroupSuccess", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/editGroupSuccess.php", "Administration", UnitAdmin, "Updated Group Successfully", false);
$default->siteMap->addDefaultPage("removeGroup", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/removeGroupBL.php", "Administration", UnitAdmin, "Remove a Group", false);
$default->siteMap->addDefaultPage("assignGroupToUnit", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/assignGroupToUnitBL.php", "Administration", UnitAdmin, "Assign Group to Unit", false);
$default->siteMap->addDefaultPage("removeGroupFromUnit", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/removeGroupFromUnitBL.php", "Administration", UnitAdmin, "Remove Group From Unit", false);
$default->siteMap->addDefaultPage("editGroupUsers", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/editGroupUsersBL.php", "Administration", UnitAdmin, "Edit Users's Group", false);
$default->siteMap->addDefaultPage("editGroupGroups", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/editGroupGroups.php", "Administration", UnitAdmin, "Edit Users's Group", false);

// Unit management
$default->siteMap->addPage("listUnits", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/listUnitsBL.php", "Administration", SysAdmin, "List Units", false);
$default->siteMap->addDefaultPage("editUnitOrg", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/editUnitOrgBL.php", "Administration", SysAdmin, "Edit Unit Organisations", false);
$default->siteMap->addDefaultPage("addUnitToOrg", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/addUnitToOrgBL.php", "Administration", SysAdmin, "Add Unit to Organisations", false);
$default->siteMap->addDefaultPage("removeUnitFromOrg", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/removeUnitFromOrgBL.php", "Administration", SysAdmin, "Remove Unit from Organisations", false);
$default->siteMap->addPage("addUnit", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/addUnitBL.php", "Administration", SysAdmin, "Add A Unit", false);
$default->siteMap->addDefaultPage("editUnit", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/editUnitBL.php", "Administration", SysAdmin, "Edit Unit Properties", false);
$default->siteMap->addPage("addUnitSuccess", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/addUnitSuccess.php", "Administration", SysAdmin, "Unit added Successfully", false);
$default->siteMap->addDefaultPage("removeUnit", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/removeUnitBL.php", "Administration", SysAdmin, "Remove a Unit", false);

// Organisation management
$default->siteMap->addPage("listOrg", "/presentation/lookAndFeel/knowledgeTree/administration/orgmanagement/listOrgBL.php", "Administration", SysAdmin, "List Organisations", false);
$default->siteMap->addPage("addOrg", "/presentation/lookAndFeel/knowledgeTree/administration/orgmanagement/addOrgBL.php", "Administration", SysAdmin, "Add An Organisation", false);
$default->siteMap->addDefaultPage("editOrg", "/presentation/lookAndFeel/knowledgeTree/administration/orgmanagement/editOrgBL.php", "Administration", SysAdmin, "Edit Organisation Properties", false);
$default->siteMap->addPage("addOrgSuccess", "/presentation/lookAndFeel/knowledgeTree/administration/orgmanagement/addOrgSuccess.php", "Administration", SysAdmin, "Organisation added Successfully", false);
$default->siteMap->addDefaultPage("removeOrg", "/presentation/lookAndFeel/knowledgeTree/administration/orgmanagement/removeOrgBL.php", "Administration", SysAdmin, "Remove an Organisation", false);

// deleted documents
$default->siteMap->addPage("expungeDeletedDocuments", "/presentation/lookAndFeel/knowledgeTree/administration/documentmanagement/expungeDeletedDocumentsBL.php", "Administration", UnitAdmin, "Expunge Deleted Documents", false);
$default->siteMap->addPage("restoreDeletedDocument", "/presentation/lookAndFeel/knowledgeTree/administration/documentmanagement/restoreDeletedDocumentBL.php", "Administration", UnitAdmin, "Restore Deleted Document", false);

// document checkout management
$default->siteMap->addPage("listDocs", "/presentation/lookAndFeel/knowledgeTree/administration/doccheckoutmanagement/listDocumentsBL.php", "Administration", UnitAdmin, "List Documents", false);
$default->siteMap->addPage("editDocCheckout", "/presentation/lookAndFeel/knowledgeTree/administration/doccheckoutmanagement/editDocCheckoutBL.php", "Administration", UnitAdmin, "Edit Document Checkout", false);

// news management
$default->siteMap->addPage("listNews", "/presentation/lookAndFeel/knowledgeTree/administration/news/listNewsBL.php", "Administration", SysAdmin, "List News Items", false);
$default->siteMap->addPage("addNews", "/presentation/lookAndFeel/knowledgeTree/administration/news/addNewsBL.php", "Administration", SysAdmin, "Add A News Item", false);
$default->siteMap->addPage("editNews", "/presentation/lookAndFeel/knowledgeTree/administration/news/editNewsBL.php", "Administration", SysAdmin, "", false);
$default->siteMap->addPage("previewNews", "/presentation/lookAndFeel/knowledgeTree/administration/news/previewNewsBL.php", "Administration", SysAdmin, "", false);
$default->siteMap->addPage("removeNews", "/presentation/lookAndFeel/knowledgeTree/administration/news/removeNewsBL.php", "Administration", SysAdmin, "", false);

// link management
$default->siteMap->addPage("listLinks", "/presentation/lookAndFeel/knowledgeTree/administration/linkmanagement/listLinksBL.php", "Administration", SysAdmin, "List Links", false);
$default->siteMap->addPage("addLink", "/presentation/lookAndFeel/knowledgeTree/administration/linkmanagement/addLinkBL.php", "Administration", SysAdmin, "Add A Link", false);
$default->siteMap->addPage("addLinkSuccess", "/presentation/lookAndFeel/knowledgeTree/administration/linkmanagement/addLinkSuccess.php", "Administration", SysAdmin, "Add A Link Success ", false);
$default->siteMap->addDefaultPage("editLink", "/presentation/lookAndFeel/knowledgeTree/administration/linkmanagement/editLinkBL.php", "Administration", SysAdmin, "Edit Link Properties", false);
$default->siteMap->addDefaultPage("removeLink", "/presentation/lookAndFeel/knowledgeTree/administration/linkmanagement/removeLinkBL.php", "Administration", SysAdmin, "Remove a Link", false);

// rolemanagement
$default->siteMap->addPage("listRoles", "/presentation/lookAndFeel/knowledgeTree/administration/rolemanagement/listRolesBL.php", "Administration", SysAdmin, "List Roles", false);
$default->siteMap->addPage("addRole", "/presentation/lookAndFeel/knowledgeTree/administration/rolemanagement/addRoleBL.php", "Administration", SysAdmin, "Add New Role", false);
$default->siteMap->addDefaultPage("editRole", "/presentation/lookAndFeel/knowledgeTree/administration/rolemanagement/editRoleBL.php", "Administration", SysAdmin, "Edit Role Properties", false);
$default->siteMap->addDefaultPage("editRoleSuccess", "/presentation/lookAndFeel/knowledgeTree/administration/rolemanagement/editRoleSuccess.php", "Administration", SysAdmin, "Edit Role Properties", false);
$default->siteMap->addDefaultPage("removeRole", "/presentation/lookAndFeel/knowledgeTree/administration/rolemanagement/removeRoleBL.php", "Administration", SysAdmin, "Remove a Role", false);

//website management
$default->siteMap->addPage("listWebsites", "/presentation/lookAndFeel/knowledgeTree/administration/websitemanagement/listWebsitesBL.php", "Administration", SysAdmin, "List Websites", false);
$default->siteMap->addPage("addWebsite", "/presentation/lookAndFeel/knowledgeTree/administration/websitemanagement/addWebsiteBL.php", "Administration", SysAdmin, "Add a Website", false);
$default->siteMap->addPage("addWebsiteSuccess", "/presentation/lookAndFeel/knowledgeTree/administration/websitemanagement/addWebsiteSuccess.php", "Administration", SysAdmin, "Add A Website Success ",false);
$default->siteMap->addDefaultPage("editWebSite", "/presentation/lookAndFeel/knowledgeTree/administration/websitemanagement/editWebsiteBL.php", "Administration", SysAdmin, "Edit Website", false);
$default->siteMap->addDefaultPage("removeWebSite", "/presentation/lookAndFeel/knowledgeTree/administration/websitemanagement/removeWebsiteBL.php", "Administration", SysAdmin, "Remove a Website", false);

// pages for subscriptions section
$default->siteMap->addDefaultPage("manageSubscriptions", "/presentation/lookAndFeel/knowledgeTree/subscriptions/manageSubscriptionsBL.php", "Subscriptions", User, _("Manage Subscriptions"));
$default->siteMap->addPage("viewAlert", "/presentation/lookAndFeel/knowledgeTree/subscriptions/viewAlertBL.php", "Subscriptions", User, "Subscriptions", false);

$default->siteMap->addSectionColour("Subscriptions", "th", "FFC602");

// pages for advanced search section
$default->siteMap->addDefaultPage("advancedSearch", "/presentation/lookAndFeel/knowledgeTree/search/advancedSearchBL.php", "Advanced Search", Guest, _("Advanced Search"), true);
$default->siteMap->addPage("standardSearch", "/presentation/lookAndFeel/knowledgeTree/search/standardSearchBL.php", "Standard Search", Guest, _("Standard Search"), false);
$default->siteMap->addPage("booleanSearch", "/presentation/lookAndFeel/knowledgeTree/search/booleanSearch.php", "Boolean Search", Guest, _("Boolean Search"), false);

$default->siteMap->addSectionColour("Advanced Search", "th", "A1571B");
$default->siteMap->addSectionColour("Standard Search", "th", "A1571B");

// pages for prefs section
$default->siteMap->addDefaultPage("preferences", "/presentation/lookAndFeel/knowledgeTree/preferences/editUserPrefsBL.php", "Preferences", User, _("Preferences"));
$default->siteMap->addPage("editPrefsSuccess", "/presentation/lookAndFeel/knowledgeTree/preferences/editPrefsSuccess.php", "Preferences", User, "Preferences", false);
$default->siteMap->addPage("changeUserPassword", "/presentation/lookAndFeel/knowledgeTree/preferences/passwordManagementBL.php", "Preferences", User, "Preferences", false);
$default->siteMap->addSectionColour("Preferences", "th", "F87308");
$default->siteMap->addSectionColour("Preferences", "td", "FEE3CE");

// pages for Help section
$default->siteMap->addDefaultPage("help", "/presentation/lookAndFeel/knowledgeTree/help.php", "Help", Guest, _("Help"));

// pages for logout section section
$default->siteMap->addDefaultPage("logout", "/presentation/logout.php", "Logout", Guest, _("Logout"));

// pages for discussion threads
$default->siteMap->addDefaultPage("viewDiscussion", "/presentation/lookAndFeel/knowledgeTree/discussions/viewDiscussionBL.php", "Discussion Threads", User, false); 
$default->siteMap->addDefaultPage("viewComment", "/presentation/lookAndFeel/knowledgeTree/discussions/viewCommentBL.php", "Discussion Threads", User, false);
$default->siteMap->addDefaultPage("addComment", "/presentation/lookAndFeel/knowledgeTree/discussions/addCommentBL.php", "Discussion Threads", User, false); 

$default->siteMap->addSectionColour("Discussion Threads", "td", "BDDFE0");
$default->siteMap->addSectionColour("Discussion Threads", "th", "57AFAE");

// Document link management
$default->siteMap->addPage("listLinkTypes", "/presentation/lookAndFeel/knowledgeTree/administration/doclinkmanagement/listLinkTypesBL.php", "Administration", SysAdmin, "List Link Types", false);
$default->siteMap->addPage("addLinkType", "/presentation/lookAndFeel/knowledgeTree/administration/doclinkmanagement/addLinkTypeBL.php", "Administration", SysAdmin, "Add A Link Type", false);
$default->siteMap->addPage("addLinkTypeSuccess", "/presentation/lookAndFeel/knowledgeTree/administration/doclinkmanagement/addLinkTypeSuccess.php", "Administration", SysAdmin, "Add A Link Type Success ", false);
$default->siteMap->addDefaultPage("editLinkType", "/presentation/lookAndFeel/knowledgeTree/administration/doclinkmanagement/editLinkTypeBL.php", "Administration", SysAdmin, _("Edit Link Type Properties"), false);
$default->siteMap->addDefaultPage("removeLinkType", "/presentation/lookAndFeel/knowledgeTree/administration/doclinkmanagement/removeLinkTypeBL.php", "Administration", SysAdmin, _("Remove a Link Type"), false);

?>
