<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');

$oUser =& User::getByUserName('nbm2');
$foo = KTAuthenticationUtil::checkPassword($oUser, 'asdfa');
var_dump($foo);

?>
