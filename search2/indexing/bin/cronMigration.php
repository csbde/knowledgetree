<?php

/**
 * PURPOSE:
 *
 * This script is to be called periodically to migrate current indexes to Lucene.
 */

require_once(realpath('../../../config/dmsDefaults.php'));
require_once('indexing/indexerCore.inc.php');

$indexer = Indexer::get();
$indexer->migrateDocuments();

exit;
?>