<?php

/**
 * PURPOSE:
 *
 * This script provides diagnosis for the various text extractors.
 */

require_once(realpath('../../../config/dmsDefaults.php'));
require_once('indexing/indexerCore.inc.php');

$indexer = Indexer::get();
$diagnoses = $indexer->diagnose();

var_dump($diagnoses);

?>