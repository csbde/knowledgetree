<?php

require_once('../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/security/Permission.inc');

$aUsers = User::getList();
foreach ($aUsers as $oUser) {
    Permission::updateSearchPermissionsForUser($oUser->getID());
}

?>
