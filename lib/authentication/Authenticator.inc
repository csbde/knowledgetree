<?php
require_once("$default->fileSystemRoot/lib/users/User.inc");
/**
 * $Id$
 *
 * Interface class that performs all authentication functions.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
     * Verifies the login credentials
     *
     * @param string the user name of the user logging in
     * @param string the user's password
     * @return array containing userID and authentication status code
     */
    function login($sUserName, $sPassword) {
        global $default;
        // initialise return array
        $aUserDetails = array();

        // retrieve the userID
        $sSQL = "SELECT id FROM $default->users_table WHERE username = ?";
        $aParams = array($sUserName);
        $iUserID = DBUtil::getOneResultKey(array($sSQL, $aParams), 'id');
        if ($iUserID) {
            $oUser = & User::get($iUserID);

            // only check the password if the user is a system administrator or a guest user
            // or this user belongs to a unit (if its not a system admin)
            if (Permission::userIsSystemAdministrator($iUserID) || Permission::userIsGuest($iUserID) || User::getUnitID($iUserID)) {

                if ($this->checkPassword($sUserName, $sPassword)) {

                    $default->log->info("Authenticator::login authenticated $sUserName, id=$iUserID");
                    if ($iUserID) {
                        // add this to the return array
                        $aUserDetails["userID"] = $iUserID;
                        // remove stale sessions from the database for the user
                        // that is signing on.
                        Session::removeStaleSessions($aUserDetails["userID"]);

                        // Check if Maxsessions has been reached
                        $iMaxSessions = lookupField($default->users_table, "max_sessions", "id", $iUserID);
                        $default->log->debug("maxsessions=$iMaxSessions for userID=$iUserID");
                        $sql = $default->db;
                        $sQuery = array("SELECT count(*) as count FROM $default->sessions_table WHERE user_id = ?", $iUserID);/*ok*/
                        if ($sql->query($sQuery)) {
                            $sql->next_record();
                            $default->log->debug("sess count=" . $sql->f("count"));
                            if ($sql->f("count") >= $iMaxSessions) {
                                // return too many sessions status code
                                $aUserDetails["status"] = 3;
                            } else {
                                // authenticated successfully
                                $aUserDetails["status"] = 1;
                            }
                            // FIXME: account disabled status???
                        }
                        else {
                            // db access failed
                            $aUserDetails["status"] = 0;
                        }
                    } else {
                        // db access failed
                        $aUserDetails["status"] = 0;
                    }
                } else {
                    $default->log->info("Authenticator::login login failed ($sUserName)");
                    // authentication failed
                    $aUserDetails["status"] = 0;
                }
            } else {
                // not a unit user
                $aUserDetails["status"] = 4;
            }
        } else {
            // username doesn't exist
            $aUserDetails["status"] = 0;
        }
        return $aUserDetails;
    }

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
