<?php

/**
 * PURPOSE:
 *
 * This script optimises the lucene index.
 */

require_once(realpath('../../../config/dmsDefaults.php'));
require_once('indexing/indexerCore.inc.php');

print _kt("Optimising Lucene index") . "...\n";

$indexer = Indexer::get();
$indexer->optimise();

print _kt("Done.") . "\n";

?>