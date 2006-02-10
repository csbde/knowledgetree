<?php

require_once(KT_LIB_DIR . '/authentication/authenticationprovider.inc.php');
require_once(KT_LIB_DIR . '/authentication/Authenticator.inc');
require_once(KT_LIB_DIR . '/authentication/class.AuthLdap.php');
require_once("Net/LDAP.php");
require_once("ldapbaseauthenticationprovider.inc.php");

class KTLDAPAuthenticationProvider extends KTLDAPBaseAuthenticationProvider {
    var $sName = "LDAP authentication provider";
    var $sNamespace = "ktstandard.authentication.ldapprovider";

    var $aAttributes = array ("cn", "uid", "givenname", "sn", "mail", "mobile");
    var $sAuthenticatorClass = "KTLDAPAuthenticator";
}

class KTLDAPAuthenticator extends KTLDAPBaseAuthenticator {
    var $aAttributes = array ("cn", "uid", "givenname", "sn", "mail", "mobile");
}

