<?php

/**
 * Any feed must be a valid atom Feed document and conform to the guidelines below:
1.	Updated will be the latest time the folder or its contents was updated. If unknown by the underlying repository, it should be the current time.
2.	Author/name will be the CMIS property createdBy
3.	Title will be the CMIS property name
4.	App:edited will be the CMIS property lastModifiedDate
5.	Link with relation self will be generated to return the uri of the feed
 */

/**
 * At any point where an Atom document of type Entry is sent or returned, it must be a valid Atom Entry document and conform to the guidelines below:
1.	Atom:Title will be best efforts by the repository.  The repository should chose a property closest to Title.
2.	App:edited will be CMIS:lastModifiedDate
3.	Link with relation self will be the URI that returns the Atom Entry document
4.	Published will be CMIS:createdDate
5.	Atom:author will be CMIS:creator
6.	For content tags
7.	Documents with content
a.	Leverage the src attribute to point to the same link as stream
b.	The repository SHOULD populate the summary tag with text that at best efforts represents the documents.  For example, an HTML table containing the properties and their values for simple feed readers
i.	Other (Content-less document, Folder, Relationship, Type, etc) – best efforts at generating HTML text that represents the object.  That text would normally go into the summary tag, but since there is no content, goes in the content tag.
8.	If content src is specified, the summary SHOULD contain a text or html representation of the object.
9.	Links will be used to provide URIs to CMIS functionality
10.	Link relations may be omitted if the function is not allowed and that function would not show up on getAllowableActions.
11.	Links may be omitted if the repository does not support that capability
12.	All CMIS properties will be exposed in CMIS properties tag even if they are duplicated in an atom element

When POSTing an Atom Document, the atom fields take precedence over the CMIS property field for writeable properties.  For example, atom:title will overwrite cmis:name
 */

include_once CMIS_ATOM_LIB_FOLDER . 'RepositoryService.inc.php';
include_once CMIS_ATOM_LIB_FOLDER . 'NavigationService.inc.php';
include_once CMIS_ATOM_LIB_FOLDER . 'ObjectService.inc.php';
include_once 'KT_cmis_atom_service_helper.inc.php';

// TODO auth failed response requires WWW-Authenticate: Basic realm="KnowledgeTree DMS" header

/**
 * AtomPub Service: folder
 *
 * Returns children, descendants (up to arbitrary depth) or detail for a particular folder
 *
 */
class KT_cmis_atom_service_folder extends KT_cmis_atom_service {

    public function GET_action()
    {
        $RepositoryService = new RepositoryService();
//        try {
//            $RepositoryService->startSession(self::$authData['username'], self::$authData['password']);
//        }
//        catch (Exception $e)
//        {
//            $this->headers[] = 'WWW-Authenticate: Basic realm="KnowledgeTree Secure Area"';
//            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_NOT_AUTHENTICATED, $e->getMessage());
//            $this->responseFeed = $feed;
//            return null;
//        }

        $repositories = $RepositoryService->getRepositories();
        $repositoryId = $repositories[0]['repositoryId'];

        // TODO implement full path/node separation as with Alfresco - i.e. path requests come in on path/ and node requests come in on node/
        //      path request e.g.: Root Folder/DroppedDocuments
        //      node request e.g.: F1/children
        //      node request e.g.: F2
        if (urldecode($this->params[0]) == 'Root Folder')
        {
            $folderId = CMISUtil::encodeObjectId('Folder', 1);
            $folderName = urldecode($this->params[0]);
        }
        else if ($this->params[0] == 'path')
        {
            $ktapi =& KT_cmis_atom_service_helper::getKt();
            $folderId = KT_cmis_atom_service_helper::getFolderId($this->params, $ktapi);
        }
        else
        {
            $folderId = $this->params[0];
            $ObjectService = new ObjectService(KT_cmis_atom_service_helper::getKt());

//            try {
//                $ObjectService->startSession(self::$authData['username'], self::$authData['password']);
//            }
//            catch (Exception $e)
//            {
//                $this->headers[] = 'WWW-Authenticate: Basic realm="KnowledgeTree Secure Area"';
//                $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_NOT_AUTHENTICATED, $e->getMessage());
//                $this->responseFeed = $feed;
//                return null;
//            }

            $cmisEntry = $ObjectService->getProperties($repositoryId, $folderId, false, false);
            $folderName = $cmisEntry['properties']['Name']['value'];
        //            $feed = $this->getFolderChildrenFeed($NavigationService, $repositoryId, $newObjectId, $cmisEntry['properties']['Name']['value']);
        }

        if (!empty($this->params[1]) && (($this->params[1] == 'children') || ($this->params[1] == 'descendants')))
        {
            $NavigationService = new NavigationService(KT_cmis_atom_service_helper::getKt());

//            try {
//                $NavigationService->startSession(self::$authData['username'], self::$authData['password']);
//            }
//            catch (Exception $e)
//            {
//                $this->headers[] = 'WWW-Authenticate: Basic realm="KnowledgeTree Secure Area"';
//                $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_NOT_AUTHENTICATED, $e->getMessage());
//                $this->responseFeed = $feed;
//                return null;
//            }

            $feed = $this->getFolderChildrenFeed($NavigationService, $repositoryId, $folderId, $folderName, $this->params[1]);
        }
        else
        {
            $ObjectService = new ObjectService(KT_cmis_atom_service_helper::getKt());

//            try {
//                $ObjectService->startSession(self::$authData['username'], self::$authData['password']);
//            }
//            catch (Exception $e)
//            {
//                $this->headers[] = 'WWW-Authenticate: Basic realm="KnowledgeTree Secure Area"';
//                $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_NOT_AUTHENTICATED, $e->getMessage());
//                $this->responseFeed = $feed;
//                return null;
//            }

            $feed = $this->getFolderFeed($ObjectService, $repositoryId, $folderId);
        }

        //Expose the responseFeed
        $this->responseFeed = $feed;
    }

