<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");

error_reporting(E_ALL);

$oTemplating = new KTTemplating();
$oTemplating->aLocationRegistry = array(
    "test" => "tests/templating/mytemplates",
);
$aExpectedRet = array("smarty", KT_DIR . "/tests/templating/mytemplates/findTemplate.smarty");
$aRet = $oTemplating->_findTemplate("findTemplate");
if ($aRet === $aExpectedRet) {
    print "Success!\n";
} else {
    print "Expected: $aExpectedRet\n";
    print "Got: $aRet\n";
}

?>
