<?php

/**
 * $Id$
 *
 * Contains the controller helper functions
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * @version $Revision$
 * @author jam dms team
 * @package 
 */
 
/**
 * Redirects to the specified URL
 *
 * @param $url  the URL to forward to
 */
function redirect($url) {
    // everything is relative to the root url
    $url = $url . $default->owl_root_url . "/";
    // if we have a session
    if (isset($uid->sessdata["sessid"])) {
        // check url for parameters and add sessid accordingly
        if (strstr($url, "?")) {
            $url = $url . "&sess=". $uid->sessdata["sessid"];
        } else {
            $url = $url . "?sess=". $uid->sessdata["sessid"];
        }
    }
    header("Location: $url");
}

/**
 * Redirects to login if no session is present
 */
function checkSession($sessionID) {
    $sessionStatus = Owl_Session::verify($sessionID);
    switch ($sessionStatus["status"]) {
        
    }
}

