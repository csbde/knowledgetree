<?php

// main library routines and defaults
require_once("../config/dmsDefaults.php");

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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa 
 * @package presentation
 */

// destroy the session
Session::destroy();
// redirect to root
redirect("/");
?>
