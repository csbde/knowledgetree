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

// load all available CMIS services
include_once CMIS_API . '/ktRepositoryService.inc.php';
include_once CMIS_API . '/ktNavigationService.inc.php';
include_once CMIS_API . '/ktObjectService.inc.php';
include_once CMIS_API . '/ktVersioningService.inc.php';
include_once 'KT_cmis_atom_service_helper.inc.php';

// TODO proper first/last links
// FIXME any incorrect or missing links
// FIXME ContentStreamAllowed tag is empty (at least sometimes)

/**
 * AtomPub Service: folder
 */
class KT_cmis_atom_service_folder extends KT_cmis_atom_service {

    /**
     * Deals with GET actions for folders.
     * This includes children and tree/descendant listings as well as individual folder retrieval 
     */
    public function GET_action()
    {        
        $repositoryId = KT_cmis_atom_service_helper::getRepositoryId($RepositoryService);
        
        // TODO implement full path/node separation as with Alfresco - i.e. path requests come in on path/ and node requests come in on node/
        //      path request e.g.: path/Root Folder/DroppedDocuments
        //      node request e.g.: node/F1/children
        //      node request e.g.: node/F2/parent
        //      node request e.g.: node/F2
        if (urldecode($this->params[0]) == 'Root Folder')
        {
            $folderId = CMISUtil::encodeObjectId(FOLDER, 1);
            $folderName = urldecode($this->params[0]);
        }
        else if ($this->params[0] == 'path')
        {
            $ktapi =& KT_cmis_atom_service_helper::getKt();
            $folderId = KT_cmis_atom_service_helper::getFolderId($this->params, $ktapi);
        }
        else if (($this->params[1] == 'children') || ($this->params[1] == 'descendants'))
        {
            $folderId = $this->params[0];
            $ObjectService = new KTObjectService(KT_cmis_atom_service_helper::getKt());
            $response = $ObjectService->getProperties($repositoryId, $folderId, false, false);
    
            if ($response['status_code'] == 1) {
                $feed = KT_cmis_atom_service_helper::getErrorFeed($this, KT_cmis_atom_service::STATUS_SERVER_ERROR, $response['message']);
                $this->responseFeed = $feed;
                return null;
            }
            else {
                $response = $response['results'];
            }
            
            $folderName = $response['properties']['name']['value'];
        }
        // NOTE parent changes to parents in later specification
        // TODO update when updating to later specification
        // TODO this only returns one parent, need to implement returnToRoot also
        else if ($this->params[1] == 'parent')
        {
            $folderId = $this->params[0];
            $NavigationService = new KTNavigationService(KT_cmis_atom_service_helper::getKt());
            $response = $NavigationService->getFolderParent($repositoryId, $folderId, false, false, false);

            if ($response['status_code'] == 1) {
                $feed = KT_cmis_atom_service_helper::getErrorFeed($this, KT_cmis_atom_service::STATUS_SERVER_ERROR, $response['message']);
                $this->responseFeed = $feed;
                return null;
            }
            else {
                $response = $response['results'];
            }
            
            // we know that a folder will only have one parent, so we can assume element 0
            $folderId = $response[0]['properties']['objectId']['value'];
            $folderName = $response[0]['properties']['name']['value'];
        }
        else {
            $folderId = $this->params[0];
        }

        if (!empty($this->params[1]) && (($this->params[1] == 'children') || ($this->params[1] == 'descendants')))
        {
            $NavigationService = new KTNavigationService(KT_cmis_atom_service_helper::getKt());
            $feed = $this->getFolderChildrenFeed($NavigationService, $repositoryId, $folderId, $folderName, $this->params[1]);
        }
        else
        {
            $ObjectService = new KTObjectService(KT_cmis_atom_service_helper::getKt());
            $feed = KT_cmis_atom_service_helper::getObjectFeed($this, $ObjectService, $repositoryId, $folderId);
        }

        // Expose the responseFeed
        $this->responseFeed = $feed;
    }

