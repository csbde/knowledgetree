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

$output = '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns:cmis="http://www.cmis.org/2008/05">
<id>urn:uuid:checkedout</id>
<link rel="self" href="' . CMIS_BASE_URI . 'checkedout"/>
<title>Checked out Documents</title>
<cmis:hasMoreItems>false</cmis:hasMoreItems>
</feed>';

?>
