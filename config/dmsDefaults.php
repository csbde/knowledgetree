<?php

/**
 * $Id$
 *
 * Stores the defaults for the DMS application
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 */

// include the environment settings
require_once("environment.php");

// table mapping entries
include("tableMappings.inc");
// site map definition
include("siteMap.inc");
// instantiate log
require_once("$default->fileSystemRoot/lib/Log.inc");
$default->log = new Log($default->fileSystemRoot . "/log", INFO);
require_once("$default->fileSystemRoot/phpmailer/class.phpmailer.php");
require_once("$default->fileSystemRoot/lib/session/Session.inc");
require_once("$default->fileSystemRoot/lib/session/control.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
// browser settings
require_once("$default->fileSystemRoot/phpSniff/phpSniff.class.php");
require("browsers.inc");
// import request variables and setup language
require_once("$default->fileSystemRoot/lib/dms.inc");
?>