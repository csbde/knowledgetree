<?php

/**
 * PURPOSE:
 *
 * This script has the purpose of recreating the lucene index.
 *
 * It will also schedule all content for re-indexing.
 *
 */

if (true)
{
	die('are you sure?');
}

session_start();
require_once(realpath('../../../config/dmsDefaults.php'));
require_once('indexing/indexerCore.inc.php');
require_once('indexing/indexers/PHPLuceneIndexer.inc.php');

PHPLuceneIndexer::createIndex();
PHPLuceneIndexer::indexAll();

print "The lucene index has been deleted. All documents are now in the queue.\n";

?>