    /**
     * Deals with folder service POST actions.
     * This includes creation/moving of both folders and documents.
     */
    public function POST_action()
    {   
        global $default;
        $default->log->info($this->rawContent); 
        
        $repositoryId = KT_cmis_atom_service_helper::getRepositoryId($RepositoryService);

        // set default action, objectId and typeId
        $action = 'create'; 
        $objectId = null;
        $typeId = null;
        
        $folderId = $this->params[0];
        $title = KT_cmis_atom_service_helper::getAtomValues($this->rawContent, 'title');
        $summary = KT_cmis_atom_service_helper::getAtomValues($this->rawContent, 'summary');

        // determine whether this is a folder or a document action
        // document action create will have a content tag <atom:content> or <content> containing base64 encoding of the document
        // move action will have an existing id supplied as a parameter - not sure how this works yet as the CMIS clients we are
        // testing don't support move functionality at this time (2009/07/23) and so we are presuming the following format:
        // /folder/<folderId>/<sourceFolderId>/<objectId>
        // also possible that there will be an existing ObjectId property, try to cater for both until we know how it really works
        // NOTE this also applies to the source folder id, see above
        
        // check for existing object id as parameter in url
        // if sourceFolderId parameter is submitted (expected as parameter 3, or params[2]) then this is a move
        if (isset($this->params[2])) {
            $action = 'move';
            $sourceFolderId = $this->params[2];
        }
        
        // get object properties - todo send through original properties array and not modified version
        $cmisObjectProperties = KT_cmis_atom_service_helper::getCmisObjectProperties($this->rawContent);
        $properties = array('name' => $title, 'summary' => $summary, 'objectTypeId' => $cmisObjectProperties['cmis:objectTypeId']);
        
        // check for existing object id as property of submitted object data
        if (!empty($cmisObjectProperties['cmis:objectId'])) {
            $objectId = $cmisObjectProperties['cmis:objectId'];
        }
        
        // TODO there may be more to do for the checking of an existing object.
        //      e.g. verifying that it does indeed exist, and throwing an exception if it does not:
        //      "If the objected property is present but not valid an exception will be thrown" (from CMIS specification)
        // NOTE this exception should be thrown in the service API code and not here.
        
        // determine type if object is being moved
        if (!is_null($objectId)) {
            CMISUtil::decodeObjectId($objectId, $typeId);
        }
        
        // check for content stream
        $content = KT_cmis_atom_service_helper::getCmisContent($this->rawContent);
        // NOTE not sure about the text type, will need testing, most content will be base64
        $cmisContent = (isset($content['cmisra:base64']) 
                            ? $content['cmisra:base64'] 
                            : ((isset($content['cmisra:text'])) 
                                    ? $content['cmisra:text'] 
                                    : null));

        $ObjectService = new KTObjectService(KT_cmis_atom_service_helper::getKt());

        $default->log->info('here: '.$action);
        
        $success = false;
        $error = null;
        if ($action == 'create')
        {
            // TODO detection and passing of optional parameters (policies, ACEs, etc...) as well as support for other object-types
            if ($cmisObjectProperties['cmis:objectTypeId'] == 'cmis:folder') {
                $default->log->info($cmisObjectProperties['cmis:objectTypeId']);
                $newObjectId = $ObjectService->createFolder($repositoryId, $properties, $folderId);
            }
            else {
                // NOTE for the moment only creation in minor versioning state
                $newObjectId = $ObjectService->createDocument($repositoryId, $properties, $folderId, $cmisContent, 'minor');
            }

            if ($newObjectId['status_code'] == 0) {
                $newObjectId = $newObjectId['results'];
                // check if returned Object Id is a valid CMIS Object Id
                CMISUtil::decodeObjectId($newObjectId, $typeId);
                if ($typeId != 'unknown') {
                    $success = true;
                }
                else {
                    $error = 'Unknown Object Type';
                }
            }
            else {
                $error = $newObjectId['message'];
            }
        }
        else if ($action == 'move')
        {
            $response = $ObjectService->moveObject($repositoryId, $objectId, $folderId, $sourceFolderId);
            
            if ($response['status_code'] == 0) {
                $success = true;
            }
            else {
                $error = $response['message'];
            }
            
            // same object as before
            $newObjectId = $objectId;
            // FIXME why set this?  it does not appear to get used
            $typeId = ucwords($cmisObjectProperties['cmis:objectTypeId']);
        }
        
        if ($success)
        {
            $this->setStatus(($action == 'create') ? self::STATUS_CREATED : self::STATUS_UPDATED);
            $feed = KT_cmis_atom_service_helper::getObjectFeed($this, $ObjectService, $repositoryId, $newObjectId, 'POST');
        }
        else {
            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_SERVER_ERROR, $error);
        }

