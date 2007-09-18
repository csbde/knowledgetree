<?php

if (true)
{
	die('are you sure?');
}

require_once(realpath('../../../config/dmsDefaults.php'));
require_once('indexing/indexerCore.inc.php');
require_once('indexing/indexers/PHPLuceneIndexer.inc.php');



// this is a function specific to PHP
PHPLuceneIndexer::createIndex();
PHPLuceneIndexer::indexAll();

print "The lucene index has been deleted. All documents are now in the queue.\n";

?>