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

$default->owl_graphics_url	= $default->owl_root_url . "/graphics";
$default->owl_LangDir		= $default->owl_fs_root . "/locale";

// Set to true to use the file system to store documents, false only uses the database
$default->owl_use_fs            = true;
//$default->owl_use_fs            = false;
// the Trailing Slash is important here.
//$default->owl_compressed_database = 1;



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

$default->owl_lang		= "NewEnglish";
$default->owl_notify_link       = "http://$_SERVER[SERVER_NAME]$default->owl_root_url/";

// Table with unit information
$default->owl_unit_table = "unit";

// Table with user info
$default->owl_users_table	= "users";

// User-unit mapping table
$default->owl_user_unit_table = "users_unit";

// Table with group membership for users 
$default->owl_users_grpmem_table= "membergroup";

/// Table with session information
$default->owl_sessions_table = "active_sessions";

// Table with file info
$default->owl_files_table	= "files";

// Table with folders info
$default->owl_folders_table	= "folders";

// Table with group info
$default->owl_groups_table	= "groups";

// Table with mime info
$default->owl_mime_table	= "mimes";

// Table with html attributes
$default->owl_html_table	= "intranet.html";

// Table with html attributes
$default->owl_prefs_table	= "intranet.prefs";

// Table with file data info
$default->owl_files_data_table  = "filedata";

//Table with document type info
$default->owl_document_types_table = "document_types";

//Table that links document types to document type fields
$default->owl_document_type_fields_table = "document_type_fields";

//Table with document type field info
$default->owl_fields_table = "document_fields";

// Table with document transactions info
$default->owl_document_transactions_table = "document_transactions";

// Table with web documents info for web publishing
$default->owl_web_documents_table = "web_documents";

// Table with web documents info for web publishing
$default->owl_web_documents_status_table = "web_documents_status";


// This is the defualt MailServer Host for emailing 
$default->owl_mail_server = "mail.jamwarehouse.com"


// Change this to reflect the database you are using
require_once("$default->owl_fs_root/phplib/db_mysql.inc");
//require("$default->owl_fs_root/phplib/db_pgsql.inc");

// Change this to reflect the authentication method you are using
//require_once("$default->owl_fs_root/lib/LDAPAuthenticator.inc");
require_once("$default->owl_fs_root/lib/Authenticator.inc");
require_once("$default->owl_fs_root/lib/DBAuthenticator.inc");

// logo file that must reside inside lang/graphics directory
$default->logo = "kt.jpg";

// BUG Number: 457588
// This is to display the version information in the footer
//$default->version = "owl 0.7 20021129";
$default->version = "owl-dms 1.0 @build-date@";
$default->phpversion = "4.0.2";

$default->errorMessage = "";
$default->debug = True;

// WES STUFF 
// Untested or in the process of implementing (DORMANT)
// change at your own risks
// on Second though Don't even think of changing anything below this line.

//$default->owl_use_htaccess = 1;
//$default->owl_launch_in_browser = 0;
//$default->owl_restrict_linkto   = true;

// user class constant definitions
// a numerical mapping is used to allow super user classes access
// to lower classes; ie. the permission check will allow the SA class
// to view pages with UA access.
// FIXME: these are just default groups- the order that they're inserted
//        preserves the access relationship (even though the id column is auto_incremented
//        and ideally shouldn't be used this way....., but if this was an ideal world i wouldn't
//        really be doing this, would i? ;)
/*
define("SA", 0);
define("UA", 1);
define("U", 2);
define("A", 3);
*/

// define site mappings
require_once("$default->owl_fs_root/lib/SiteMap.inc");
$default->siteMap = new SiteMap();
// action, section, page, userClass (SA, UA, U, A)
$default->siteMap->addPage("LOGIN", "login.php?loginAction=login", "General", "A");
$default->siteMap->addPage("LOGIN_FORM", "login.php?loginAction=loginForm", "General", "A"); 
$default->siteMap->addPage("LOGOUT", "logout.php", "General", "A");
$default->siteMap->addPage("DASHBOARD", "dashboard.php", "General", "A");
$default->siteMap->addPage("BROWSE", "browse.php", "Browse Collections", "A");
$default->siteMap->addPage("ADDFOLDER", "addFolder.php", "Browse Collections", "UA");
$default->siteMap->addPage("ADDUSER", "addUser.php", "Administration", "UA");
$default->siteMap->addPage("ADDUNIT", "addUnit.php", "Administration", "SA");
$default->siteMap->addPage("ADDORG", "addOrganisation.php", "Administration", "SA");
?>
