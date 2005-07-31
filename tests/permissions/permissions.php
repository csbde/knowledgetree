<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/permissions/permission.inc.php");

error_reporting(E_ALL);

// var_dump(KTHelpReplacement::get(1));
$res = KTPermission::createFromArray(array(
));

?>
