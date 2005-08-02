<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/groups/Group.inc');

$oParentGroup =& Group::get(4); // Sysadmin
$oMemberGroup =& Group::get(1); // Test

$res = $oParentGroup->addMemberGroup($oMemberGroup);
var_dump($res);

?>
