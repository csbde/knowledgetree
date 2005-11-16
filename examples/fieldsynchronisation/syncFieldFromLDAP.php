<?php

require_once('../../config/dmsDefaults.php');

require_once('Net/LDAP.php');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');

$oKTConfig =& KTConfig::getSingleton();

$config = array(
    'dn' => $oKTConfig->get("ldap/ldapSearchUser"),
    'password' => $oKTConfig->get("ldap/ldapSearchPassword"),
    'host' => $oKTConfig->get("ldap/ldapServer"),
    'base' => $oKTConfig->get("ldap/ldapRootDn"),
);

$oFieldset =& KTFieldset::getByNamespace('http://ktcvs.local/local/fieldsets/synctest');
$oField = DocumentField::getByFieldsetAndName($oFieldset, 'synctest');

$oLdap =& Net_LDAP::connect($config);
if (PEAR::isError($oLdap)) {
    var_dump($oLdap);
    exit(0);
}

$aParams = array(
    'scope' => 'sub',
    'attributes' => array('cn'),
);
$rootDn = $oKTConfig->get("ldap/ldapRootDn");
if (is_array($rootDn)) {
    $rootDn = join(",", $rootDn);
}
$aResults = $oLdap->search($rootDn, '(objectClass=organizationalPerson)', $aParams);

$aValues = array();
foreach ($aResults->entries() as $oEntry) {
    // print $oEntry->dn() . "\n";
    $sValue = $oEntry->get_value('cn', 'single');
    // print $sValue . "\n";
    $aValues[] = $sValue;
}
KTMetadataUtil::synchroniseMetadata($oField, $aValues);
