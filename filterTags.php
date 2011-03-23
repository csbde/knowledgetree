<?php

/**
 * NOTES:
 *
 * 1. It would be nice if this (as well as other sections of the site) didn't have to include dmsDefaults.
 *    I think we should look into a minimal version of dmsDefaults which can be extended to create the full version.
 *    The minimal version can then be loaded for instances like this.
 */

require_once('config/dmsDefaults.php');

$query = KTUtil::arrayGet($_REQUEST, 'q');
if (empty($query)) {
    echo '';
    exit(0);
}

// Always return typed input as selectable option.
$tags[] = array('id' => $query, 'name' => $query);

$sql = "SELECT id, tag FROM tag_words WHERE tag LIKE '%$query%'";
$tagResult = DBUtil::getResultArray(array($sql));
foreach ($tagResult as $id => $tag) {
    $tags[] = array('id' => $tag['id'], 'name' => $tag['tag']);
}

echo json_encode($tags);
exit(0);

?>