        // Expose the responseFeed
        $this->responseFeed = $feed;
    }
    
    /**
     * Deals with DELETE actions for folders.
     * This includes deleting a single folder (with no content) and deleting an entire folder tree
     * 
     * @return 204 on success, 500 on error
     */
    public function DELETE_action()
    {
        // NOTE due to the way KnowledgeTree works with folders this is always going to call deleteTree.
        //      we COULD call deleteObject but when we delete a folder we expect to be trying to delete
        //      the folder and all content.
        // TODO determine whether client is requesting deleteObject or deleteTree
        
        $repositoryId = KT_cmis_atom_service_helper::getRepositoryId($RepositoryService);
        
        $ObjectService = new KTObjectService(KT_cmis_atom_service_helper::getKt());

        // attempt delete - last parameter sets $deleteAllVersions true
        $response = $ObjectService->deleteTree($repositoryId, $this->params[0], 'delete', true);

        // error?
        if ($response['status_code'] == 1) {
            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_SERVER_ERROR, $response['message']);
            // Expose the responseFeed
            $this->responseFeed = $feed;
            return null;
        }
        else {
            $response = $response['results'];
        }
        
        // list of failed objects?
        if (is_array($response))
        {
            $this->setStatus(self::STATUS_SERVER_ERROR);
            
            $feed = new KT_cmis_atom_responseFeed_GET(CMIS_APP_BASE_URI);
            $feed->newField('title', 'Error: Failed to delete all objects in tree: ' . self::STATUS_SERVER_ERROR, $feed);
            
            foreach($response as $failed)
            {            
                $entry = $feed->newEntry();
                $objectElement = $feed->newElement('cmisra:object');
                $propertiesElement = $feed->newElement('cmis:properties');
                $propElement = $feed->newElement('cmis:propertyId');
                $propElement->appendChild($feed->newAttr('cmis:name', 'objectId'));
                $feed->newField('cmis:value', $failed, $propElement);
                $propertiesElement->appendChild($propElement);
                $objectElement->appendChild($propertiesElement);
                $entry->appendChild($objectElement);
            }
            
            $this->responseFeed = $feed;
            return null;
        }
        
        // success
        $this->setStatus(self::STATUS_NO_CONTENT);
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
        if ($feedType == 'children') {
            $entries = $NavigationService->getChildren($repositoryId, $folderId, false, false);
        }
        else if ($feedType == 'descendants') {
            // TODO how will client request depth?
            $entries = $NavigationService->getDescendants($repositoryId, $folderId);
        }
        else {
            // error, we shouldn't be here, if we are then the wrong service/function was called
        }
        
        // hack, for removing one level of access
        $entries = $entries['results'];

        // $baseURI=NULL,$title=NULL,$link=NULL,$updated=NULL,$author=NULL,$id=NULL
        $feed = new KT_cmis_atom_responseFeed_GET(CMIS_APP_BASE_URI);
        $workspace = $feed->getWorkspace();
        
        $feed->newField('title', $folderName . ' ' . ucwords($feedType), $feed);
        
        // TODO dynamic?
        $feedElement = $feed->newField('author');
        $element = $feed->newField('name', 'System', $feedElement);
        $feed->appendChild($feedElement);
		
		// id
        $feed->newField('id', 'urn:uuid:' . $folderId . '-' . $feedType, $feed);

        // TODO get actual most recent update time, only use current if no other available
        $feed->newField('updated', KT_cmis_atom_service_helper::formatDatestamp(), $feed);

        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel', 'self'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/folder/' . $folderId . '/' . $feedType));
        $feed->appendChild($link);
        
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel', 'service'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . 'servicedocument'));
        $feed->appendChild($link);

        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel', 'via'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/folder/' . $folderId));
        $feed->appendChild($link);

        KT_cmis_atom_service_helper::createObjectFeed($feed, $entries, $folderName);

        $feed->newField('cmis:hasMoreItems', 'false', $feed);

        return $feed;
    }

}

