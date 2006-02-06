<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/groups/Group.inc');
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');

$oGroup = Group::get(5);
$foo = KTAuthenticationUtil::synchroniseGroupToSource($oGroup);
var_dump($foo);

?>
