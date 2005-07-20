<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");

$aSource = array(
    array("active = ?", array(true)),
    array("foo = ? AND asdf = ?", array(5, "ff")),
);

$aResults = KTUtil::whereToString($aSource);

$aExpectedResults = array(
    "active = ? AND foo = ? AND asdf = ?",
    array(true, 5, "ff"),
);

if ($aResults === $aExpectedResults) {
    print "Success!\n";
} else {
    print "Failure!\n";
    print "Received: " . print_r($aResults, true) . "\n";
    print "Expected: " . print_r($aExpectedResults, true) . "\n";
}

?>
