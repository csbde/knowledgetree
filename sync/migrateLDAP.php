<?php

/**
 * LDAP Migration script.
 * For all users in the database, the username is queried in the new directory server
 * This DN is then used to create an UPDATE statement to correct the old directory server dns
 * in the database.
 */

require_once("../config/dmsDefaults.php");

// first select all users
$aUsers = User::getList();

// then initialise the LDAP authenticator with the new directory server address
$sNewLdapServer = "jam001.jamwarehouse.com";
$sNewLdapDn = "CN=Users,DC=jamwarehouse,DC=com";
$oLdap = new LDAPAuthenticator($sNewLdapServer, $sNewLdapDn);;
echo "<pre>";
for ($i=0; $i<count($aUsers); $i++) {
	// for each user, lookup the dn based on the username	
	$oUser = $aUsers[$i];
	$aResults = $oLdap->searchUsers($oUser->getUserName(), array ("dn"));
	if (count($aResults) > 1) {
		echo "retrieved " . count($aResults) . " matches for username=" . $oUser->getUserName() . ": " . arrayToString($aResults) . "<br>";
	} else if (count($aResults) == 1) {
		$sNewDN = $aResults[$oUser->getUserName()]["dn"];
		// echo an update statement that sets the dn to the correct value		
		echo "UPDATE users SET ldap_dn='" . $sNewDN . "' WHERE id=" . $oUser->getID() . "<br>";
	}
}
echo "Change the configuration file to point to the new directory server-" . $sNewLdapServer;
echo "</pre>";
?>