    public function POST_action()
    {
        $RepositoryService = new RepositoryService();

//        try {
//            $RepositoryService->startSession(self::$authData['username'], self::$authData['password']);
//        }
//        catch (Exception $e)
//        {
//            $this->headers[] = 'WWW-Authenticate: Basic realm="KnowledgeTree Secure Area"';
//            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_NOT_AUTHENTICATED, $e->getMessage());
//            $this->responseFeed = $feed;
//            return null;
//        }

        $repositories = $RepositoryService->getRepositories();
        $repositoryId = $repositories[0]['repositoryId'];

        $folderId = $this->params[0];
        $title = KT_cmis_atom_service_helper::getAtomValues($this->parsedXMLContent['@children'], 'title');
        $summary = KT_cmis_atom_service_helper::getAtomValues($this->parsedXMLContent['@children'], 'summary');

        $properties = array('name' => $title, 'summary' => $summary);

        // determine whether this is a folder or a document create
        // document create will have a content tag <atom:content> or <content> containing base64 encoding of the document
        $content = KT_cmis_atom_service_helper::getAtomValues($this->parsedXMLContent['@children'], 'content');
        if (is_null($content))
            $type = 'folder';
        else
            $type = 'document';

        // TODO what if mime-type is incorrect?  CMISSpaces appears to be sending text/plain on an executable file.
        //      perhaps because the content is text/plain once base64 encoded?
        //      How to determine the actual content type?
        /*
         * <atom:entry xmlns:atom="http://www.w3.org/2005/Atom" xmlns:cmis="http://docs.oasis-open.org/ns/cmis/core/200901">
         * <atom:title>setup.txt</atom:title>
         * <atom:summary>setup.txt</atom:summary>
         * <atom:content type="text/plain">dGhpcyBiZSBzb21lIHRlc3QgY29udGVudCBmb3IgYSBkb2N1bWVudCwgeWVzPw==</atom:content>
         * <cmis:object>
         * <cmis:properties>
         * <cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>document</cmis:value></cmis:propertyString>
         * </cmis:properties>
         * </cmis:object>
         * </atom:entry>
         */

        $cmisObjectProperties = KT_cmis_atom_service_helper::getCmisProperties($this->parsedXMLContent['@children']['cmis:object']
                                                                                                      [0]['@children']['cmis:properties']
                                                                                                      [0]['@children']);

        $ObjectService = new ObjectService(KT_cmis_atom_service_helper::getKt());

//        try {
//            $ObjectService->startSession(self::$authData['username'], self::$authData['password']);
//        }
//        catch (Exception $e)
//        {
//            $this->headers[] = 'WWW-Authenticate: Basic realm="KnowledgeTree Secure Area"';
//            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_NOT_AUTHENTICATED, $e->getMessage());
//            $this->responseFeed = $feed;
//            return null;
//        }

        if ($type == 'folder')
            $newObjectId = $ObjectService->createFolder($repositoryId, ucwords($cmisObjectProperties['ObjectTypeId']), $properties, $folderId);
        else
            $newObjectId = $ObjectService->createDocument($repositoryId, ucwords($cmisObjectProperties['ObjectTypeId']), $properties, $folderId, $content);

        // check if returned Object Id is a valid CMIS Object Id
        $dummy = CMISUtil::decodeObjectId($newObjectId, $typeId);
        if ($typeId != 'Unknown')
        {
            $this->setStatus(self::STATUS_CREATED);
            if ($type == 'folder')
            {
                $feed = $this->getFolderFeed($ObjectService, $repositoryId, $newObjectId);
            }
            else
            {
                $NavigationService = new NavigationService(KT_cmis_atom_service_helper::getKt());

//                try {
//                    $NavigationService->startSession(self::$authData['username'], self::$authData['password']);
//                }
//                catch (Exception $e)
//                {
//                    $this->headers[] = 'WWW-Authenticate: Basic realm="KnowledgeTree Secure Area"';
//                    $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_NOT_AUTHENTICATED, $e->getMessage());
//                    $this->responseFeed = $feed;
//                    return null;
//                }

                $cmisEntry = $ObjectService->getProperties($repositoryId, $folderId, false, false);
                $feed = $this->getFolderChildrenFeed($NavigationService, $repositoryId, $folderId, $cmisEntry['properties']['Name']['value']);
            }
        }
        else
        {
            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_SERVER_ERROR, $newObjectId['message']);
        }

