<?php
/**
 * $Id$
 *
 * Defines site mappings- actions, pages and permissions.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */ 
 
require_once(KT_LIB_DIR . '/session/SiteMap.inc');
$default->siteMap = new SiteMap(false);

// action, page, section, group with access, link text

// general pages
$default->siteMap->addPage("login", "/login.php", "General", None, "");

// dashboard
$default->siteMap->addPage("dashboard", "/dashboard.php", "General", Guest, "dashboard");

// old dashboard
$default->siteMap->addPage("olddashboard", "/olddashboard.php", "General", Guest, "olddashboard");
// dashboard news

//pages for manage documents section
$default->siteMap->addPage("browse", "/browse.php", "Manage Documents", Guest, _kt("browse documents"));
$default->siteMap->addPage("viewDocument", "/view.php", "Manage Documents", Guest, _kt("View Document"), false);
$default->siteMap->addPage("editDocument", "/edit.php", "Manage Documents", Guest, _kt("Edit Document"), false);

// pages for administration section
$default->siteMap->addDefaultPage("administration", "/admin.php", "Administration", UnitAdmin, _kt("Administration"));

// pages for advanced search section
$default->siteMap->addDefaultPage("advancedSearch", "/search/advancedSearchBL.php", "Advanced Search", Guest, _kt("Advanced Search"), true);
$default->siteMap->addPage("booleanSearch", "/search/booleanSearch.php", "Boolean Search", Guest, _kt("Boolean Search"), false);

$default->siteMap->addSectionColour("Advanced Search", "th", "A1571B");
$default->siteMap->addSectionColour("Standard Search", "th", "A1571B");

// pages for prefs section
$default->siteMap->addDefaultPage("preferences", "/preferences.php", "Preferences", User, _kt("Preferences"));

// pages for Help section
$default->siteMap->addDefaultPage("help", "/presentation/lookAndFeel/knowledgeTree/help.php", "Help", Guest, _kt("Help"));

// pages for logout section section
$default->siteMap->addDefaultPage("logout", "/presentation/logout.php", "Logout", Guest, _kt("Logout"));

?>
