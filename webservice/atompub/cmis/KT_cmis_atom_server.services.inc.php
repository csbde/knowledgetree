<?php

include_once CMIS_ATOM_LIB_FOLDER . 'RepositoryService.inc.php';
include_once CMIS_ATOM_LIB_FOLDER . 'NavigationService.inc.php';
include_once CMIS_ATOM_LIB_FOLDER . 'ObjectService.inc.php';
include_once 'KT_cmis_atom_service_helper.inc.php';

// TODO response if failed auth, need generic response which can be used by all code

/**
 * AtomPub Service: folder
 *
 * Returns children, descendants (up to arbitrary depth) or detail for a particular folder
 *
 */
class KT_cmis_atom_service_folder extends KT_cmis_atom_service
{
	public function GET_action()
    {
        $RepositoryService = new RepositoryService();
        $RepositoryService->startSession(self::$authData['username'], self::$authData['password']);
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
        // this is a bit of a hack, but then it's to accomodate a bit of a hack to work with the knowledgetree/drupal cmis modules...
        else if ($this->params[0] == 'path')
        {
            $ktapi =& $RepositoryService->getInterface();
            $folderId = KT_cmis_atom_service_helper::getFolderId($this->params, $ktapi);
        }
        else
        {
            $folderId = $this->params[0];
            $ObjectService = new ObjectService();
            $ObjectService->startSession(self::$authData['username'], self::$authData['password']);
            $cmisEntry = $ObjectService->getProperties($repositoryId, $folderId, false, false);
            $folderName = $cmisEntry['properties']['Name']['value'];
//            $feed = $this->getFolderChildrenFeed($NavigationService, $repositoryId, $newObjectId, $cmisEntry['properties']['Name']['value']);
        }

        if (!empty($this->params[1]) && (($this->params[1] == 'children') || ($this->params[1] == 'descendants')))
        {
            $NavigationService = new NavigationService();
            $NavigationService->startSession(self::$authData['username'], self::$authData['password']);

            $feed = $this->getFolderChildrenFeed($NavigationService, $repositoryId, $folderId, $folderName, $this->params[1]);
        }
        else
        {
            $ObjectService = new ObjectService();
            $ObjectService->startSession(self::$authData['username'], self::$authData['password']);

            $feed = $this->getFolderFeed($ObjectService, $repositoryId, $folderId);
        }

		//Expose the responseFeed
		$this->responseFeed = $feed;
	}
    
