<?php

/**
 * NOTES:
 *
 * 1. We are going to want to generate the list of users on first call and then cache this using memcache.
 *    Subsequent calls will retrieve the user list from the memcache store and not the db.
 * 2. It would be nice if this (as well as other sections of the site) didn't have to include dmsDefaults.
 *    I think we should look into a minimal version of dmsDefaults which can be extended to create the full version.
 *    The minimal version can then be loaded for instances like this.
 *
 */

require_once('../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/users/User.inc');

$query = KTUtil::arrayGet($_REQUEST, 'q');
if (empty($query)) {
    echo '';
    exit(0);
}

$userList = User::getList("name like '%$query%' OR username like '%$query%'");
foreach ($userList as $user) {
    $name = $user->getName();
    $users[] = array('id' => $user->getId(), 'name' => !empty($name) ? $name : $user->getUsername());
}

echo json_encode($users);
exit(0);

?>