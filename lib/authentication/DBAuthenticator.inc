<?php
require_once("$default->fileSystemRoot/lib/authentication/Authenticator.inc");
/**
 * $Id$
 *
 * Perform authentication tasks against the database.
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
        if ($sql->query("SELECT * FROM $default->users_table WHERE username = '$userName' AND password = '" . md5($password) . "'")) {
            if ($sql->num_rows($sql) == "1") {
                return true;
            } else {
                return false;
            }
        } else {
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
        global $default;

        $sql = $default->db;
        $sQuery = "SELECT ";
        // build select
        for ($i=0; $i<count($aAttributes); $i++) {
            $sQuery .= $aAttributes[$i] . (( ($i+1) == count($aAttributes) ) ? "" : ", ");
        }
        $sQuery .= " FROM $default->users_table WHERE username = '$sUserName'";

        if ($sql->query($sQuery)) {
            $aUserResults = array();
            while ($sql->next_record()) {
                for ($i=0; $i<count($aAttributes); $i++) {
                    $aUserResults["$sUserName"]["$aAttributes[$i]"] = $sql->f($aAttributes[$i]);
                }
            }
            return $aUserResults;
        } else {
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
        global $default;

        $sql = $default->db;
        $sQuery = "SELECT ";
        // build select
        for ($i=0; $i<count($aAttributes); $i++) {
            $sQuery .= $aAttributes[$i] . (( ($i+1) == count($aAttributes) ) ? "" : ", ");
        }
        $sQuery .= " FROM $default->users_table where username like '%$sUserNameSearch%'";

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
            return false;
        }
    }
}
?>