        //Expose the responseFeed
        $this->responseFeed = $feed;
    }

    /**
     * Retrieves children/descendants of the specified folder
     * TODO this currently only works in children mode, add descendants
     *
     * @param string $repositoryId
     * @param string $folderId folder id for which children/descendants are requested
     * @param string $feedType children or descendants
     * @return string CMIS AtomPub feed
     */
    private function getFolderChildrenFeed($NavigationService, $repositoryId, $folderId, $folderName, $feedType = 'children')
    {
        if ($feedType == 'children')
        {
            $entries = $NavigationService->getChildren($repositoryId, $folderId, false, false);
        }
        else if ($feedType == 'descendants')
        {
            $entries = $NavigationService->getDescendants($repositoryId, $folderId, false, false);
        }
        else
        {
            // error, we shouldn't be here, if we are then the wrong service/function was called
        }

        // $baseURI=NULL,$title=NULL,$link=NULL,$updated=NULL,$author=NULL,$id=NULL
        $feed = new KT_cmis_atom_responseFeed(CMIS_APP_BASE_URI, $folderName . ' ' . ucwords($feedType), null, null, null,
                                              'urn:uuid:' . $folderId . '-' . $feedType);

        // TODO dynamic?
        $feedElement = $feed->newField('author');
        $element = $feed->newField('name', 'System', $feedElement);
        $feed->appendChild($feedElement);

        // TODO get actual most recent update time, only use current if no other available
        $feed->appendChild($feed->newElement('updated', KT_cmis_atom_service_helper::formatDatestamp()));
        
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','source'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/folder/' . $folderId));
        $feed->appendChild($link);

        foreach($entries as $cmisEntry)
        {
            KT_cmis_atom_service_helper::createObjectEntry($feed, $cmisEntry, $folderName);
        }

        // <cmis:hasMoreItems>false</cmis:hasMoreItems>

        // global $childrenFeed
        // $output = $childrenFeed[0];
        // $output = $childrenFeed[1];

        return $feed;
    }

    /**
     * Retrieves data about a specific folder
     *
     * @param object $ObjectService The CMIS service
     * @param string $repositoryId
     * @param string $folderId
     * @return string CMIS AtomPub feed
     */
    private function getFolderFeed($ObjectService, $repositoryId, $folderId)
    {
        $cmisEntry = $ObjectService->getProperties($repositoryId, $folderId, false, false);

        $feed = new KT_cmis_atom_responseFeed(CMIS_APP_BASE_URI, $cmisEntry['properties']['ObjectTypeId']['value'], null, null, null,
                                              'urn:uuid:' . $cmisEntry['properties']['ObjectId']['value']);

        KT_cmis_atom_service_helper::createObjectEntry($feed, $cmisEntry, $folderName);
        //        // <cmis:hasMoreItems>false</cmis:hasMoreItems>
        //        // global $folderFeed;
        //        // $outputs =

        return $feed;
    }

}

