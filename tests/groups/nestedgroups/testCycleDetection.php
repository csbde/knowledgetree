<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');

$aGroupMembers = array(
    1 => array(2, 3, 4),
    2 => array(),
    3 => null,
    4 => array(5),
    5 => null,
    6 => array(1),
    7 => array(2, 3),
);
$iGroupID = 5;

$aRet = GroupUtil::filterCyclicalGroups($iGroupID, $aGroupMembers);
sort($aRet);

$aExpectedResult = array(2, 3, 7);

if ($aRet === $aExpectedResult) {
    print "Success!\n";
} else {
    print "Failed!\n";
    print "Expected: " . print_r($aExpectedResult, true);
    print "Got: " . print_r($aRet, true);
}

?>
