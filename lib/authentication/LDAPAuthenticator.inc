<?php

require_once("./lib/class.AuthLdap.php");
require_once("$default->owl_fs_root/lib/Authenticator.inc");

/**
 * $Id$
 * 
 * Perform authentication tasks against LDAP compliant directory server.
 * 
 * @version $Revision$ 
 * @author <a href="mailto:michael@jamwarehouse.com>Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package dmslib
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
      * @param $ldapServer
      *        the LDAP server to connect to for validation
      * @param $ldapDN
      *        the dn branch to perform the authentication against
      */
     function LDAPAuthentication($ldapServer, $ldapDN) {
         $this->ldapServer = $ldapServer;
         $this->ldapDN = $ldapDN;         
     }
     
     /**
      * Checks the user's password against the LDAP directory
      *
      * @param $userName
      *        the name of the user to check
      * @param $password
      *        the password to check
      * @return true if the password is correct, else false
      */
     function checkPassword($userName, $password) {
         global $default;
         $ldap = new AuthLdap();
         
     }
}
?>