/**
 * AtomPub Service: document
 */
// TODO confirm that an error response is sent when a document has status "deleted"
class KT_cmis_atom_service_document extends KT_cmis_atom_service {

    /**
     * Deals with GET actions for documents.
     * This includes individual document retrieval 
     */
    public function GET_action()
    {
        $repositoryId = KT_cmis_atom_service_helper::getRepositoryId($RepositoryService);
        
        $ObjectService = new KTObjectService(KT_cmis_atom_service_helper::getKt());

        $objectId = $this->params[0];
        
        // TODO this is "parents" in later versions of the specification
        //      update accordingly when updating to newer specification
        if ($this->params[1] == 'parent')
        {
            $NavigationService = new KTNavigationService(KT_cmis_atom_service_helper::getKt());
            $response = $NavigationService->getObjectParents($repositoryId, $objectId, false, false);

            if ($response['status_code'] == 1) {
                $feed = KT_cmis_atom_service_helper::getErrorFeed($this, KT_cmis_atom_service::STATUS_SERVER_ERROR, $response['message']);
                $this->responseFeed = $feed;
                return null;
            }
            else {
                $response = $response['results'];
            }
            
            // for now a document will only have one parent as KnowledgeTree does not support multi-filing
            // TODO update this code if/when multi-filing support is added
            $objectId = $response[0]['properties']['objectId']['value'];
        }
        // determine whether we want the document entry feed or the actual physical document content.
        // this depends on $this->params[1]
        else if (!empty($this->params[1]))
        {
            KT_cmis_atom_service_helper::downloadContentStream($this, $ObjectService, $repositoryId);
            return null;
        }

        $feed = KT_cmis_atom_service_helper::getObjectFeed($this, $ObjectService, $repositoryId, $objectId);

        // Expose the responseFeed
        $this->responseFeed = $feed;
    }
    
    /**
     * Deals with DELETE actions for documents.
     * This includes deletion of a specific version of a document (latest version) via deleteObject 
     * as well as deleteAllVersions
     * 
     * @return 204 on success, 500 on error
     */
    public function DELETE_action()
    {        
        $repositoryId = KT_cmis_atom_service_helper::getRepositoryId($RepositoryService);
        
        $VersioningService = new KTVersioningService(KT_cmis_atom_service_helper::getKt());
        $ObjectService = new KTObjectService(KT_cmis_atom_service_helper::getKt());
        
        // attempt delete
        $response = $ObjectService->deleteObject($repositoryId, $this->params[0]);

        if ($response['status_code'] == 1) {
            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_SERVER_ERROR, $response['message']);
            // Expose the responseFeed
            $this->responseFeed = $feed;
            return null;
        }
        
        // success
        $this->setStatus(self::STATUS_NO_CONTENT);        
    }
    
}

class KT_cmis_atom_service_pwc extends KT_cmis_atom_service {
    
    protected $serviceType = 'PWC';

    /**
     * Deals with GET actions for Private Working Copies.
     * This includes individual Private Working Copy retrieval 
     */
    public function GET_action()
    {
        $repositoryId = KT_cmis_atom_service_helper::getRepositoryId($RepositoryService);
        
        $ObjectService = new KTObjectService(KT_cmis_atom_service_helper::getKt());

        // determine whether we want the Private Working Copy entry feed or the actual physical Private Working Copy content.
        // this depends on $this->params[1]
        if (!empty($this->params[1]))
        {
            KT_cmis_atom_service_helper::downloadContentStream($this, $ObjectService, $repositoryId);
            return null;
        }

        $feed = KT_cmis_atom_service_helper::getObjectFeed($this, $ObjectService, $repositoryId, $this->params[0]);

        // Expose the responseFeed
        $this->responseFeed = $feed;
    }
    
    /**
     * Deals with DELETE actions for Private Working Copies.
     * This includes deletion of a specific version of a document (latest version) via deleteObject 
     * as well as deleteAllVersions
     * 
     * @return 204 on success, 500 on error
     */
    public function DELETE_action()
    {
        // call the cancel checkout function
        
        $repositoryId = KT_cmis_atom_service_helper::getRepositoryId($RepositoryService);  
        $VersioningService = new KTVersioningService(KT_cmis_atom_service_helper::getKt());
        
        $response = $VersioningService->cancelCheckout($repositoryId, $this->params[0]);

        if ($response['status_code'] == 1) {
            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_SERVER_ERROR, $response['message']);
           // Expose the responseFeed
            $this->responseFeed = $feed;
            return null;
        }
        
