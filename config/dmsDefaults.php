<?php

/**
 * $Id$
 *
 * Stores the defaults for the DMS application
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 */

// Some urls
$default->owl_root_url		= "";
$default->owl_graphics_url	= $default->owl_root_url . "/graphics";

// Directory where owl is located
$default->owl_fs_root		= "c:/owl";
$default->owl_LangDir		= $default->owl_fs_root . "/locale";

// Directory where The Documents Directory is On Disc
$default->owl_FileDir           =  "c:/owl";
//$default->owl_FileDir           =  "/tmp";

// Set to true to use the file system to store documents, false only uses the database
//$default->owl_use_fs            = false;
// the Trailing Slash is important here.
//$default->owl_FileDir           =  "/tmp/";
//$default->owl_compressed_database = 1;


$default->owl_use_fs            = true;

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

// Table with user info
$default->owl_users_table	= "users";

// Table with group memebership for users 
$default->owl_users_grpmem_table= "membergroup";
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
$default->owl_html_table	= "html";

// Table with html attributes
$default->owl_prefs_table	= "prefs";

// Table with file data info
$default->owl_files_data_table  = "filedata";

// Change this to reflect the database you are using
require_once("$default->owl_fs_root/phplib/db_mysql.inc");
//require("$default->owl_fs_root/phplib/db_pgsql.inc");

// Change this to reflect the authentication method you are using
//require_once("$default->owl_fs_root/lib/LDAPAuthenticator.inc");
//require_once("$default->owl_fs_root/lib/DBAuthenticator.inc");

// Database info
$default->owl_db_user           = "root";
$default->owl_db_pass           = "pass123";
$default->owl_db_host           = "";
$default->owl_db_name           = "intranet";

// logo file that must reside inside lang/graphics directory
$default->logo = "owl.gif";

// BUG Number: 457588
// This is to display the version information in the footer
//$default->version = "owl 0.7 20021129";
$default->version = "owl-dms 1.0 @build-date@";
$default->phpversion = "4.0.2";

$default->debug = True;

// WES STUFF 
// Untested or in the process of implementing (DORMANT)
// change at your own risks
// on Second though Don't even think of changing anything below this line.

//$default->owl_use_htaccess = 1;
//$default->owl_launch_in_browser = 0;
//$default->owl_restrict_linkto   = true;

$default->owl_email_server = "mail.jamwarehouse.com";
$default->owl_email_from = "owl@spare2.jamwarehouse.com";
$default->owl_email_fromname = "owl";

// user class constant definitions
// a numerical mapping is used to allow super user classes access
// to lower classes; ie. the permission check will allow the SA class
// to view pages with UA access.
define("SA", 0);
define("UA", 1);
define("A", 2);

// define site mappings
require_once("/lib/SiteMap.inc");
$default->siteMap = new SiteMap();
// action, section, page, userClass (SA, UA, U, A)
$default->siteMap->addPage("LOGIN", "login.php?loginAction=login", "General", "A");
$default->siteMap->addPage("LOGIN_FORM", "login.php?loginAction=loginForm", "General", "A"); 
$default->siteMap->addPage("LOGOUT", "logout.php", "General", "A");
$default->siteMap->addPage("BROWSE", "browse.php", "Browse Collections", "A");
$default->siteMap->addPage("ADDFOLDER", "addFolder.php", "Browse Collections", "UA");
$default->siteMap->addPage("ADDUSER", "addUser.php", "Administration", "UA");
$default->siteMap->addPage("ADDUNIT", "addUnit.php", "Administration", "SA");
$default->siteMap->addPage("ADDORG", "addOrganisation.php", "Administration", "SA");
?>
