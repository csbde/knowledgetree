<?php
require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");

$server = array(
    "REQUEST_URI" => "/presentation/lookAndFeel/knowledgeTree/preferences/editUserPrefsBL/asdf?asdf=1&foo=&",
    "SCRIPT_NAME" => "/presentation/lookAndFeel/knowledgeTree/preferences/editUserPrefsBL.php",
);

$expected = "/presentation/lookAndFeel/knowledgeTree/preferences/editUserPrefsBL";
$received = KTUtil::getRequestScriptName($server);
if ($expected !== $received) {
    print "FAILED!\n";
    print "Expected: $expected\n";
    print "Received: $received\n";
}