        $this->setStatus(self::STATUS_NO_CONTENT);
        $this->responseFeed = null;
    }
    
    public function PUT_action()
    {
        $repositoryId = KT_cmis_atom_service_helper::getRepositoryId($RepositoryService);
        $VersioningService = new KTVersioningService(KT_cmis_atom_service_helper::getKt());
        $ObjectService = new KTObjectService(KT_cmis_atom_service_helper::getKt());
        
        // get object properties
        $cmisObjectProperties = KT_cmis_atom_service_helper::getCmisObjectProperties($this->rawContent);

        // check for content stream
        $content = KT_cmis_atom_service_helper::getCmisContent($this->rawContent);
        // NOTE not sure about the text type, will need testing, most content will be base64
        $cmisContent = (isset($content['cmisra:base64']) 
                            ? $content['cmisra:base64'] 
                            : ((isset($content['cmisra:text'])) 
                                    ? $content['cmisra:text'] 
                                    : null));

        // if we haven't found it now, the hack begins - retrieve the EXISTING content and submit this as the contentStream
        // this is needed because KnowledgeTree will not accept a checkin without a content stream but CMISSpaces (and possibly 
        // other CMIS clients are the same, does not send a content stream on checkin nor does it offer the user a method to choose one)
        // NOTE that if the content is INTENDED to be empty this and all the above checks will FAIL!
        // FIXME this is horrible, terrible, ugly and bad!
        if (empty($cmisContent)) {
            $cmisContent = base64_encode(KT_cmis_atom_service_helper::getContentStream($this, $ObjectService, $repositoryId));
        }
        
        // and if we don't have the content stream by now, we give up...but leave the error to be generated by the underlying KnowledgeTree code
        // checkin function call
        // TODO dynamically detect version change type - leaving this for now as the CMIS clients tested do not appear to 
        //      offer the choice to the user - perhaps it will turn out that this will come from somewhere else but for now
        //      we assume minor version updates only
        $major = false;
        $checkinComment = '';
        $response = $VersioningService->checkIn($repositoryId, $this->params[0], $major, $cmisObjectProperties, $cmisContent, $checkinComment);
        
        if ($response['status_code'] == 1) {
            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_SERVER_ERROR, $response['message']);
            // Expose the responseFeed
            $this->responseFeed = $feed;
            return null;
        }
        
        $feed = KT_cmis_atom_service_helper::getObjectFeed($this, $ObjectService, $repositoryId, $this->params[0]);

        // Expose the responseFeed
        $this->responseFeed = $feed;
    }
    
}

/**
 * AtomPub Service: checkedout
 */
class KT_cmis_atom_service_checkedout extends KT_cmis_atom_service {
    
