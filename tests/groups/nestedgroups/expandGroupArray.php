<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');

error_reporting(E_ALL);

$aGroupMembers = array(
    1 => array(3, 4),
    2 => array(9),
    3 => null,
    4 => array(5),
    5 => null,
    6 => array(1),
    7 => array(2, 3),
    8 => array(1, 7),
);

$aExpectedRet = array(
    1 => array(3, 4, 5),
    2 => array(9),
    3 => null,
    4 => array(5),
    5 => null,
    6 => array(1, 3, 4, 5),
    7 => array(2, 3, 9),
    8 => array(1, 2, 3, 4, 5, 7, 9),
);

$aRet = GroupUtil::expandGroupArray($aGroupMembers);
if ($aRet === $aExpectedRet) {
    print "Success!\n";
} else {
    print "Failure!\n";
    print "Expected: \n";
    print_r($aExpectedRet);
    print "Received: \n";
    print_r($aRet);
}

?>
