<?php

// main library routines and defaults
require_once("../../config/dmsDefaults.php");
require_once("$default->owl_fs_root/lib/owl.lib.php");
require_once("$default->owl_fs_root/lib/administration/UnitManager.inc");

echo "<pre>";
// unit tests for UnitManager methods

$um = new UnitManager();
$userArray = $um->listLdapUsers($userNameSearch);
if (!'userArray') {
    echo "ldap user lookup failed!<br>";
} else {
    print_r($userArray);
}
/*
// do some transformation of the first entry in the array?
// think maybe just set username = uid

// setup user details array
$unitID = 1;
//($userDetails['username'], $userDetails['name'], '', $userDetails['email'], $userDetails['mobile'], $userDetails['ldap_dn'])";
$userDetails = array("username" => "michael",
                     "name" => "michael joseph",
                     "email" => "michael@jamwarehouse.com",
                     "mobile" => "0731418818",
                     "ldap_dn" => "uid=michael,ou=Alcohol and Drug Abuse,ou=Environment and Development,o=Medical Research Council"
                    );

global $default;

$result = $um->addUser($unitID, $userDetails);
if (!$result) {
    echo "add user failed!<br>";
    echo "error message=$default->errorMessage";
} else {
    echo "added user successfully<br>";
}
*/
echo "</pre>";
?>