    /**
     * Deals with GET actions for checkedout documents. 
     */
    public function GET_action()
    {
        $repositoryId = KT_cmis_atom_service_helper::getRepositoryId($RepositoryService);
        $NavigationService = new KTNavigationService(KT_cmis_atom_service_helper::getKt());

        $checkedout = $NavigationService->getCheckedOutDocs($repositoryId);
        
        // hack, for removing one level of access
        $checkedout = $checkedout['results'];

        //Create a new response feed
        $feed = new KT_cmis_atom_responseFeed_GET(CMIS_APP_BASE_URI);
        $workspace = $feed->getWorkspace();
        
        $feed->newField('title', 'Checked out Documents', $feed);
        
        // TODO dynamic?
        $feedElement = $feed->newField('author');
        $element = $feed->newField('name', 'admin', $feedElement);
        $feed->appendChild($feedElement);
        
        $feed->appendChild($feed->newElement('id', 'urn:uuid:checkedout'));

        // TODO get actual most recent update time, only use current if no other available
        $feed->appendChild($feed->newElement('updated', KT_cmis_atom_service_helper::formatDatestamp()));
        
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel', 'self'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/checkedout'));
        $feed->appendChild($link);
        
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','first'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/checkedout/pageNo=1&amp;pageSize=0'));
        $link->appendChild($feed->newAttr('type', 'application/atom+xml;type=feed'));
        $feed->appendChild($link);

        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','last'));
        // TODO set page number correctly - to be done when we support paging the the API
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/checkedout/pageNo=1&amp;pageSize=0'));
        $link->appendChild($feed->newAttr('type', 'application/atom+xml;type=feed'));
        $feed->appendChild($link);

        foreach($checkedout as $cmisEntry) {
            KT_cmis_atom_service_helper::createObjectEntry($feed, $cmisEntry, $folderName, true);
        }

        $feed->newField('cmis:hasMoreItems', 'false', $feed);

        // Expose the responseFeed
        $this->responseFeed = $feed;
    }
    
    public function POST_action()
    {
        $repositoryId = KT_cmis_atom_service_helper::getRepositoryId($RepositoryService);
        $VersioningService = new KTVersioningService(KT_cmis_atom_service_helper::getKt());
        $ObjectService = new KTObjectService(KT_cmis_atom_service_helper::getKt());

        $cmisObjectProperties = KT_cmis_atom_service_helper::getCmisObjectProperties($this->rawContent);
        
        // check for existing object id as property of submitted object data
        if (empty($cmisObjectProperties['cmis:objectId']))
        {
            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_SERVER_ERROR, 'No object was specified for checkout');
            // Expose the responseFeed
            $this->responseFeed = $feed;
            return null;
        }
        
        $response = $VersioningService->checkOut($repositoryId, $cmisObjectProperties['cmis:objectId']);
        
        if ($response['status_code'] == 1) {
            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_SERVER_ERROR, 'No object was specified for checkout');
            // Expose the responseFeed
            $this->responseFeed = $feed;
            return null;
        }
        
        $this->setStatus(self::STATUS_CREATED);
        $feed = KT_cmis_atom_service_helper::getObjectFeed($this, $ObjectService, $repositoryId, $cmisObjectProperties['cmis:objectId'], 'POST');

        // Expose the responseFeed
        $this->responseFeed = $feed;
    }

}

/**
 * AtomPub Service: types
 */
class KT_cmis_atom_service_types extends KT_cmis_atom_service {

    public function GET_action()
    {
        $RepositoryService = new KTRepositoryService();
        $repositoryId = KT_cmis_atom_service_helper::getRepositoryId($RepositoryService);

        $types = $RepositoryService->getTypes($repositoryId);
        
        // hack for removing one level of access
        $types = $types['results'];
        
        $type = ((empty($this->params[0])) ? 'all' : $this->params[0]);
        $feed = KT_cmis_atom_service_helper::getTypeFeed($type, $types);

        // Expose the responseFeed
        $this->responseFeed = $feed;
    }
    
}

/**
 * AtomPub Service: type
 */
class KT_cmis_atom_service_type extends KT_cmis_atom_service {

    public function GET_action()
    {
        $RepositoryService = new KTRepositoryService();
        $repositoryId = KT_cmis_atom_service_helper::getRepositoryId($RepositoryService);
        $type = $this->params[0];

        try {
            $typeDefinition = $RepositoryService->getTypeDefinition($repositoryId, $type);
        }
        catch (Exception $e) {
            $feed = KT_cmis_atom_service_helper::getErrorFeed($this, self::STATUS_SERVER_ERROR, $e->getMessage());
            // Expose the responseFeed
            $this->responseFeed = $feed;
            return null;
        }

        $feed = KT_cmis_atom_service_helper::getTypeFeed($type, array($typeDefinition));

        // Expose the responseFeed
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
        $feed = new KT_cmis_atom_responseFeed_GET(CMIS_APP_BASE_URI);

        $feed->newField('title', 'Child Types of ' . ucwords($this->params[0]), $feed);
        $feed->newField('id', $this->params[0] . '-children', $feed);

        // TODO fetch child types - to be implemented when we support child types in the API

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

        $feed->newField('updated', KT_cmis_atom_service_helper::formatDatestamp(), $feed);
        $feed->newField('cmis:hasMoreItems', 'false', $feed);

        return $feed;
    }

}

?>
