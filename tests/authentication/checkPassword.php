<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');

$oUser =& User::getByUserName('nbm2');
if (0) {
    $foo = KTAuthenticationUtil::checkPassword($oUser, 'asdf');
    var_dump($foo);
} else {
    $foo = KTAuthenticationUtil::checkPassword($oUser, 'asdjasdjk');
    var_dump($foo);
}

?>