/**
 * AtomPub Service: types
 *
 * Returns a list of supported object types
 *
 */
class KT_cmis_atom_service_types extends KT_cmis_atom_service {

    public function GET_action()
    {
        $RepositoryService = new RepositoryService();

//        try {
//            $RepositoryService->startSession(self::$authData['username'], self::$authData['password']);
//        }
//        catch (Exception $e)
//        {
//            $this->headers[] = 'WWW-Authenticate: Basic realm="KnowledgeTree Secure Area"';
//            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_NOT_AUTHENTICATED, $e->getMessage());
//            $this->responseFeed = $feed;
//            return null;
//        }

        // fetch repository id
        $repositories = $RepositoryService->getRepositories();
        $repositoryId = $repositories[0]['repositoryId'];

        $types = $RepositoryService->getTypes($repositoryId);
        $type = ((empty($this->params[0])) ? 'all' : $this->params[0]);
        $feed = KT_cmis_atom_service_helper::getTypeFeed($type, $types);

        //Expose the responseFeed
        $this->responseFeed = $feed;
    }
    
}

/**
 * AtomPub Service: type
 *
 * Returns the type defintion for the selected type
 *
 */
class KT_cmis_atom_service_type extends KT_cmis_atom_service {

    public function GET_action()
    {
        $RepositoryService = new RepositoryService();

//        try {
//            $RepositoryService->startSession(self::$authData['username'], self::$authData['password']);
//        }
//        catch (Exception $e)
//        {
//            $this->headers[] = 'WWW-Authenticate: Basic realm="KnowledgeTree Secure Area"';
//            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_NOT_AUTHENTICATED, $e->getMessage());
//            $this->responseFeed = $feed;
//            return null;
//        }

        // fetch repository id
        $repositories = $RepositoryService->getRepositories();
        $repositoryId = $repositories[0]['repositoryId'];

        if (!isset($this->params[1])) {
        // For easier return in the wanted format, we call getTypes instead of getTypeDefinition.
        // Calling this with a single type specified returns an array containing the definition of
        // just the requested type.
        // NOTE could maybe be more efficient to call getTypeDefinition direct and then place in
        //      an array on this side?  or directly expose the individual entry response code and
        //      call directly from here rather than via getTypeFeed.
            $type = ucwords($this->params[0]);
            $types = $RepositoryService->getTypes($repositoryId, $type);
            $feed = KT_cmis_atom_service_helper::getTypeFeed($type, $types);
        }
        else {
        // TODO dynamic dates, as needed everywhere
        // NOTE children of types not yet implemented and we don't support any non-basic types at this time
            $feed = $this->getTypeChildrenFeed($this->params[1]);
        }

        //Expose the responseFeed
        $this->responseFeed=$feed;
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
    private function getTypeChildrenFeed()
    {
    //Create a new response feed
    // $baseURI=NULL,$title=NULL,$link=NULL,$updated=NULL,$author=NULL,$id=NULL
        $feed = new KT_cmis_atom_responseFeed(CMIS_APP_BASE_URI, 'Child Types of ' . ucwords($this->params[0]), null, null, null,
            $this->params[0] . '-children');

        // TODO actually fetch child types - to be implemented when we support child types in the API

        // id

        // links
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','first'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . 'type/' . $this->params[0] . '/' . $this->params[1] . '?pageNo=1&amp;pageSize=0'));
        $link->appendChild($feed->newAttr('type', 'application/atom+xml;type=feed'));

        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','last'));
        // TODO set page number correctly - to be done when we support paging the the API
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . 'type/' . $this->params[0] . '/' . $this->params[1] . '?pageNo=1&amp;pageSize=0'));
        $link->appendChild($feed->newAttr('type', 'application/atom+xml;type=feed'));

        // TODO actual dynamic listing, currently we have no objects with which to test

