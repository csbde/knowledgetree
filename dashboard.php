<?php
/**
 * dashboard.php -- Main dashboard page.
 *  
 * This page is presented to the user after login.
 * It contains a high level overview of the users subscriptions, checked out 
 * document, pending approval routing documents, etc. 
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Id$
 * @Copyright (c) 1999-2003 The Owl Project Team
 * @author michael@jamwarehouse.com
 * @package dms
 */

// main library routines and defaults
require_once("./config/dmsDefaults.php");
require_once("./lib/owl.lib.php");
require_once("./config/html.php");
require_once("./lib/control.inc");
require_once("./lib/Session.inc");
require_once("./lib/SiteMap.inc");

// -------------------------------
// page start
// -------------------------------
    
echo generateLink("LOGOUT") . "logout</a>";
?>
