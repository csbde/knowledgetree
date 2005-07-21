<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/groups/Group.inc');

$oGroup = Group::get(4);
foreach ($oGroup->getMembers() as $oUser) {
    print $oUser->getName() . "\n";
}

?>
