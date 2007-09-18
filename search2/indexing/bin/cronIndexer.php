<?php

require_once(realpath('../../../config/dmsDefaults.php'));
require_once('indexing/indexerCore.inc.php');

$indexer = Indexer::get();
$indexer->indexDocuments();

?>