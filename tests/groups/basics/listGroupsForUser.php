<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');

$oUser = User::get(1);
foreach (GroupUtil::listGroupsForUser($oUser) as $oGroup) {
    print $oGroup->getName() . "\n";
}

?>
