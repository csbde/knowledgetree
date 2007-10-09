<?php

/**
 * PURPOSE:
 *
 * The purpose of this script is to register types.
 *
 * Usage: registerTypes.php [clear]
 *
 * If 'clear' is specified, mime type mappings will be cleared.
 *
 */

session_start();
print _kt("Registering Extractor mapping to Mime types") . "...\n";

require_once(realpath('../../../config/dmsDefaults.php'));

$config = KTConfig::getSingleton();
$indexer = $config->get('indexer/coreClass');

if ($indexer != 'PHPLuceneIndexer')
{
	print _kt("This script only works with the PHPLuceneIndexer.") . "\n";
	exit;
}

require_once('indexing/indexerCore.inc.php');

$clear=false;
if ($argc > 0)
{
	foreach($argv as $arg)
	{
		switch (strtolower($arg))
		{
			case 'clear':
				$clear=true;
				print "* " . _kt("Clearing mime type associations.") . "\n";
				break;
			case 'help':
				print "Usage: registerTypes.php [clear]\n";
				exit;
		}
		if (strtolower($arg) == 'clear')
		{
			$clear=true;
		}
	}
}

$indexer = Indexer::get();
$indexer->registerTypes($clear);

print _kt("Done.") . "\n";
?>