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
 * @package dmslib
 */
 
/**
 * Redirects to the specified URL
 *
 * @param $url  the URL to forward to
 */
function redirect($url) {
    // everything is relative to the root url
    $url = $default->owl_root_url . $url;
    header("Location: $url");
}

/**
 * Generates a link via the control page, with the passed action
 *
 * @param $action
 *        the controller action to generate a link for
 * @return the generated href
 */
 //TODO: maybe this should just be the url?
function generateLink($action) {
    return "<a href=\"control.php?action=$action\">";
}

/**
 * Validates the session.
 *
 * @param $sessionID
 *        the session ID to validate
 * @return
 *        true if the session is valid, else false.
 */
function checkSession() {
    $sessionStatus = Session::verify();
    // TODO: error handling in here with appropriate actions
    //       error messages are in $sessionStatus["errorMessage"]
    switch ($sessionStatus["status"]) {
        case 1 : // session verified, update lastused time
                 return true;
                 break;
        case 2 : // session timed out                 
        case 3 : // session already in use
                 return false;
    }
}
