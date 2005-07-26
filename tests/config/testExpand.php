<?php

error_reporting(E_ALL);

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/config/config.inc.php');

$KTConfig = new KTConfig;
$KTConfig->loadFile("expand.ini");
$aExpectedRet = "bb";
$aRet = $KTConfig->get("expand/b");
if ($aRet === $aExpectedRet) {
    print "Success!\n";
} else {
    print "Failure!\n";
    print "Expected: " . print_r($aExpectedRet, true) . "\n";
    print "Got: " . print_r($aRet, true) . "\n";
}

$aExpectedRet = "kt@mail.example.org";
$aRet = $KTConfig->get("mail/emailFrom");
if ($aRet === $aExpectedRet) {
    print "Success!\n";
} else {
    print "Failure!\n";
    print "Expected: " . print_r($aExpectedRet, true) . "\n";
    print "Got: " . print_r($aRet, true) . "\n";
}

$aExpectedRet = "zxcvasdfzxcvrewqzxcvasdf";
$aRet = $KTConfig->get("multi/c");
if ($aRet === $aExpectedRet) {
    print "Success!\n";
} else {
    print "Failure!\n";
    print "Expected: " . print_r($aExpectedRet, true) . "\n";
    print "Got: " . print_r($aRet, true) . "\n";
}

?>
