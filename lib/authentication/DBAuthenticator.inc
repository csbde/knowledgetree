<?php

require_once("$default->owl_fs_root/lib/authentication/Authenticator.inc");

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

        $sql = new Owl_DB;
        $query = "select * from $default->owl_users_table where username = '$userName' and password = '" . md5($password) . "'";
        $sql->query($query);
        $numrows = $sql->num_rows($sql);

        if ($numrows == "1") {            
            return true;
        } else {
            return false;
        }
     }
}
?>
