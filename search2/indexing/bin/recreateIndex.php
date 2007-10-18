<?php

/**
 * PURPOSE:
 *
 * This script has the purpose of recreating the lucene index.
 *
 * It will also schedule all content for re-indexing.
 *
 */

session_start();
require_once(realpath('../../../config/dmsDefaults.php'));

print _kt("Recreate Lucene index") . "...\n";

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
			case 'indexall':
				$indexall=true;
				break;
			case 'help':
				print "Usage: recreateIndex.php [positive] [indexall]\n";
				exit;
		}
	}
}
if (!$sure)
{
	print "* " . _kt("Are you sure you want to do this? Add 'positive' as a parameter to continue.") . "\n";
	exit;
}



$config = KTConfig::getSingleton();
$indexer = $config->get('indexer/coreClass');

if ($indexer != 'PHPLuceneIndexer')
{
	print _kt("This script only works with the PHPLuceneIndexer.") . "\n";
	exit;
}

require_once('indexing/indexerCore.inc.php');
require_once('indexing/indexers/PHPLuceneIndexer.inc.php');



PHPLuceneIndexer::createIndex();
print "\n* " . _kt("The lucene index has been recreated.") . "\n";

if ($indexall)
{
	PHPLuceneIndexer::indexAll();
	print "\n* " . _kt("All documents are scheduled for indexing.") . "\n";
}

print _kt("Done.") . "\n";


?>