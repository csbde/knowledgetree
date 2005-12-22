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
$default->siteMap->addPage("login", "/login.php", "General", None, "");

// dashboard
$default->siteMap->addPage("dashboard", "/dashboard.php", "General", Guest, "dashboard");
// dashboard news

//pages for manage documents section
$default->siteMap->addPage("browse", "/browse.php", "Manage Documents", Guest, _("browse documents"));
$default->siteMap->addPage("viewDocument", "/view.php", "Manage Documents", Guest, _("View Document"), false);
$default->siteMap->addPage("editDocument", "/edit.php", "Manage Documents", Guest, _("Edit Document"), false);

// pages for administration section
$default->siteMap->addDefaultPage("administration", "/admin.php", "Administration", UnitAdmin, _("Administration"));

// pages for advanced search section
$default->siteMap->addDefaultPage("advancedSearch", "/search/advancedSearchBL.php", "Advanced Search", Guest, _("Advanced Search"), true);
$default->siteMap->addPage("booleanSearch", "/search/booleanSearch.php", "Boolean Search", Guest, _("Boolean Search"), false);

$default->siteMap->addSectionColour("Advanced Search", "th", "A1571B");
$default->siteMap->addSectionColour("Standard Search", "th", "A1571B");

// pages for prefs section
$default->siteMap->addDefaultPage("preferences", "/preferences.php", "Preferences", User, _("Preferences"));

// pages for Help section
$default->siteMap->addDefaultPage("help", "/presentation/lookAndFeel/knowledgeTree/help.php", "Help", Guest, _("Help"));

// pages for logout section section
$default->siteMap->addDefaultPage("logout", "/presentation/logout.php", "Logout", Guest, _("Logout"));

?>
