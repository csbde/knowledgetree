<?php

/**
 * $Id$
 *
 * Stores the environment settings for the DMS application
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 */
 
// ldap settings
$default->ldapServer = "192.168.1.9";
$default->ldapRootDn = "o=Medical Research Council";
// Database info
$default->owl_db_user           = "dms";
$default->owl_db_pass           = "djw9281js";
$default->owl_db_host           = "localhost";
$default->owl_db_name           = "dms";

// email settings
$default->owl_email_server = "mail.jamwarehouse.com";
$default->owl_email_from = "dms@jamwarehouse.com";
$default->owl_email_fromname = "the knowledge tree";

// url relative to website root
$default->owl_root_url		= "/dms";
// Directory where owl is located
$default->owl_fs_root		= "/usr/local/www/owl/dms";
// Directory where The Documents Directory is On Disc
$default->owl_FileDir           =  "/usr/local/www/owl/dms/Documents";

// url of graphics dir
$default->owl_graphics_url	= $default->owl_root_url . "/graphics";

require_once("$default->owl_fs_root/lib/Log.inc");
$default->log = new Log($default->owl_fs_root . "/log.txt", DEBUG);
?>
