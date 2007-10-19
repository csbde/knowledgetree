<?php

/**
 * PURPOSE:
 *
 * This script has the purpose of shutting down the java lucene server.
 *
 */

session_start();
require_once(realpath('../../../config/dmsDefaults.php'));

print _kt("Shutdown the Document Indexer") . "...\n";

$config = KTConfig::getSingleton();
$indexer = $config->get('indexer/coreClass');

$sure=false;
$indexall = false;
if ($argc > 0)
{
	foreach($argv as $arg)
	{
		switch (strtolower($arg))
		{
			case 'positive':
				$sure=true;
				break;
			case 'help':
				print "Usage: shutdown.php [positive]\n";
				exit;
		}
	}
}
if (!$sure)
{
	print "* " . _kt("Are you sure you want to do this? Add 'positive' as a parameter to continue.") . "\n";
	exit;
}

require_once('indexing/indexerCore.inc.php');

$indexer = Indexer::get();
$indexer->shutdown();

print "\n* " . _kt("The request to shutdown has been sent to the server. It may take a few seconds.") . "\n";


print _kt("Done.") . "\n";


?>