<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');

error_reporting(E_ALL);

$oUser =& User::get(4);
$aGroups = GroupUtil::listGroupsForUserExpand($oUser);
var_dump($aGroups);

?>
