<?php

/**
 * logout.php -- Logout page
 *  
 * This page controls the web application by responding to a set of
 * defined actions.  The controller performs session handling, page-level
 * authentication and forwards the request to the appropriate handling
 * page.  
 *
  *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Id$
 * @Copyright (c) 1999-2002 The Owl Project Team
 * @author michael
 * @package dms
 */
 
// main library routines and defaults
require_once("./config/dmsDefaults.php");
require_once("./lib/owl.lib.php");
require_once("./lib/control.inc");
require_once("./config/html.php");
require_once("./lib/Session.inc");

// destroy the session
Session::destroy();
// redirect to root
redirect("/");
?>
