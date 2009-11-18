<?php
/**
 * $Id$
 *
 * Defines site mappings- actions, pages and permissions.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */ 
 
require_once(KT_LIB_DIR . '/session/SiteMap.inc');
$default->siteMap = new SiteMap(false);

// action, page, section, group with access, link text

// general pages
$default->siteMap->addPage('login', '/login.php', 'General', None, '');

// dashboard
$default->siteMap->addPage('dashboard', '/dashboard.php', 'General', Guest, 'dashboard');

// old dashboard
$default->siteMap->addPage('olddashboard', '/olddashboard.php', 'General', Guest, 'olddashboard');
// dashboard news

//pages for manage documents section
$default->siteMap->addPage('browse', '/browse.php', 'Manage Documents', Guest, _kt('browse documents'));
$default->siteMap->addPage('viewDocument', '/view.php', 'Manage Documents', Guest, _kt('View Document'), false);
$default->siteMap->addPage('editDocument', '/edit.php', 'Manage Documents', Guest, _kt('Edit Document'), false);

// pages for administration section
$default->siteMap->addDefaultPage('administration', '/admin.php', 'Administration', UnitAdmin, _kt('Administration'));

// pages for advanced search section
$default->siteMap->addDefaultPage('advancedSearch', '/search/advancedSearchBL.php', 'Advanced Search', Guest, _kt('Advanced Search'), true);
$default->siteMap->addPage('booleanSearch', '/search/booleanSearch.php', 'Boolean Search', Guest, _kt('Boolean Search'), false);

$default->siteMap->addSectionColour('Advanced Search', 'th', 'A1571B');
$default->siteMap->addSectionColour('Standard Search', 'th', 'A1571B');

// pages for prefs section
$default->siteMap->addDefaultPage('preferences', '/preferences.php', 'Preferences', User, _kt('Preferences'));

// pages for about section
$default->siteMap->addDefaultPage('aboutkt', '/about.php', 'About', Guest, _kt('About'));

// pages for Help section
$default->siteMap->addDefaultPage('help', '/presentation/lookAndFeel/knowledgeTree/help.php', 'Help', Guest, _kt('Help'));

// pages for logout section section
$default->siteMap->addDefaultPage('logout', '/presentation/logout.php', 'Logout', Guest, _kt('Logout'));

?>
