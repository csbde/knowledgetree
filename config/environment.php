<?php

/**
 * $Id$
 *
 * Stores the environment settings for the DMS application
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 */

// install path
$default->fileSystemRoot  = "/usr/local/www/owl/dms";

// Database info
$default->dbUser           = "dms";
$default->dbPass           = "djw9281js";
$default->dbHost           = "localhost";
$default->dbName           = "dms";

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

if ($default->system->initialised()) {
    $aSettings = array("ldapServer", "ldapRootDn", "emailServer", "emailFrom", "emailFromName",
                       "emailAdmin", "emailAdminName",
                       "serverName", "fileSystemRoot", "documentRoot", "languageDirectory",
                       "uiDirectory", "rootUrl", "graphicsUrl", "uiUrl", "useFS", "defaultLanguage",
                       "sessionTimeout", "sslEnabled");    
    
    for ($i=0; $i<count($aSettings); $i++) {
        $default->$aSettings[$i] = $default->system->get($aSettings[$i]);
    }
} else {
    // TODO: redirect to system setup form

    // ldap settings
    $default->ldapServer = "192.168.1.9";
    $default->ldapRootDn = "o=Medical Research Council";
    

    // email settings
    $default->emailServer = "mail.jamwarehouse.com";
    $default->emailFrom = "dms@jamwarehouse.com";
    $default->emaiFromName = "MRC Document Management System";
    $default->emailAdmin = "dmsHelp@jamwarehouse.com";
    $default->emailAdminName = "DMS Administrator";
    
    $default->serverName = "changeme.to.your.hostname";
    
    // directories
    $default->documentRoot  =  "/usr/local/www/owl/dms/Documents";
    $default->languageDirectory  = $default->fileSystemRoot . "/locale";
    $default->uiDirectory  = $default->fileSystemRoot . "/presentation/lookAndFeel/knowledgeTree";
    $default->uMask = "0755";
     
    // urls
    $default->rootUrl  = "/dms";
    $default->graphicsUrl = $default->rootUrl . "/graphics";
    $default->uiUrl  = $default->rootUrl . "/presentation/lookAndFeel/knowledgeTree";
    
    // app settings
    // TODO: in browse- scan current folder and sync db
    $default->useFS            = true;
    $default->defaultLanguage  = "NewEnglish";
    // session timeout (in seconds)
    $default->sessionTimeout = 1200;
    // whether ssl is enabled or not
    $default->sslEnabled = true;
//}
?>
