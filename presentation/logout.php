<?php

/**
 * $Id$
 *  
 * This page logs the current user out of the system.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa 
 * @package presentation
 */

// main library routines and defaults
require_once("../config/dmsDefaults.php");

// logout
$oAuth = new $default->authenticationClass;
$oAuth->logout();

// redirect to root
redirect((strlen($default->rootUrl) > 0 ? $default->rootUrl : "/"));
?>
