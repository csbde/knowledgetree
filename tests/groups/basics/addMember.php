<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/groups/Group.inc');

/* $oGroup = addGroup("testHasMember");
if (PEAR::isError($oGroup)) { print $oGroup->toString(); exit(1); }
$oUser = addUser("testHasMember");
if (PEAR::isError($oUser)) { print $oGroup->toString(); exit(1); }
$oGroup->addMember($oUser);*/

$oUser = User::get(1);
$oGroup = Group::get(4);
$res = $oGroup->addMember($oUser);
if ($res === true) {
    print "Success!\n";
} else {
    var_dump($res);
    print "Failed!\n";
}

?>
