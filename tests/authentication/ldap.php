<?php
echo "<pre>";
$user="michael";
s$password="michael";
$ldap["domain"]="jamwarehouse.com";
$ldap["dn"]=$user."@".$ldap["domain"]; //microsoft ldap wants the username@domai
n for authentication
//$ldap["dn"]="uid=$user, ou=The Jam Warehouse,ou=External to MRC, o=Medical Res
earch Council";
$ldap["ds"]="192.168.1.8"; //ldap server
if ($ldap["connection"]=ldap_connect($ldap["ds"])) {
    if ($ldap["connection"]) {
        echo "binding to " . $ldap["dn"];
        //if(@ldap_bind($ldap["connection"],$ldap["dn"],$password)) {
        if(@ldap_bind($ldap["connection"])) {
            $result = ldap_search( $ldap["connection"], "CN=Users,DC=jamwarehous
e,DC=com", "samaccountname=*mi*");
            echo '$result = ldap_search( $ldap["connection"], "CN=Users,DC=jamwa
rehouse,DC=com", "samaccountname=*mi*");';
            //$result = ldap_search( $ldap["connection"], "o=Medical Research Co
uncil", "uid=karen");
            $account="samaccountname=".$user;
            //$result = ldap_list($ldap["connection"], "CN=Users,DC=jamwarehouse
,DC=com", $account);
            $entry = ldap_get_entries($ldap["connection"], $result);
            $fullname=$entry[0]["cn"][0];
            $email=$entry[0]["mail"][0];
            $firstname=$entry[0]["givenname"][0];
            $midname=$entry[0]["initials"][0];
            $lastname=$entry[0]["sn"][0];

            //print_r($entry[0]["samaccountname"]);
            print_r($entry);

            //print "<BR>Your name is ".$fullname." and your email is ".$email;
        } else {
            print ("<BR><B>Incorrect password or user ".$user." not found.</B><P
>");
        }
    }
}
echo "</pre>";
?>