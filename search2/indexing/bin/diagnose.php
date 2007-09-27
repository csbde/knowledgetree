<?php

/**
 * PURPOSE:
 *
 * This script provides diagnosis for the various text extractors.
 */

require_once(realpath('../../../config/dmsDefaults.php'));
require_once('indexing/indexerCore.inc.php');

print "Diagnosing the text extractors...\n";

$indexer = Indexer::get();
$diagnoses = $indexer->diagnose();

if (count($diagnoses) == 0)
{
	print "There don't appear to be any problems.\n";
}
else
{
	foreach($diagnoses as $key=>$value)
	{
		$name = $value['name'];
		$diagnosis = $value['diagnosis'];

		print "\nExtractor: $name ($key)\n";
		print "* $diagnosis\n";
	}
}

print "\nDone.\n";

?>