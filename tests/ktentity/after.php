<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/users/User.inc");

error_reporting(E_ALL);

$oUser = User::getByLastLoginAfter('1990-01-01 00:00:00');
var_dump($oUser);

?>
