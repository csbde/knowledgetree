<?php

/**
 * $Id$
 * 
 * Interface class that performs all authentication functions.
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
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
        global $default, $lang_err_database;
        // initialise return array
        $aUserDetails = array();

        if ($this->checkPassword($sUserName, $sPassword)) {
            // retrieve the userID
            $iUserID = lookupID($default->owl_users_table, "username", "$sUserName");
            $default->log->info("Authenticator::login authenticated ($sUserName, $sPassword), id=$iUserID");
            if ($iUserID) {
                // add this to the return array
                $aUserDetails["userID"] = $iUserID;
                // remove stale sessions from the database for the user
                // that is signing on.
                Session::removeStaleSessions($aUserDetails["userID"]);

                // Check if Maxsessions has been reached
                // FIXME: make this check work before production install
                $maxSessions = lookupField($default->owl_users_table, "max_sessions", "id", $iUserID);
                $default->log->debug("maxsessions=$maxSessions for userID=$iUserID");
                $sql = $default->db;
                if ($sql->query("SELECT * FROM $default->owl_sessions_table WHERE user_id = '".$aUserDetails["user_id"]."'")) {
                    if ($sql->num_rows() >= $maxSessions) {
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
                    $_SESSION["errorMessage"] = $lang_err_database;
                    $aUserDetails["status"] = 0;
                }
            } else {
                // db access failed
                $_SESSION["errorMessage"] = $lang_err_database;
                $aUserDetails["status"] = 0;
            }
        } else {
            $default->log->info("Authenticator::login login failed ($sUserName, $sPassword)");        
            // authentication failed
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
     * [Abstract] Searches the user store for users matching the supplied search string.
     * 
     * @param string the username to search for
     * @param array the attributes to return from the search     
     * @return array containing the users found
     */
    function searchUsers($sUserNameSearch, $aAttributes) {}
}
?>