<?php

require_once("$default->fileSystemRoot/lib/authentication/Authenticator.inc");

/**
 * $Id$
 * 
 * Perform authentication tasks against the database.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.authentication
 */

class DBAuthenticator extends Authenticator {

    /**
     * Checks the user's password against the database
     *
     * @param string the name of the user to check
     * @param string the password to check
     * @return boolean true if the password is correct, else false
     */
    function checkPassword($userName, $password) {
        global $default;

        $sql = $default->db;
        if ($sql->query("SELECT * FROM $default->owl_users_table WHERE username = '$userName' AND password = '" . md5($password) . "'")) {
            if ($sql->num_rows($sql) == "1") {
                return true;
            } else {
                return false;
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
    }

    /**
     * Searches the directory for a specific user
     *
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */ 
    function getUser($sUserName, $aAttributes) {
        global $default, $lang_err_database;

        $sql = $default->db;
        $sQuery = "SELECT ";
        // build select
        for ($i=0; $i<count($aAttributes); $i++) {
            $sQuery .= $aAttributes[$i] . (( ($i+1) == count($aAttributes) ) ? "" : ", ");
        }
        $sQuery .= " FROM $default->owl_users_table WHERE username = '$sUserName'";

        if ($sql->query($sQuery)) {
            $aUserResults = array();
            while ($sql->next_record()) {
                for ($i=0; $i<count($aAttributes); $i++) {
                    $aUserResults["$sUserName"]["$aAttributes[$i]"] = $sql->f($aAttributes[$i]);
                }
            }
            return $aUserResults;
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
    }
    
    /**
     * Searches the user store for users matching the supplied search string.
     * 
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */
    function searchUsers($sUserNameSearch, $aAttributes) {
        global $default, $lang_err_database;

        $sql = $default->db;
        $sQuery = "SELECT ";
        // build select
        for ($i=0; $i<count($aAttributes); $i++) {
            $sQuery .= $aAttributes[$i] . (( ($i+1) == count($aAttributes) ) ? "" : ", ");
        }
        $sQuery .= " FROM $default->owl_users_table where username like '%$sUserNameSearch%'";

        if ($sql->query($sQuery)) {
            $aUserResults = array();
            while ($sql->next_record()) {
                $sUserName = $sql->f("username");
                for ($i=0; $i<count($aAttributes); $i++) {
                    $aUserResults["$sUserName"]["$aAttributes[$i]"] = $sql->f($aAttributes[$i]);
                }
            }
            return $aUserResults;
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
    }
}
?>
