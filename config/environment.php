<?php
/**
 * $Id$
 *
 * Defines KnowledgeTree application environment settings.
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

// ----------------------------------------------------------------
// The options in this section should automatically be detected by
// KnowledgeTree.  
// ----------------------------------------------------------------

// install path (file path)
//
// Leave commented to have it automatically detected.
// 
// $default->fileSystemRoot  = "";

// Webserver name (host name)
//
// Leave commented to have it automatically detected.
// 
// $default->serverName = "";

// Whether ssl is enabled or not
//
// Leave commented to have it automatically detected.
//
//$default->sslEnabled = false;

// Path to the web application from the root of the web site.
// If KT is at http://example.org/foo/, then rootUrl should be '/foo'
//
// Leave commented to have it automatically detected.
//
//$default->rootUrl  = "";

// ----------------------------------------------------------------
// At a minimum, you may need to change some of settings in this
// section.
// ----------------------------------------------------------------

// Change this to reflect the database you are using
// currently mysql is the only supported type
$default->dbType = "mysql";

// Database info
$default->dbHost           = "localhost";
$default->dbName           = "dms";
$default->dbUser           = "dms";
$default->dbPass           = "pass";

// ----------------------------------------------------------------
// This section is for more esoteric settings.
// ----------------------------------------------------------------

// Change this to reflect the authentication method you are using
// valid choices are: DBAuthenticator, LDAPAuthenticator
$default->authenticationClass = "DBAuthenticator";

// enable folder hiding flag
$default->folderHidingFlag = 1;

// default language
$default->defaultLanguage = "en";
$default->useAcceptLanguageHeader = true;

// hack to set org to use for dashboard greeting
$default->organisationID = 1;

// Scrolling News (true/false).
// Note: This makes use of the MARQUEE HTML tag. This tag is not fully supported in
//       all web browsers. It is generally safe for most web browsers (IE/Gecko-based).
//       Only enable this if you are sure that this will not adversely
//       effect your clients.
// $default->scrollingNews = true;
$default->scrollingNews = false; 

// If you don't require all documents to have all their generic metadata
// filled in, then set $default->genericMetaDataRequired = false;
$default->genericMetaDataRequired = true;

// ----------------------------------------------------------------
// WARNING: Settings below here may be overridden if using database
// configuration
//
// To enable database configuration, uncomment the next command:
//
//         $default->useDatabaseConfiguration = true;
//
// ----------------------------------------------------------------

// ldap settings
$default->ldapServer = "192.168.1.9";
$default->ldapRootDn = "o=Organisation";
// current supported types=iPlanet, ActiveDirectory;
$default->ldapServerType = "iPlanet";
$default->ldapDomain = "domain.com";
$default->ldapSearchUser = "searchUser@domain.com";
$default->ldapSearchPassword = "pwd";
    
// email settings
$default->emailServer = "mail.domain.com";
$default->emailFrom = "kt@domain.com";
$default->emaiFromName = "KnowledgeTree Document Management System";
$default->emailAdmin = "kt@jamwarehouse.com";
$default->emailAdminName = "DMS Administrator";
    
// directories
$default->documentRoot = $default->fileSystemRoot . "/Documents";
$default->uiDirectory = $default->fileSystemRoot . "/presentation/lookAndFeel/knowledgeTree";
     
// urls
$default->graphicsUrl = $default->rootUrl . "/graphics";
$default->uiUrl  = $default->rootUrl . "/presentation/lookAndFeel/knowledgeTree";
    
// session timeout (in seconds)
$default->sessionTimeout = 1200;
// add javascript content pane scrolling arrows
$default->contentPaneScrolling = false;

?>
