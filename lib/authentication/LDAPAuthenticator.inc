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
     var $ldapServer;
     /**
      * The base LDAP DN to perform authentication against
      */
     var $ldapDN;
     
     /**
      * Creates a new instance of the LDAPAuthenticator
      *
      * @param string the LDAP server to connect to for validation
      * @param string the dn branch to perform the authentication against
      */
     function LDAPAuthentication($ldapServer, $ldapDN) {
         $this->ldapServer = $ldapServer;
         $this->ldapDN = $ldapDN;         
     }
     
     /**
      * Checks the user's password against the LDAP directory
      *
      * @param string the name of the user to check
      * @param string the password to check
      * @return boolean true if the password is correct, else false
      */
     function checkPassword($userName, $password) {
         global $default;
         $ldap = new AuthLdap();
         
     }
}
?>
