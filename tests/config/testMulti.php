<?php

error_reporting(E_ALL);

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/config/config.inc.php');

$KTConfig = new KTConfig;
$KTConfig->loadFile("foo.ini");
$KTConfig->loadFile("bar.ini");
$aExpectedRet = "foo";
$aRet = $KTConfig->get("bar");
if ($aRet === $aExpectedRet) {
    print "Success!\n";
} else {
    print "Failure!\n";
    print "Expected: " . print_r($aExpectedRet, true) . "\n";
    print "Got: " . print_r($aRet, true) . "\n";
}
$aRet = $KTConfig->get("bar/bar");
if ($aRet === $aExpectedRet) {
    print "Success!\n";
} else {
    print "Failure!\n";
    print "Expected: " . print_r($aExpectedRet, true) . "\n";
    print "Got: " . print_r($aRet, true) . "\n";
}
$aRet = $KTConfig->get("bar/asdf");
if ($aRet === $aExpectedRet) {
    print "Success!\n";
} else {
    print "Failure!\n";
    print "Expected: " . print_r($aExpectedRet, true) . "\n";
    print "Got: " . print_r($aRet, true) . "\n";
}
$aExpectedRet = "asdf";
$aRet = $KTConfig->get("asdf/asdf");
if ($aRet === $aExpectedRet) {
    print "Success!\n";
} else {
    print "Failure!\n";
    print "Expected: " . print_r($aExpectedRet, true) . "\n";
    print "Got: " . print_r($aRet, true) . "\n";
}

?>
