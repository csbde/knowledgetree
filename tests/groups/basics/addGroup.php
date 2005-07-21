<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');

$ret = GroupUtil::addGroup(array("name" => "Test2"));
if (PEAR::isError($ret)) {
    print "Error adding group: " . $ret->toString();
} else if ($ret === true) {
    print "Group added successfully.\n";
} else {
    print "Bad code!\n";
}

?>
