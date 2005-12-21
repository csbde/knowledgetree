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

//pages for manage documents section
$default->siteMap->addDefaultPage("browse", "/presentation/lookAndFeel/knowledgeTree/browse.php", "Manage Documents", Guest, _("browse documents"));
$default->siteMap->addPage("viewDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/view.php", "Manage Documents", Guest, _("View Document"), false);
$default->siteMap->addPage("editDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/editDocument.php", "Manage Documents", Guest, _("Edit Document"), false);

$default->siteMap->addPage("addDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/addDocument.php", "Manage Documents", User, _("Add A Document"));
$default->siteMap->addPage("bulkImport", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/bulkImport.php", "Manage Documents", User, _("Bulk Import"));

// folder management
$default->siteMap->addPage("editFolderDocType", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderDocumentTypes.php", "Manage Documents", User, "");
$default->siteMap->addPage("editFolderPermissions", "/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderPermissions.php", "Manage Documents", User, _("Folder Permissions"));

$default->siteMap->addPage("editDocumentPermissions", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentPermissions.php", "Manage Documents", User, _("Document Permissions"));
$default->siteMap->addPage("documentWorkflow", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentWorkflow.php", "Manage Documents", User, _("Document Workflow"));

$default->siteMap->addSectionColour("Manage Documents", "td", "BDDFE0");
$default->siteMap->addSectionColour("Manage Documents", "th", "57AFAE");

// pages for administration section
$default->siteMap->addDefaultPage("administration", "/admin.php", "Administration", UnitAdmin, _("Administration"));

$default->siteMap->addPage("userManagement", "/admin.php/principals/users", "Administration", UnitAdmin, _("User Management"), true, 1);
$default->siteMap->addPage("userPasswordManagement", "/presentation/lookAndFeel/knowledgeTree/administration/usermanagement/passwordManagementBL.php", "Administration", SysAdmin, _("User Management"), false, 1);
$default->siteMap->addPage("groupManagement", "/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/listGroupsBL.php", "Administration", UnitAdmin, _("Group Management"), true, 2);
$default->siteMap->addPage("unitManagement", "/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/listUnitsBL.php", "Administration", SysAdmin, _("Unit Management"), true, 3);
$default->siteMap->addPage("orgManagement", "/presentation/lookAndFeel/knowledgeTree/administration/orgmanagement/listOrgBL.php", "Administration", SysAdmin, _("Organisation Management"), true, 4);

$default->siteMap->addPage("doctype", "presentation/lookAndFeel/knowledgeTree/administration/doctypemanagement/documentTypes.php", "Administration", SysAdmin, _("Document Type Management"), true, 5);
$default->siteMap->addPage("docfield", "/admin.php/documents/fieldmanagement", "Administration", SysAdmin, _("Document Field Management"), true, 6);

$default->siteMap->addPage("archivedDocuments", "/admin.php/storage/archived", "Administration", UnitAdmin, _("Restore Archived Documents"), true, 7);
$default->siteMap->addPage("deletedDocuments", "/admin.php/storage/expunge", "Administration", UnitAdmin, _("Deleted Documents"), true, 8);
$default->siteMap->addPage("doccheckoutManagement", "/presentation/lookAndFeel/knowledgeTree/administration/doccheckoutmanagement/listDocumentsBL.php", "Administration", UnitAdmin, _("Document Checkout Management"), true, 9);

$default->siteMap->addPage("newsManagement", "/presentation/lookAndFeel/knowledgeTree/administration/news/listNewsBL.php", "Administration", SysAdmin, _("Dashboard News Management"), true, 10);
$default->siteMap->addPage("linkManagement", "/presentation/lookAndFeel/knowledgeTree/administration/linkmanagement/listLinksBL.php", "Administration", SysAdmin, _("QuickLink Management"), true, 11);

$default->siteMap->addPage("roleManagement", "/admin.php/security/roles", "Administration", SysAdmin, _("Role Management"), true, 12);
$default->siteMap->addPage("websiteManagement", "/presentation/lookAndFeel/knowledgeTree/administration/websitemanagement/listWebsitesBL.php", "Administration", SysAdmin, _("Website Management"), true, 13);

$default->siteMap->addPage("systemAdministration", "/presentation/lookAndFeel/knowledgeTree/administration/systemsettings/systemSettingsBL.php", "Administration", SysAdmin, _("System Settings"), true, 14);

$default->siteMap->addPage("browseAdministration", "/presentation/lookAndFeel/knowledgeTree/administration/browsemanagement/editBrowserBL.php", "Administration", SysAdmin, _("Browse Management"), true, 15);

$default->siteMap->addPage("docLinkManagement", "/presentation/lookAndFeel/knowledgeTree/administration/doclinkmanagement/listLinkTypesBL.php", "Administration", SysAdmin, _("Link Type Management"), true, 16);

$default->siteMap->addPage("manageHelp", "/presentation/lookAndFeel/knowledgeTree/administration/help/manageHelp.php", "Administration", SysAdmin, _("Help Administration"), true, 17);

$default->siteMap->addPage("managePermissions", "/presentation/lookAndFeel/knowledgeTree/administration/permissions/managePermissions.php", "Administration", SysAdmin, _("Permissions Administration"), true, 18);
$default->siteMap->addPage("manageLookupTrees", "/presentation/lookAndFeel/knowledgeTree/administration/docfieldmanagement/manageLookupTrees.php", "Administration", SysAdmin, _("Lookup Tree Administration"), true, 19);
$default->siteMap->addPage("manageWorkflows", "/admin.php/documents/workflows", "Administration", SysAdmin, _("Workflow Management"), true, 20);
$default->siteMap->addPage("manageCleanup", "/presentation/lookAndFeel/knowledgeTree/administration/documentmanagement/manageCleanup.php", "Administration", SysAdmin, _("Repository Cleanup"), true, 21);
$default->siteMap->addPage("manageSavedSearches", "/presentation/lookAndFeel/knowledgeTree/administration/searches/savedSearch.php", "Administration", SysAdmin, _("Saved Searches"), true, 22);
$default->siteMap->addPage("manageDocumentConditions", "/presentation/lookAndFeel/knowledgeTree/administration/searches/conditions.php", "Administration", SysAdmin, _("Document Conditions"), true, 23);

$default->siteMap->addSectionColour("Administration", "th", "056DCE");
$default->siteMap->addSectionColour("Administration", "td", "6699FF");

// pages for advanced search section
$default->siteMap->addDefaultPage("advancedSearch", "/presentation/lookAndFeel/knowledgeTree/search/advancedSearchBL.php", "Advanced Search", Guest, _("Advanced Search"), true);
$default->siteMap->addPage("booleanSearch", "/presentation/lookAndFeel/knowledgeTree/search/booleanSearch.php", "Boolean Search", Guest, _("Boolean Search"), false);

$default->siteMap->addSectionColour("Advanced Search", "th", "A1571B");
$default->siteMap->addSectionColour("Standard Search", "th", "A1571B");

// pages for prefs section
$default->siteMap->addDefaultPage("preferences", "/preferences.php", "Preferences", User, _("Preferences"));

// pages for Help section
$default->siteMap->addDefaultPage("help", "/presentation/lookAndFeel/knowledgeTree/help.php", "Help", Guest, _("Help"));

// pages for logout section section
$default->siteMap->addDefaultPage("logout", "/presentation/logout.php", "Logout", Guest, _("Logout"));

?>
