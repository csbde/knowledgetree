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
require_once(realpath('../../../config/dmsDefaults.php'));

print _kt("Registering Extractor mapping to Mime types") . "...\n";

$config = KTConfig::getSingleton();
$indexer = $config->get('indexer/coreClass');

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