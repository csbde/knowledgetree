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
$default->owl_db_user           = "rob";
$default->owl_db_pass           = "rob";
$default->owl_db_host           = "localhost";
$default->owl_db_name           = "owl";
// email settings
$default->owl_email_server = "mail.jamwarehouse.com";
$default->owl_email_from = "owl@rob.jamwarehouse.com";
$default->owl_email_fromname = "owl";
$default->owl_root_url		= "C:/Projects/MRC/Devel/owl";
// Directory where owl is located
$default->owl_fs_root		= "C:/Projects/MRC/Devel/owl";
// Directory where The Documents Directory is On Disc
$default->owl_FileDir           =  "C:/Projects/MRC/Devel/owl";

require_once($default->owl_fs_root . "/lib/Log.inc");

$default->log = new Log($default->owl_fs_root . "/log.txt", INFO);

?>
