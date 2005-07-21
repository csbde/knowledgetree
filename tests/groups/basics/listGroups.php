<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');

foreach (GroupUtil::listGroups() as $oGroup) {
    print $oGroup->getName() . "\n";
}

?>
