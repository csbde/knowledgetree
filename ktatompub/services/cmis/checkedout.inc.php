<?php

/**
 * Retrieves the list of checked out documents and returns it as an AtomPub feed
 *
 * NOTE since we don't yet support getCheckedOutDocuments in the CMIS API, this code
 * returns a static empty feed
 *
 * TODO when the CMIS API functionality exists, create the feed dynamically
 */

include 'services/cmis/RepositoryService.inc.php';
include 'services/cmis/NavigationService.inc.php';

$RepositoryService = new RepositoryService();
$NavigationService = new NavigationService();

$NavigationService->startSession($username, $password);

$repositories = $RepositoryService->getRepositories();
$repositoryId = $repositories[0]['repositoryId'];

$feed = new KTCMISAPPFeed(KT_APP_BASE_URI, 'Checked out Documents', null, null, null, 'urn:uuid:checkedout');

$entry = null;
$feed->newField('hasMoreItems', 'false', $entry, true);

$output = $feed->getAPPdoc();

?>
