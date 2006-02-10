<?php

require_once(KT_LIB_DIR . '/authentication/authenticationprovider.inc.php');
require_once(KT_LIB_DIR . '/authentication/Authenticator.inc');
require_once("ldapbaseauthenticationprovider.inc.php");

class KTActiveDirectoryAuthenticationProvider extends KTLDAPBaseAuthenticationProvider {
    var $sName = "ActiveDirectory authentication provider";
    var $sNamespace = "ktstandard.authentication.adprovider";

    var $bGroupSource = true;

    var $sAuthenticatorClass = "KTActiveDirectoryAuthenticator";
    var $aAttributes = array ("cn", "samaccountname", "givenname", "sn", "userprincipalname", "telephonenumber");
}

class KTActiveDirectoryAuthenticator extends KTLDAPBaseAuthenticator {
    var $aAttributes = array ("cn", "samaccountname", "givenname", "sn", "userprincipalname", "telephonenumber");
}

