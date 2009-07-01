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

$checkedout = $NavigationService->getCheckedoutDocs($repositoryId);

$feed = new KTCMISAPPFeed(KT_APP_BASE_URI, 'Checked out Documents', null, null, null, 'urn:uuid:checkedout');

foreach($checkedout as $document)
{
    $entry = $feed->newEntry();
    $objectElement = $feed->newElement('cmis:object');
    $propertiesElement = $feed->newElement('cmis:properties');

    foreach($cmisEntry['properties'] as $propertyName => $property)
    {
        $propElement = $feed->newElement('cmis:' . $property['type']);
        $propElement->appendChild($feed->newAttr('cmis:name', $propertyName));
        $feed->newField('value', CMISUtil::boolToString($property['value']), $propElement);
        $propertiesElement->appendChild($propElement);
    }

    $objectElement->appendChild($propertiesElement);
    $entry->appendChild($objectElement);
}

$entry = null;
$feed->newField('hasMoreItems', 'false', $entry, true);

$output = $feed->getAPPdoc();

?>
