<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/groups/Group.inc');

$oParentGroup =& Group::get(1); // Sysadmin
$oMemberGroup =& Group::get(4); // Test

$res = $oParentGroup->removeMemberGroup($oMemberGroup);
var_dump($res);

?>
