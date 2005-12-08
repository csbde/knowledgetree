<?php
require_once("../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');

class KTLogoutDispatcher extends KTStandardDispatcher {
    function do_main() {
        global $default;

        $oAuthenticator =& KTAuthenticationUtil::getAuthenticatorForUser($this->oUser);
        $oAuthenticator->logout($this->oUser);
        Session::destroy();

        redirect((strlen($default->rootUrl) > 0 ? $default->rootUrl : "/"));
        exit(0);
    }
}
$d =& new KTLogoutDispatcher;
$d->dispatch();
?>
