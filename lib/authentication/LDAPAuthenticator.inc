<?php

require_once("$default->owl_fs_root/lib/authentication/class.AuthLdap.php");
require_once("$default->owl_fs_root/lib/authentication/Authenticator.inc");

/**
 * $Id$
 * 
 * Perform authentication tasks against LDAP compliant directory server.
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.authentication
 */
class LDAPAuthenticator extends Authenticator {

    /**
     * The LDAP server to connect to
     */
    var $sLdapServer;
    /**
     * The base LDAP DN to perform authentication against
     */
    var $sBaseDN;
    /**
     * The LDAP accessor class
     */
    var $oLdap;

    /**
     * Creates a new instance of the LDAPAuthenticator
     *
     * @param string the LDAP server to connect to for validation (optional)
     * @param string the dn branch to perform the authentication against (optional)
     */
    function LDAPAuthentication($sLdapServer = "", $sLdapDN = "") {
        global $default;

        $this->sLdapServer = strlen($sLdapServer) > 0 ? $sLdapServer : $default->system->get("ldapServer");
        $this->sBaseDN = strlen($sLdapDN) > 0 ? $sLdapDN : $default->system->get("ldapRootDn");

        // initialise and setup ldap class
        $this->oLdap = new AuthLdap();
        $this->oLdap->server = array($this->sLdapServer);
        $this->oLdap->dn = $this->sBaseDN;
    }

    /**
     * Checks the user's password against the LDAP directory
     *
     * @param string the name of the user to check
     * @param string the password to check
     * @return boolean true if the password is correct, else false
     */
    function checkPassword($sUserName, $sPassword) {
        global $default;

        return $oLdap->checkPass($sUserName, $sPassword);
    }


    /**
     * Searches the LDAP directory for users matching the supplied search string.
     * 
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */
    function searchUsers($sUserNameSearch, $aAttributes) {
        global $default;

        // connect and search
        if ( $this->oLdap->connect() ) {
            // search for the users
            // append and prepend wildcards
            $aUserResults = $this->oLdap->getUsers("*" . $sUserNameSearch . "*", $aAttributes);
            //return $aUserResults;
            if ($aUserResults) {
                // return the array
                return $aUserResults;
            } else {
                // the search failed, return empty array
                return false;
            }
        } else {
            $_SESSION["errorMessage"] = "LDAP error: (" . $this->oLdap->ldapErrorCode . ") " . $this->oLdap->ldapErrorText;
            return false;
        }
    }
}
?>
