<?php

/**
 * $Id$
 *  
 * Logout page -- this page controls the web application by responding to a set of
 * defined actions.  The controller performs session handling, page-level
 * authentication and forwards the request to the appropriate handling
 * page.  
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author <a href="mailto:michael@jamwarehouse.com>Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa 
 * @package dms
 */
 
// main library routines and defaults
require_once("./config/dmsDefaults.php");
require_once("$default->owl_fs_root/lib/db.inc");
require_once("$default->owl_fs_root/lib/dms.inc");
require_once("$default->owl_fs_root/lib/lookup.inc");
require_once("$default->owl_fs_root/lib/control.inc");
require_once("$default->owl_fs_root/lib/Session.inc");

// destroy the session
Session::destroy();
// redirect to root
redirect("/");
?>
