<?

require_once(realpath('../../../config/dmsDefaults.php'));
//require_once('indexing/indexerCore.inc.php');

// TODO!!
//$changed_docs = SearchHelper::getSavedSearchEvents();

die('todo');
/*

how this works -

a saved search is created.

1) any changes - ie new docs, checkins, metadata updates, etc are logged to the saved_search_events table
2) periodically, iterate through all documents - do search, and mail user results. remove the event indication.


*/
?>