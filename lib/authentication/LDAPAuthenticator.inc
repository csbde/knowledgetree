<?php
require_once("$default->fileSystemRoot/lib/authentication/class.AuthLdap.php");
require_once("$default->fileSystemRoot/lib/authentication/Authenticator.inc");
/**
 * $Id$
 *
 * Perform authentication tasks against LDAP compliant directory server.
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
     * @param string the ldap server type (optional)
     */
    function LDAPAuthenticator($sLdapServer = "", $sLdapDN = "", $sServerType = "", $sLdapDomain = "") {
        global $default;

        $this->sLdapServer = strlen($sLdapServer) > 0 ? $sLdapServer : $default->ldapServer;
        $this->sBaseDN = strlen($sLdapDN) > 0 ? $sLdapDN : $default->ldapRootDn;
        $this->sServerType = strlen($sServerType) > 0 ? $sServerType : $default->ldapServerType;
        $this->sLdapDomain = strlen($sLdapDomain) > 0 ? $sLdapDomain : $default->ldapDomain;
        
        // initialise and setup ldap class
        $this->oLdap = new AuthLdap($this->sLdapServer, $this->sBaseDN, $this->sServerType, $this->sLdapDomain, $default->ldapSearchUser, $default->ldapSearchPassword);
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
        if ($this->oLdap->connect()) {
            // lookup dn from username - must exist in db
            $sBindDn = lookupField($default->users_table, "ldap_dn", "username", $sUserName);
            if ($sBindDn && $sPassword) {
                if ( $this->oLdap->authBind($sBindDn, $sPassword) ) {
                    return true;
                } else {
                    $_SESSION["errorMessage"] = "LDAP error: (" . $this->oLdap->ldapErrorCode . ") " . $this->oLdap->ldapErrorText;
                    return false;
                }
            } else {
                // no ldap_dn for this user, so reject this authentication attempt
                $_SESSION["errorMessage"] = "Username $sUserName does not not exist in the DMS.  Please contact the System Administrator for assistance.";
                return false;
            }
        } else {
            $_SESSION["errorMessage"] = "LDAP error: (" . $this->oLdap->ldapErrorCode . ") " . $this->oLdap->ldapErrorText;
            return false;
        }
    }


    /**
     * Searched the directory for a specific user
     *
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */ 
    function getUser($sUserName, $aAttributes) {
        global $default;
        // connect and search
        if ( $this->oLdap->connect() ) {
            // search for the users
            // append and prepend wildcards
            $aUserResults = $this->oLdap->getUsers($sUserName, $aAttributes);
            if ($aUserResults) {
                // return the array
                return $aUserResults;
            } else {
                // the search failed, return empty array
                return array();
            }
        } else {
            $_SESSION["errorMessage"] = "LDAP error: (" . $this->oLdap->ldapErrorCode . ") " . $this->oLdap->ldapErrorText;
            return false;
        }
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
            if ($aUserResults) {
                // return the array
                return $aUserResults;
            } else {
                // the search failed, return empty array
                return array();
            }
        } else {
            $default->log->error("LDAPAuthentication::searchUsers LDAP error: (" . $this->oLdap->ldapErrorCode . ") " . $this->oLdap->ldapErrorText);
            return false;
        }
    }
}
?>
