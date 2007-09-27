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
print "Recreate Lucene index...\n";

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
	print "* Are you sure you want to do this? Add 'positive' as a parameter to continue.\n";
	exit;
}


require_once(realpath('../../../config/dmsDefaults.php'));

$config = KTConfig::getSingleton();
$indexer = $config->get('indexer/coreClass');

if ($indexer != 'PHPLuceneIndexer')
{
	print "This script only works with the PHPLuceneIndexer.\n";
	exit;
}

require_once('indexing/indexerCore.inc.php');
require_once('indexing/indexers/PHPLuceneIndexer.inc.php');



PHPLuceneIndexer::createIndex();
print "\n* The lucene index has been recreated.\n";

if ($indexall)
{
	PHPLuceneIndexer::indexAll();
	print "\n* All documents are scheduled for indexing.\n";
}

print "Done.\n";


?>