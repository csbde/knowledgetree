<?php
require_once(KT_LIB_DIR . '/users/User.inc');
/**
 * $Id$
 *
 * Interface class that performs all authentication functions.
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.authentication 
 */
class Authenticator {
    /**
     * Logs the user out of the application
     *
     * @param int the ID of user logging out
     * @param string the user's sessionID
     */
    function logout() {
        // remove session from db
        Session::destroy();
    }

    /**
     * [Abstract] Checks the user's password
     *
     * @param string the name of the user to check
     * @param string the password to check
     * @return boolean true if the password is correct, else false
     */
    function checkPassword($sUserName, $sPassword) {}

    /**
     * [Abstract] Searches the directory for a specific user
     *
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */ 
    function getUser($sUserName, $aAttributes) {}
        
    /**
     * [Abstract] Searches the user store for users matching the supplied search string.
     * 
     * @param string the username to search for
     * @param array the attributes to return from the search     
     * @return array containing the users found
     */
    function searchUsers($sUserNameSearch, $aAttributes) {}
}
?>