	public function POST_action()
    {
//        $username = $password = 'admin';
        $RepositoryService = new RepositoryService();
        $RepositoryService->startSession(self::$authData['username'], self::$authData['password']);
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
         * <atom:entry xmlns:atom="http://www.w3.org/2005/Atom" xmlns:cmis="http://www.cmis.org/2008/05">
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

        $ObjectService = new ObjectService();
        $ObjectService->startSession(self::$authData['username'], self::$authData['password']);
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
                $NavigationService = new NavigationService();
                $NavigationService->startSession(self::$authData['username'], self::$authData['password']);
                $cmisEntry = $ObjectService->getProperties($repositoryId, $folderId, false, false);
                $feed = $this->getFolderChildrenFeed($NavigationService, $repositoryId, $folderId, $cmisEntry['properties']['Name']['value']);
            }
        }
        else
        {
            $this->setStatus(self::STATUS_SERVER_ERROR);
            $feed = new KT_cmis_atom_responseFeed(CMIS_APP_BASE_URI, 'Error: ' . self::STATUS_SERVER_ERROR);
            $entry = $feed->newEntry();
            $feed->newField('error', $newObjectId['message'], $entry);
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
                                              'urn:uuid:' . $folderName . '-' . $feedType);

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
class KT_cmis_atom_service_types extends KT_cmis_atom_service
{
	public function GET_action()
    {
//        $username = $password = 'admin';
        $RepositoryService = new RepositoryService();
        // technically do not need to log in to access this information
        // TODO consider requiring authentication even to access basic repository information
        $RepositoryService->startSession(self::$authData['username'], self::$authData['password']);

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
class KT_cmis_atom_service_type extends KT_cmis_atom_service
{
	public function GET_action()
    {
//        $username = $password = 'admin';
        $RepositoryService = new RepositoryService();
        // technically do not need to log in to access this information
        // TODO consider requiring authentication even to access basic repository information
        $RepositoryService->startSession(self::$authData['username'], self::$authData['password']);

        // fetch repository id
        $repositories = $RepositoryService->getRepositories();
        $repositoryId = $repositories[0]['repositoryId'];
        
        if (!isset($this->params[1]))
        {
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
        else
        {
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
                    <feed xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns:cmis="http://www.cmis.org/2008/05">
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
class KT_cmis_atom_service_checkedout extends KT_cmis_atom_service
{
	public function GET_action()
    {
//        $username = $password = 'admin';
        $RepositoryService = new RepositoryService();
        $NavigationService = new NavigationService();

        $NavigationService->startSession(self::$authData['username'], self::$authData['password']);

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
class KT_cmis_atom_service_document extends KT_cmis_atom_service
{
	public function GET_action()
    {
//        $username = $password = 'admin';
        $RepositoryService = new RepositoryService();

        $ObjectService = new ObjectService();
        $ObjectService->startSession(self::$authData['username'], self::$authData['password']);
        
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

$childrenFeed[] = '<?xml version="1.0" encoding="utf-8"?>
        <feed xmlns="http://www.w3.org/2005/Atom" xmlns:cmis="http://www.cmis.org/2008/05">
         <id>urn:uuid:28537649-8af2-4c74-aa92-5d8bbecac9ce-children</id>
         <link rel="self" href="http://10.33.4.34/ktatompub/?/cmis/folder/F1/children"/>
         <title>Root Folder Children</title>
         <entry>
          <id>urn:uuid:86224486-b7ae-4074-a793-82cd259b0026-folder</id>
          <link rel="cmis-children" href="http://10.33.4.34/ktatompub/?cmis/folder/F2/children"/>

          <link rel="cmis-descendants" href="http://10.33.4.34/ktatompub/?cmis/folder/F2/descendants"/>
          <link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/folder"/>
          <link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
          <summary>DroppedDocuments</summary>
          <title>DroppedDocuments</title>
          <cmis:object>
           <cmis:properties>
            <cmis:propertyId cmis:name="ObjectId">

             <cmis:value>F2</cmis:value>
            </cmis:propertyId>
            <cmis:propertyString cmis:name="ObjectTypeId">
             <cmis:value>Folder</cmis:value>
            </cmis:propertyString>
            <cmis:propertyString cmis:name="Name">
             <cmis:value>DroppedDocuments</cmis:value>

            </cmis:propertyString>
           </cmis:properties>
          </cmis:object>
         </entry>
         <entry>
          <id>urn:uuid:86224486-b7ae-4074-a793-82cd259b0026-folder</id>
          <link rel="cmis-children" href="http://10.33.4.34/ktatompub/?cmis/folder/F4/children"/>
          <link rel="cmis-descendants" href="http://10.33.4.34/ktatompub/?cmis/folder/F4/descendants"/>

          <link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/folder"/>
          <link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
          <summary>Test KT Folder</summary>
          <title>Test KT Folder</title>
          <cmis:object>
           <cmis:properties>
            <cmis:propertyId cmis:name="ObjectId">
             <cmis:value>F4</cmis:value>

            </cmis:propertyId>
            <cmis:propertyString cmis:name="ObjectTypeId">
             <cmis:value>Folder</cmis:value>
            </cmis:propertyString>
            <cmis:propertyString cmis:name="Name">
             <cmis:value>Test KT Folder</cmis:value>
            </cmis:propertyString>
           </cmis:properties>

          </cmis:object>
         </entry>
        <entry>
         <author><name>admin</name></author>
        <content type="application/pdf" src="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><id>urn:uuid:2df9d676-f173-47bb-8ec1-41fa1186b66d</id>
        <link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d"/>

        <link rel="enclosure" type="application/pdf" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d"/>
        <link rel="edit-media" type="application/pdf" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/permissions"/>
        <link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/associations"/>
        <link rel="cmis-parents" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/parents"/>
        <link rel="cmis-allversions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/versions"/>
        <link rel="cmis-stream" type="application/pdf" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/document"/>
        <link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
        <published>2009-06-23T09:40:47.889+02:00</published>
        <summary></summary>
        <title>h4555-cmis-so.pdf</title>
        <updated>2009-06-23T09:40:58.524+02:00</updated>

        <cmis:object>
        <cmis:properties>
        <cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d</cmis:value></cmis:propertyId>
        <cmis:propertyString cmis:name="BaseType"><cmis:value>document</cmis:value></cmis:propertyString>
        <cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>document</cmis:value></cmis:propertyString>
        <cmis:propertyString cmis:name="CreatedBy"><cmis:value>admin</cmis:value></cmis:propertyString>

        <cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-23T09:40:47.889+02:00</cmis:value></cmis:propertyDateTime>
        <cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>admin</cmis:value></cmis:propertyString>
        <cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-23T09:40:58.524+02:00</cmis:value></cmis:propertyDateTime>
        <cmis:propertyString cmis:name="Name"><cmis:value>h4555-cmis-so.pdf</cmis:value></cmis:propertyString>
        <cmis:propertyBoolean cmis:name="IsImmutable"><cmis:value>false</cmis:value></cmis:propertyBoolean>

        <cmis:propertyBoolean cmis:name="IsLatestVersion"><cmis:value>true</cmis:value></cmis:propertyBoolean>
        <cmis:propertyBoolean cmis:name="IsMajorVersion"><cmis:value>false</cmis:value></cmis:propertyBoolean>
        <cmis:propertyBoolean cmis:name="IsLatestMajorVersion"><cmis:value>false</cmis:value></cmis:propertyBoolean>
        <cmis:propertyString cmis:name="VersionLabel"/>
        <cmis:propertyId cmis:name="VersionSeriesId"><cmis:value>workspace://SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d</cmis:value></cmis:propertyId>

        <cmis:propertyBoolean cmis:name="IsVersionSeriesCheckedOut"><cmis:value>false</cmis:value></cmis:propertyBoolean>
        <cmis:propertyString cmis:name="VersionSeriesCheckedOutBy"/>
        <cmis:propertyId cmis:name="VersionSeriesCheckedOutId"/>
        <cmis:propertyString cmis:name="CheckinComment"/>
        <cmis:propertyInteger cmis:name="ContentStreamLength"><cmis:value>343084</cmis:value></cmis:propertyInteger>
        <cmis:propertyString cmis:name="ContentStreamMimeType"><cmis:value>application/pdf</cmis:value></cmis:propertyString>
        <cmis:propertyString cmis:name="ContentStreamFilename"><cmis:value>h4555-cmis-so.pdf</cmis:value></cmis:propertyString>

        <cmis:propertyString cmis:name="ContentStreamURI"><cmis:value>http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf</cmis:value></cmis:propertyString>
        </cmis:properties>
        </cmis:object>


         </entry>
        </feed>';

        $childrenFeed[] = '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns:cmis="http://www.cmis.org/2008/05" xmlns:alf="http://www.alfresco.org" xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/">
<author><name>System</name></author>
<generator version="3.0.0 (Stable 1526)">Alfresco (Labs)</generator>
<icon>http://10.33.4.34:8080/alfresco/images/logo/AlfrescoLogo16.ico</icon>
<id>urn:uuid:28537649-8af2-4c74-aa92-5d8bbecac9ce-children</id>
<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/path/workspace/SpacesStore/Company%20Home/children"/>
<link rel="cmis-source" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="first" href="http://10.33.4.34:8080/alfresco/service/api/path/workspace/SpacesStore/Company%20Home/children?pageNo=1&amp;pageSize=0&amp;guest=&amp;format=atomfeed" type="application/atom+xml;type=feed"/>

<link rel="last" href="http://10.33.4.34:8080/alfresco/service/api/path/workspace/SpacesStore/Company%20Home/children?pageNo=1&amp;pageSize=0&amp;guest=&amp;format=atomfeed" type="application/atom+xml;type=feed"/>
<title>Company Home Children</title>
<updated>2009-06-18T10:20:29.937+02:00</updated>
<entry>
<author><name>System</name></author>
<content>e98319fa-76e4-478f-8ce8-a3a0fd683e2c</content>
<id>urn:uuid:e98319fa-76e4-478f-8ce8-a3a0fd683e2c</id>

<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c"/>
<link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c"/>
<link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c/permissions"/>
<link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c/associations"/>
<link rel="cmis-parent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="cmis-folderparent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c/parent"/>
<link rel="cmis-children" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c/children"/>
<link rel="cmis-descendants" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c/descendants"/>
<link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/F/st_sites"/>
<link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
<published>2009-06-18T10:20:37.788+02:00</published>
<summary>Site Collaboration Spaces</summary>
<title>Sites</title>
<updated>2009-06-18T10:20:37.874+02:00</updated>

<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>F/st_sites</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>System</cmis:value></cmis:propertyString>

<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-18T10:20:37.788+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-18T10:20:37.874+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>Sites</cmis:value></cmis:propertyString>
<cmis:propertyId cmis:name="ParentId"><cmis:value>workspace://SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce</cmis:value></cmis:propertyId>

</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-18T10:20:37.874+02:00</app:edited>
<alf:icon>http://10.33.4.34:8080/alfresco/images/icons/space-icon-default-16.gif</alf:icon>
</entry>
<entry>
<author><name>System</name></author>
<content>8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10</content>
<id>urn:uuid:8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10</id>

<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10"/>
<link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10"/>
<link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10/permissions"/>
<link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10/associations"/>
<link rel="cmis-parent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="cmis-folderparent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10/parent"/>
<link rel="cmis-children" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10/children"/>
<link rel="cmis-descendants" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10/descendants"/>
<link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/folder"/>
<link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
<published>2009-06-18T10:20:29.939+02:00</published>
<summary>User managed definitions</summary>
<title>Data Dictionary</title>
<updated>2009-06-18T10:20:30.004+02:00</updated>

<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>System</cmis:value></cmis:propertyString>

<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-18T10:20:29.939+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-18T10:20:30.004+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>Data Dictionary</cmis:value></cmis:propertyString>
<cmis:propertyId cmis:name="ParentId"><cmis:value>workspace://SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce</cmis:value></cmis:propertyId>

</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-18T10:20:30.004+02:00</app:edited>
<alf:icon>http://10.33.4.34:8080/alfresco/images/icons/space-icon-default-16.gif</alf:icon>
</entry>
<entry>
<author><name>System</name></author>
<content>ba2524ef-7f3d-4ed4-84a0-8d99b6524737</content>
<id>urn:uuid:ba2524ef-7f3d-4ed4-84a0-8d99b6524737</id>

<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737"/>
<link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737"/>
<link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737/permissions"/>
<link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737/associations"/>
<link rel="cmis-parent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="cmis-folderparent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737/parent"/>
<link rel="cmis-children" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737/children"/>
<link rel="cmis-descendants" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737/descendants"/>
<link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/folder"/>
<link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
<published>2009-06-18T10:20:30.312+02:00</published>
<summary>The guest root space</summary>
<title>Guest Home</title>

<updated>2009-06-18T10:20:30.400+02:00</updated>
<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>System</cmis:value></cmis:propertyString>

<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-18T10:20:30.312+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-18T10:20:30.400+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>Guest Home</cmis:value></cmis:propertyString>
<cmis:propertyId cmis:name="ParentId"><cmis:value>workspace://SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce</cmis:value></cmis:propertyId>

</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-18T10:20:30.400+02:00</app:edited>
<alf:icon>http://10.33.4.34:8080/alfresco/images/icons/space-icon-default-16.gif</alf:icon>
</entry>
<entry>
<author><name>System</name></author>
<content>86224486-b7ae-4074-a793-82cd259b0026</content>
<id>urn:uuid:86224486-b7ae-4074-a793-82cd259b0026</id>

<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026"/>
<link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026"/>
<link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026/permissions"/>
<link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026/associations"/>
<link rel="cmis-parent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="cmis-folderparent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026/parent"/>
<link rel="cmis-children" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026/children"/>
<link rel="cmis-descendants" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026/descendants"/>
<link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/folder"/>
<link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
<published>2009-06-18T10:20:30.402+02:00</published>
<summary>User Homes</summary>
<title>User Homes</title>
<updated>2009-06-18T10:20:30.428+02:00</updated>

<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/86224486-b7ae-4074-a793-82cd259b0026</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>System</cmis:value></cmis:propertyString>

<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-18T10:20:30.402+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-18T10:20:30.428+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>User Homes</cmis:value></cmis:propertyString>
<cmis:propertyId cmis:name="ParentId"><cmis:value>workspace://SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce</cmis:value></cmis:propertyId>

</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-18T10:20:30.428+02:00</app:edited>
<alf:icon>http://10.33.4.34:8080/alfresco/images/icons/space-icon-default-16.gif</alf:icon>
</entry>
<entry>
<author><name>System</name></author>
<content>0df9087f-e334-4890-a467-b60e3d6be92c</content>
<id>urn:uuid:0df9087f-e334-4890-a467-b60e3d6be92c</id>

<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c"/>
<link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c"/>
<link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c/permissions"/>
<link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c/associations"/>
<link rel="cmis-parent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="cmis-folderparent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c/parent"/>
<link rel="cmis-children" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c/children"/>
<link rel="cmis-descendants" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c/descendants"/>
<link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/folder"/>
<link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
<published>2009-06-18T10:20:45.115+02:00</published>
<summary>Web Content Management Spaces</summary>
<title>Web Projects</title>
<updated>2009-06-18T10:20:45.137+02:00</updated>

<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>System</cmis:value></cmis:propertyString>

<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-18T10:20:45.115+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-18T10:20:45.137+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>Web Projects</cmis:value></cmis:propertyString>
<cmis:propertyId cmis:name="ParentId"><cmis:value>workspace://SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce</cmis:value></cmis:propertyId>

</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-18T10:20:45.137+02:00</app:edited>
<alf:icon>http://10.33.4.34:8080/alfresco/images/icons/space-icon-default-16.gif</alf:icon>
</entry>
<entry>
<author><name>admin</name></author>
<content type="application/pdf" src="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><id>urn:uuid:2df9d676-f173-47bb-8ec1-41fa1186b66d</id>
<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d"/>

<link rel="enclosure" type="application/pdf" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d"/>
<link rel="edit-media" type="application/pdf" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/permissions"/>
<link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/associations"/>
<link rel="cmis-parents" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/parents"/>
<link rel="cmis-allversions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/versions"/>
<link rel="cmis-stream" type="application/pdf" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/document"/>
<link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
<published>2009-06-23T09:40:47.889+02:00</published>
<summary></summary>
<title>h4555-cmis-so.pdf</title>
<updated>2009-06-23T09:40:58.524+02:00</updated>

<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>document</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>document</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>admin</cmis:value></cmis:propertyString>

<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-23T09:40:47.889+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>admin</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-23T09:40:58.524+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>h4555-cmis-so.pdf</cmis:value></cmis:propertyString>
<cmis:propertyBoolean cmis:name="IsImmutable"><cmis:value>false</cmis:value></cmis:propertyBoolean>

<cmis:propertyBoolean cmis:name="IsLatestVersion"><cmis:value>true</cmis:value></cmis:propertyBoolean>
<cmis:propertyBoolean cmis:name="IsMajorVersion"><cmis:value>false</cmis:value></cmis:propertyBoolean>
<cmis:propertyBoolean cmis:name="IsLatestMajorVersion"><cmis:value>false</cmis:value></cmis:propertyBoolean>
<cmis:propertyString cmis:name="VersionLabel"/>
<cmis:propertyId cmis:name="VersionSeriesId"><cmis:value>workspace://SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d</cmis:value></cmis:propertyId>

<cmis:propertyBoolean cmis:name="IsVersionSeriesCheckedOut"><cmis:value>false</cmis:value></cmis:propertyBoolean>
<cmis:propertyString cmis:name="VersionSeriesCheckedOutBy"/>
<cmis:propertyId cmis:name="VersionSeriesCheckedOutId"/>
<cmis:propertyString cmis:name="CheckinComment"/>
<cmis:propertyInteger cmis:name="ContentStreamLength"><cmis:value>343084</cmis:value></cmis:propertyInteger>
<cmis:propertyString cmis:name="ContentStreamMimeType"><cmis:value>application/pdf</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ContentStreamFilename"><cmis:value>h4555-cmis-so.pdf</cmis:value></cmis:propertyString>

<cmis:propertyString cmis:name="ContentStreamURI"><cmis:value>http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf</cmis:value></cmis:propertyString>
</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-23T09:40:58.524+02:00</app:edited>
<alf:icon>http://10.33.4.34:8080/alfresco/images/filetypes/pdf.gif</alf:icon>
</entry>
<cmis:hasMoreItems>false</cmis:hasMoreItems>
<opensearch:totalResults>6</opensearch:totalResults>
<opensearch:startIndex>0</opensearch:startIndex>

<opensearch:itemsPerPage>0</opensearch:itemsPerPage>
</feed>';

$folderFeed = '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:cmis="http://www.cmis.org/2008/05">
<entry>
<author><name>System</name></author>
<content>28537649-8af2-4c74-aa92-5d8bbecac9ce</content>
<id>urn:uuid:28537649-8af2-4c74-aa92-5d8bbecac9ce</id>
<link rel="self" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="edit" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="cmis-allowableactions" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce/permissions"/>
<link rel="cmis-relationships" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce/associations"/>
<link rel="cmis-children" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce/children"/>
<link rel="cmis-descendants" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce/descendants"/>
<link rel="cmis-type" href="http://127.0.0.1:8080/alfresco/service/api/type/folder"/>
<link rel="cmis-repository" href="http://127.0.0.1:8080/alfresco/service/api/repository"/>
<published>2009-06-18T10:20:29.871+02:00</published>
<summary>The company root space</summary>
<title>Company Home</title>
<updated>2009-06-18T10:20:29.937+02:00</updated>
<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-18T10:20:29.871+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-18T10:20:29.937+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>Company Home</cmis:value></cmis:propertyString>
<cmis:propertyId cmis:name="ParentId"/>
</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-18T10:20:29.937+02:00</app:edited>
<alf:icon>http://127.0.0.1:8080/alfresco/images/icons/space-icon-default-16.gif</alf:icon>
</entry>
</feed>';

$docFeed = '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:cmis="http://www.cmis.org/2008/05">
<entry>
<author><name>admin</name></author>
<content type="application/pdf" src="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><id>urn:uuid:2df9d676-f173-47bb-8ec1-41fa1186b66d</id>
<link rel="self" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d"/>
<link rel="enclosure" type="application/pdf" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="edit" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d"/>
<link rel="edit-media" type="application/pdf" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="cmis-allowableactions" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/permissions"/>
<link rel="cmis-relationships" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/associations"/>
<link rel="cmis-parents" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/parents"/>
<link rel="cmis-allversions" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/versions"/>
<link rel="cmis-stream" type="application/pdf" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="cmis-type" href="http://127.0.0.1:8080/alfresco/service/api/type/document"/>
<link rel="cmis-repository" href="http://127.0.0.1:8080/alfresco/service/api/repository"/>
<published>2009-06-23T09:40:47.889+02:00</published>
<summary></summary>
<title>h4555-cmis-so.pdf</title>
<updated>2009-06-23T09:40:58.524+02:00</updated>
<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>document</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>document</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>admin</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-23T09:40:47.889+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>admin</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-23T09:40:58.524+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>h4555-cmis-so.pdf</cmis:value></cmis:propertyString>
<cmis:propertyBoolean cmis:name="IsImmutable"><cmis:value>false</cmis:value></cmis:propertyBoolean>
<cmis:propertyBoolean cmis:name="IsLatestVersion"><cmis:value>true</cmis:value></cmis:propertyBoolean>
<cmis:propertyBoolean cmis:name="IsMajorVersion"><cmis:value>false</cmis:value></cmis:propertyBoolean>
<cmis:propertyBoolean cmis:name="IsLatestMajorVersion"><cmis:value>false</cmis:value></cmis:propertyBoolean>
<cmis:propertyString cmis:name="VersionLabel"/>
<cmis:propertyId cmis:name="VersionSeriesId"><cmis:value>workspace://SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d</cmis:value></cmis:propertyId>
<cmis:propertyBoolean cmis:name="IsVersionSeriesCheckedOut"><cmis:value>false</cmis:value></cmis:propertyBoolean>
<cmis:propertyString cmis:name="VersionSeriesCheckedOutBy"/>
<cmis:propertyId cmis:name="VersionSeriesCheckedOutId"/>
<cmis:propertyString cmis:name="CheckinComment"/>
<cmis:propertyInteger cmis:name="ContentStreamLength"><cmis:value>343084</cmis:value></cmis:propertyInteger>
<cmis:propertyString cmis:name="ContentStreamMimeType"><cmis:value>application/pdf</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ContentStreamFilename"><cmis:value>h4555-cmis-so.pdf</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ContentStreamURI"><cmis:value>http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf</cmis:value></cmis:propertyString>
</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-23T09:40:58.524+02:00</app:edited>
<alf:icon>http://127.0.0.1:8080/alfresco/images/filetypes/pdf.gif</alf:icon>
</entry>
</feed>';
?>