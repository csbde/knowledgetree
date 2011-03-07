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

$query = KTUtil::arrayGet($_REQUEST, 'q');
if (empty($query)) {
    echo '';
    exit(0);
}

// just build a static list for test purposes
$users = array(1 => 'bob', 613 => 'dave', 614 => 'doug', 580 => 'davey crockett', 593 => 'crockett and tubbs', 597 => 'moooo, I\'m a cow');

$return = array();
foreach ($users as $id => $user) {
	if (strpos($user, $query) !== false) {
	    $user = array('id' => $id, 'name' => $user);
	    $return[] = $user;
	}
}

echo json_encode($return);
exit(0);

?>