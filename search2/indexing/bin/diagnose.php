<?php

/**
 * PURPOSE:
 *
 * This script provides diagnosis for the various text extractors.
 */

require_once(realpath('../../../config/dmsDefaults.php'));
require_once('indexing/indexerCore.inc.php');

print _kt("Diagnosing the text extractors") . "...\n";

$indexer = Indexer::get();
$diagnoses = $indexer->diagnoseExtractors();

if (count($diagnoses) == 0)
{
	print _kt("There don't appear to be any problems.") ." \n";
}
else
{
	foreach($diagnoses as $key=>$value)
	{
		$name = $value['name'];
		$diagnosis = $value['diagnosis'];

		print "\n" . _kt('Extractor:') ." $name ($key)\n";
		print "* $diagnosis\n";
	}
}

print "\n" . _kt("Done.") . "\n";

?>