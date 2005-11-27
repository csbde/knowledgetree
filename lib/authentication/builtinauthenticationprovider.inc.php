<?php

require_once(KT_LIB_DIR . '/authentication/authenticationprovider.inc.php');
require_once(KT_LIB_DIR . '/authentication/Authenticator.inc');
require_once('DBAuthenticator.inc');

class KTBuiltinAuthenticationProvider extends KTAuthenticationProvider {
    var $sName = "Built-in authentication provider";
    var $sNamespace = "ktcore.authentication.builtin";

    function &getAuthenticator($oSource) {
        // $oSource is null, since the built-in authentication provider
        // only has a single, non-registered, instance.
        return new BuiltinAuthenticator;
    }
    
    function showUserSource($oUser, $oSource) {
        return '<p class="descriptiveText"><a href="?action=setPassword&user_id=' . $oUser->getId() . '">Change ' . $oUser->getName() . '\'s password</a></p>';
    }
}

class BuiltinAuthenticator extends Authenticator {
    /**
     * Checks the user's password against the database
     *
     * @param string the name of the user to check
     * @param string the password to check
     * @return boolean true if the password is correct, else false
     */
    function checkPassword($oUser, $password) {
        global $default;

        $sql = $default->db;
        $userName = $oUser->getUserName();
        $sQuery = "SELECT * FROM $default->users_table WHERE username = ? AND password = ?";/*ok*/
        $aParams = array($userName, md5($password));
        if ($sql->query(array($sQuery, $aParams))) {
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
        $sQuery = "SELECT ";/*ok*/
        // build select
        for ($i=0; $i<count($aAttributes); $i++) {
            $sQuery .= $aAttributes[$i] . (( ($i+1) == count($aAttributes) ) ? "" : ", ");
        }
        $sQuery .= " FROM $default->users_table WHERE username = ?";
        $aParams = array($sUserName);

        if ($sql->query(array($sQuery, $aParams))) {
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
        $sQuery = "SELECT ";/*ok*/
        // build select
        for ($i=0; $i<count($aAttributes); $i++) {
            $sQuery .= $aAttributes[$i] . (( ($i+1) == count($aAttributes) ) ? "" : ", ");
        }
        $sQuery .= " FROM $default->users_table where username like '%" . DBUtil::escapeSimple($sUserNameSearch) . "%'";

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

