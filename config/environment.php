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

// install path
$default->fileSystemRoot  = "/usr/local/www/dms";
// server settings
$default->serverName = "change.to.your.hostname";
// whether ssl is enabled or not
$default->sslEnabled = true;
// Change this to reflect the authentication method you are using
// valid choices are: DBAuthenticator, LDAPAuthenticator
$default->authenticationClass = "DBAuthenticator";
require_once("$default->fileSystemRoot/lib/authentication/$default->authenticationClass.inc");

// Database info
$default->dbHost           = "localhost";
$default->dbName           = "dms";
$default->dbUser           = "dms";
$default->dbPass           = "pass";

// Change this to reflect the database you are using
//require("$default->fileSystemRoot/phplib/db_pgsql.inc");
require_once("$default->fileSystemRoot/phplib/db_mysql.inc");

// single db instantiation
require_once("$default->fileSystemRoot/lib/database/db.inc");
$default->db = new Database();

// instantiate system settings class
require_once("$default->fileSystemRoot/lib/database/lookup.inc");
require_once("$default->fileSystemRoot/lib/System.inc");
$default->system = new System();

// hack to set org to use for dashboard greeting
$default->organisationID = 1;

if ($default->system->initialised()) {
    $aSettings = array("ldapServer", "ldapRootDn", "ldapServerType",
                       "ldapDomain", "ldapSearchUser", "ldapSearchPassword", 
                       "emailServer", "emailFrom", "emailFromName",
                       "emailAdmin", "emailAdminName",
                       "documentRoot", "languageDirectory",
                       "uiDirectory", "rootUrl", "graphicsUrl", "uiUrl", "useFS", "defaultLanguage",
                       "sessionTimeout");
    
    for ($i=0; $i<count($aSettings); $i++) {
        $default->$aSettings[$i] = $default->system->get($aSettings[$i]);
    }
} else {
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
    $default->documentRoot  =  $default->fileSystemRoot . "/Documents";
    $default->languageDirectory  = $default->fileSystemRoot . "/locale";
    $default->uiDirectory  = $default->fileSystemRoot . "/presentation/lookAndFeel/knowledgeTree";
     
    // urls
    $default->rootUrl  = "";
    $default->graphicsUrl = $default->rootUrl . "/graphics";
    $default->uiUrl  = $default->rootUrl . "/presentation/lookAndFeel/knowledgeTree";
    
    // app settings
    // TODO: in browse- scan current folder and sync db
    $default->useFS            = true;
    $default->defaultLanguage  = "NewEnglish";
    // session timeout (in seconds)
    $default->sessionTimeout = 1200;
}
?>