<?php

/**
 * NOTES:
 *
 * 1. It would be nice if this (as well as other sections of the site) didn't have to include dmsDefaults.
 *    I think we should look into a minimal version of dmsDefaults which can be extended to create the full version.
 *    The minimal version can then be loaded for instances like this.
 */

$start = strpos(dirname(__FILE__), 'plugins');
$filePath = substr(dirname(__FILE__), 0, $start);
require_once($filePath . 'config/dmsDefaults.php');

$query = KTUtil::arrayGet($_REQUEST, 'q');
$documentId = KTUtil::arrayGet($_REQUEST, 'documentId');
if (empty($query) || empty($documentId)) {
    echo '';
    exit(0);
}

// Always return typed input as selectable option.
$tags[] = array('id' => $query, 'name' => $query);

$sql = "SELECT tw.id, tw.tag FROM tag_words tw
        LEFT JOIN document_tags dt
        ON document_id = $documentId AND dt.tag_id = tw.id
        WHERE dt.tag_id IS NULL AND tw.tag LIKE '%$query%'";
$tagResult = DBUtil::getResultArray(array($sql));
foreach ($tagResult as $id => $tag) {
    $tags[] = array('id' => $tag['tag'], 'name' => $tag['tag']);
}

echo json_encode($tags);
exit(0);

?>
