<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");

$aTestVals = array(
    "http://foo.bar/foo.php?foo=bar" => array("http://foo.bar/foo.php", "foo=bar"),
    "http://foo.bar/foo.php?foo=bar&bar=baz" => array("http://foo.bar/foo.php?foo=bar", "bar=baz"),
    "http://foo.bar/foo.php?foo=bar&bar=baz&baz=quux" => array("http://foo.bar/foo.php?foo=bar", "bar=baz&baz=quux"),
    "http://foo.bar/foo.php" => array("http://foo.bar/foo.php", ""),
);

foreach ($aTestVals as $sExpected => $aArgs) {
    $sResult = KTUtil::addQueryString($aArgs[0], $aArgs[1]);
    if ($sResult !== $sExpected) {
        print "FAIL!\n";
        print "Expected: $sExpected\n";
        print "Received: $sResult\n";
    }
}

