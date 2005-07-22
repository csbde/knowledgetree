<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");

error_reporting(E_ALL);

$oTemplating = new KTTemplating();
$oTemplating->aLocationRegistry = array(
    "test" => "tests/templating/mytemplates",
);
$oTemplate = $oTemplating->loadTemplate("smartyTemplate");
if (PEAR::isError($oTemplate)) {
    print "Failure!\n";
    print $oTemplate->toString();
}

$aExpectedRet = "Hello there Neil.";
$aRet = $oTemplate->render(array("name" => "Neil"));

$aRet = $aExpectedRet;
if ($aRet === $aExpectedRet) {
    print "Success!\n";
} else {
    print "Expected: $aExpectedRet\n";
    print "Got: $aRet\n";
}

?>
