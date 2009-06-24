<?php

/**
 * Type access functions for CMIS AtomPub
 */

include 'services/cmis/RepositoryService.inc.php';

$RepositoryService = new RepositoryService();
// technically do not need to log in to access this information
// TODO consider requiring authentication even to access basic repository information
$RepositoryService->startSession($username, $password);

// fetch repository id
$repositories = $RepositoryService->getRepositories();
$repositoryId = $repositories[0]['repositoryId'];

switch($arg)
{
    case 'type':
        {
            if (!isset($query[3]))
            {
                $type = ucwords($query[2]);
                $types = $RepositoryService->getTypes($repositoryId, $type);
                $output = CMISTypeFeed::getTypeFeed($type, $types);
            }
            else
            {
                // TODO dynamic dates, as needed everywhere
                // NOTE children of types not yet implemented and we don't support any non-basic types at this time
                $output = CMISTypeFeed::getTypeChildrenFeed($query[2]);
            }
        }
        break;
    case 'types':
        $types = $RepositoryService->getTypes($repositoryId);
        $output = CMISTypeFeed::getTypeFeed('All Types', $types);
		break;
}

/**
 * Class to generate CMIS AtomPub feeds for Type responses
 */

class CMISTypeFeed {

    /**
     * Retrieves the list of types as a CMIS AtomPub feed
     *
     * @param string $typeDef Type requested - 'All Types' indicates a listing, else only a specific type
     * @param array $types The types found
     * @return string CMIS AtomPub feed
     */
    static public function getTypeFeed($typeDef, $types)
    {
        $feed = new KTCMISAPPFeed(KT_APP_BASE_URI, $typeDef, null, null, null, 'urn:uuid:type-' . $query[2]);

        foreach($types as $type)
        {
            $entry = $feed->newEntry();
            $feed->newId('urn:uuid:type-' . strtolower($type['typeId']), $entry);

            // links
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','self'));
            $link->appendChild($feed->newAttr('href', CMIS_BASE_URI . 'type/' . strtolower($type['typeId'])));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-type'));
            $link->appendChild($feed->newAttr('href', CMIS_BASE_URI . 'type/' . strtolower($type['typeId'])));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-children'));
            $link->appendChild($feed->newAttr('href', CMIS_BASE_URI . 'type/' . strtolower($type['typeId']) . '/children'));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-descendants'));
            $link->appendChild($feed->newAttr('href', CMIS_BASE_URI . 'type/' . strtolower($type['typeId']) . '/descendants'));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-repository'));
            $link->appendChild($feed->newAttr('href', CMIS_BASE_URI . 'repository'));
            $entry->appendChild($link);

            $entry->appendChild($feed->newElement('summary', $type['typeId'] . ' Type'));
            $entry->appendChild($feed->newElement('title', $type['typeId']));

            // main CMIS entry
            $feedElement = $feed->newElement('cmis:' . strtolower($type['typeId']) . 'Type');
            foreach($type as $property => $value)
            {
                $feed->newField($property, CMISUtil::boolToString($value), $feedElement);
            }

            $entry->appendChild($feedElement);
        }

        $output = $feed->getAPPdoc();

        return $output;
    }

    /**
     * Retrieves a list of child types for the supplied type
     *
     * NOTE this currently returns a hard coded empty list, since we do not currently support child types
     * TODO make dynamic if/when we support checking for child types (we don't actually need to support child types themselves)
     *
     * @param string $type
     * @return string CMIS AtomPub feed
     */
    static public function getTypeChildrenFeed($type)
    {
        $output = '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns:cmis="http://www.cmis.org/2008/05">
<id>urn:uuid:type-' . $type . '-children</id>
<link rel="self" href="' . CMIS_BASE_URI . 'type/document/children"/>
<link rel="first" href="' . CMIS_BASE_URI . 'type/document/children?pageNo=1&amp;pageSize=0&amp;guest=" type="application/atom+xml;type=feed"/>
<link rel="last" href="' . CMIS_BASE_URI . 'type/document/children?pageNo=1&amp;pageSize=0&amp;guest=" type="application/atom+xml;type=feed"/>
<title>Child types of ' . $type . '</title>
<updated>2009-06-23T13:40:32.786+02:00</updated>
<cmis:hasMoreItems>false</cmis:hasMoreItems>
</feed>';

        return $output;
    }

}

?>