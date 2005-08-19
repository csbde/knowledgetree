<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");

$aSource = array(
    array('unzip', "-q", "-j", "-n", "-d", '/tmp', '5 July 2005 Pricelist - Rectron(cpt).zip'),
    array('unzip', "-q", "-j", "-n", "-d", '/tmp', "5'th July 2005 Pricelist - Rectron(cpt).zip"),
    array('echo', ''),
    array('echo', ' '),
);

$aExpectedResults = array(
  "'unzip' '-q' '-j' '-n' '-d' '/tmp' '5 July 2005 Pricelist - Rectron(cpt).zip'",
  "'unzip' '-q' '-j' '-n' '-d' '/tmp' '5'\''th July 2005 Pricelist - Rectron(cpt).zip'",
  "'echo' ''",
  "'echo' ' '",
);

$aResults = array();

foreach ($aSource as $aArgs) {
    $aResults[] = KTUtil::safeShellString($aArgs);
}

if ($aResults === $aExpectedResults) {
    print "Success!\n";
} else {
    print "Failure!\n";
    print "Received: " . print_r($aResults, true) . "\n";
    print "Expected: " . print_r($aExpectedResults, true) . "\n";
}

?>