        // TODO
        //        <updated>2009-06-23T13:40:32.786+02:00</updated>
        //        <cmis:hasMoreItems>false</cmis:hasMoreItems>
/*
        // TODO need to create this dynamically now, will no longer work with static output
        $output = '<?xml version="1.0" encoding="UTF-8"?>
                    <feed xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns:cmis="http://docs.oasis-open.org/ns/cmis/core/200901">
                    <id>urn:uuid:type-' . $type . '-children</id>
                    <link rel="self" href="' . CMIS_APP_BASE_URI . 'type/document/children"/>
                    <link rel="first" href="' . CMIS_APP_BASE_URI . 'type/document/children?pageNo=1&amp;pageSize=0&amp;guest=" type="application/atom+xml;type=feed"/>
                    <link rel="last" href="' . CMIS_APP_BASE_URI . 'type/document/children?pageNo=1&amp;pageSize=0&amp;guest=" type="application/atom+xml;type=feed"/>
                    <title>Child types of ' . $type . '</title>
                    <updated>2009-06-23T13:40:32.786+02:00</updated>
                    <cmis:hasMoreItems>false</cmis:hasMoreItems>
                    </feed>';
*/
        return $feed;
    }

}

/**
 * AtomPub Service: checkedout
 *
 * Returns a list of checked out documents for the logged in user
 *
 */
// NOTE this is always an empty document, underlying API code still to be implemented
class KT_cmis_atom_service_checkedout extends KT_cmis_atom_service {

    public function GET_action()
    {
        $RepositoryService = new RepositoryService();
        $NavigationService = new NavigationService(KT_cmis_atom_service_helper::getKt());

//        try {
//            $NavigationService->startSession(self::$authData['username'], self::$authData['password']);
//        }
//        catch (Exception $e)
//        {
//            $this->headers[] = 'WWW-Authenticate: Basic realm="KnowledgeTree Secure Area"';
//            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_NOT_AUTHENTICATED, $e->getMessage());
//            $this->responseFeed = $feed;
//            return null;
//        }

        $repositories = $RepositoryService->getRepositories();
        $repositoryId = $repositories[0]['repositoryId'];

        $checkedout = $NavigationService->getCheckedoutDocs($repositoryId);

        //Create a new response feed
        $feed = new KT_cmis_atom_responseFeed(CMIS_APP_BASE_URI, 'Checked out Documents', null, null, null, 'urn:uuid:checkedout');

        foreach($checkedout as $document)
        {
            $entry = $feed->newEntry();
            $objectElement = $feed->newElement('cmis:object');
            $propertiesElement = $feed->newElement('cmis:properties');

            foreach($cmisEntry['properties'] as $propertyName => $property)
            {
                $propElement = $feed->newElement('cmis:' . $property['type']);
                $propElement->appendChild($feed->newAttr('cmis:name', $propertyName));
                $feed->newField('cmis:value', CMISUtil::boolToString($property['value']), $propElement);
                $propertiesElement->appendChild($propElement);
            }

            $objectElement->appendChild($propertiesElement);
            $entry->appendChild($objectElement);
        }

        $entry = null;
        $feed->newField('hasMoreItems', 'false', $entry, true);

        //Expose the responseFeed
        $this->responseFeed = $feed;
    }

}

/**
 * AtomPub Service: document
 *
 * Returns detail on a particular document
 *
 */
class KT_cmis_atom_service_document extends KT_cmis_atom_service {

    public function GET_action()
    {
        $RepositoryService = new RepositoryService();
        $ObjectService = new ObjectService(KT_cmis_atom_service_helper::getKt());

//        try {
//            $ObjectService->startSession(self::$authData['username'], self::$authData['password']);
//        }
//        catch (Exception $e)
//        {
//            $this->headers[] = 'WWW-Authenticate: Basic realm="KnowledgeTree Secure Area"';
//            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_NOT_AUTHENTICATED, $e->getMessage());
//            $this->responseFeed = $feed;
//            return null;
//        }

        $repositories = $RepositoryService->getRepositories();
        $repositoryId = $repositories[0]['repositoryId'];

        $cmisEntry = $ObjectService->getProperties($repositoryId, $this->params[0], false, false);

        //Create a new response feed
        $feed = new KT_cmis_atom_responseFeed(CMIS_APP_BASE_URI, $cmisEntry['properties']['ObjectTypeId']['value'], null, null, null,
                                              'urn:uuid:' . $cmisEntry['properties']['ObjectId']['value']);

        KT_cmis_atom_service_helper::createObjectEntry($feed, $cmisEntry, $cmisEntry['properties']['ParentId']['value']);

        // <cmis:hasMoreItems>false</cmis:hasMoreItems>

        //        global $docFeed;
        //        $output = $docFeed;

        //Expose the responseFeed
        $this->responseFeed=$feed;
    }
    
}

?>