<?php

// initialise ktapi instance
KT_cmis_atom_service_helper::$ktapi = KT_cmis_atom_service_helper::getKt();

class KT_cmis_atom_service_helper {

    public static $ktapi = null;
    public static $repositoryId = null;
    
    /**
     * Helper function to set internal repository id
     *
     * @param object $RepositoryService
     */
    static public function setRepositoryId(&$RepositoryService = null)
    {
        if (is_null($RepositoryService)) {
            $RepositoryService = new KTRepositoryService();
        }
        
        $repositories = $RepositoryService->getRepositories();
        
        // hack for removing one level of access
        $repositories = $repositories['results'];
        
        // TODO handle multiple repositories
        self::$repositoryId = $repositories[0]['repositoryId'];         
    }
    
    /**
     * Helper function to fetch internal repository id
     * 
     * Calls set function automatically, use $set = false to prevent this and return the current setting, if any
     * 
     * NOTE the function will automatically call the setRepositoryId function if no previous repository id was set
     *
     * @param object $RepositoryService
     * @param boolean $RepositoryService
     * @return string
     */
    static public function getRepositoryId(&$RepositoryService = null, $set = true)
    {        
        if (empty(self::$repositoryId) || $set) {
            self::setRepositoryId($RepositoryService);
        }
        
        return self::$repositoryId;
    }

    /**
     * Retrieves data about a specific folder OR document within a folder
     *
     * @param object $ObjectService The CMIS service
     * @param string $repositoryId
     * @param string $folderId
     * @return string CMIS AtomPub feed
     */
    static public function getObjectFeed(&$service, $ObjectService, $repositoryId, $objectId, $method = 'GET')
    {
        self::$repositoryId = $repositoryId;
        
        $serviceType = $service->getServiceType();
        $response = $ObjectService->getProperties($repositoryId, $objectId, false, false);

        if ($response['status_code'] == 1) {
            return KT_cmis_atom_service_helper::getErrorFeed($service, KT_cmis_atom_service::STATUS_SERVER_ERROR, $response['message']);
        }

        $cmisEntry = $response['results'];
        $response = null;
        
        // POST/PWC responses only send back an entry, not a feed
        if (($serviceType == 'PWC') || ($method == 'POST')) {
            if ($method == 'POST') {
                $response = new KT_cmis_atom_response_POST(CMIS_APP_BASE_URI);
            }
            else {
                $response = new KT_cmis_atom_response_GET(CMIS_APP_BASE_URI);
            }
        }
        else if ($method == 'GET') {
            $response = new KT_cmis_atom_responseFeed_GET(CMIS_APP_BASE_URI);
            $response->newField('title', $cmisEntry['properties']['objectTypeId']['value'], $response);
            $response->newField('id', 'urn:uuid:' . $cmisEntry['properties']['objectId']['value'], $response);
        }

        if ($serviceType == 'PWC') $pwc = true; else $pwc = false;
        KT_cmis_atom_service_helper::createObjectEntry($response, $cmisEntry, $cmisEntry['properties']['parentId']['value'], $pwc, $method);
        
        return $response;
    }
    
    static public function createObjectFeed(&$feed, $entries, $folderName)
    {
        foreach($entries as $cmisEntry) {
            self::createObjectEntry($feed, $cmisEntry, $folderName);
        }
    }

    /**
     * Creates an AtomPub entry for a CMIS entry and adds it to the supplied feed
     *
     * @param object $feed The feed to which we add the entry
     * @param array $cmisEntry The entry data
     * @param string $parent The parent folder - this appears to be unused, not sure what it was meant for
     * @param boolean $pwc Whether this is a PWC object
     * @param $method The request method used (POST/GET/...)
     */
    static public function createObjectEntry(&$feed, $cmisEntry, $parent, $pwc = false, $method = 'GET')
    {        
        $workspace = $feed->getWorkspace();

    	// create entry
        $entry = $feed->newEntry();

        // When request is a POST we will be returning only an object entry, not a full feed, and so this belongs here
        if (($method == 'POST') || $pwc)
        {
            // append attributes
            $entry->appendChild($feed->newAttr('xmlns', 'http://www.w3.org/2005/Atom'));
            $entry->appendChild($feed->newAttr('xmlns:app', 'http://www.w3.org/2007/app'));
            $entry->appendChild($feed->newAttr('xmlns:cmis', 'http://docs.oasis-open.org/ns/cmis/core/200908/'));
            $entry->appendChild($feed->newAttr('xmlns:cmisra', 'http://docs.oasis-open.org/ns/cmis/restatom/200908/'));
        }
		
        self::createObjectEntryContent($entry, $feed, $workspace, $cmisEntry, $parent, $pwc, $method);
    }
    
    /**
     * Creates an AtomPub child feed for a CMIS entry and adds it to the supplied entry
     * 
     * @param $feed The child feed element
     * @param $entries Entried contained within the child feed element
     * @param $folderName The parent folder name (currently unused)
     */
    static public function createObjectChildrenFeed(&$childrenFeed, $entries, $workspace, $feed, $folderName)
    {
        foreach($entries as $cmisEntry) {
            self::createChildObjectEntry($childrenFeed, $cmisEntry, $workspace, $feed, $folderName);
        }
    }

    /**
     * Creates an AtomPub child feed for a CMIS entry and adds it to the supplied entry
     *
     * @param object $entry The entry to which we add the child feed
     * @param array $cmisEntry The object entry data
     */
    // NOTE this approach appears to be necessary due to the structure of the underlying atompub code and specification,
    //      which does not directly support nesting - attempting to create a new feed and append it within the outer
    //      feed resulted in an empty cmisra:children node, so this approach was substituted
    static public function createChildObjectEntry(&$childrenFeed, $cmisEntry, $workspace, $feed, $folderNam)
    {        
        $type = strtolower($cmisEntry['properties']['objectTypeId']['value']);
		
        // create entry
        $entry = $feed->newElement('entry');
        self::createObjectEntryContent($entry, $feed, $workspace, $cmisEntry);//, $parent, $pwc, $method);
        $childrenFeed->appendChild($entry);
    }
    
    /**
     * Creates the actual object entry: this is shared between other functions which require this content
     *
     * @param object $entry The entry object
     * @param object $feed The response feed
     * @param array $cmisEntry The CMIS object content
     * @param string $parent The parent folder name
     * @param boolean $pwc Whether this is a PWC object (will be returned slightly differently)
     * @param string $method The calling method (slightly affects the output)
     */
    static public function createObjectEntryContent($entry, &$feed, $workspace, $cmisEntry, $parent = '', $pwc = false, $method = 'GET')
    {
        $type = $cmisEntry['properties']['objectTypeId']['value'];
        
        // TODO dynamic actual creator name
        $responseElement = $feed->newField('author');
        $element = $feed->newField('name', 'admin', $responseElement);
        $entry->appendChild($responseElement);
        
        $typeString = str_replace('cmis:', '', $type);
        
        if (!empty($cmisEntry['properties']['contentStreamLength']['value']))
        {
            $field = $feed->newElement('content');
            $field->appendChild($feed->newAttr('type', $cmisEntry['properties']['contentStreamMimeType']['value']));
            $field->appendChild($feed->newAttr('src', CMIS_APP_BASE_URI . $workspace . '/' . $typeString 
                                                        . '/' . $cmisEntry['properties']['objectId']['value'] 
                                                        . '/' . $cmisEntry['properties']['contentStreamFilename']['value']));
            $entry->appendChild($field);
        }
		
		// content & id tags
        $id = $cmisEntry['properties']['objectId']['value'];
        $feed->newField('id', 'urn:uuid:' . $id, $entry);

        // links
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel', 'self'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' 
                                                    . (!$pwc ? $typeString : 'pwc') . '/' 
                                                    . $cmisEntry['properties']['objectId']['value']));
        $entry->appendChild($link);

        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel', 'edit'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $typeString 
                                                    . '/' . $cmisEntry['properties']['objectId']['value']));
        $entry->appendChild($link);
        
        if ((strtolower($cmisEntry['properties']['objectTypeId']['value']) == 'cmis:document') 
            && (!empty($cmisEntry['properties']['contentStreamLength']['value'])))
        {
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel', 'edit-media'));
            $link->appendChild($feed->newAttr('type', $cmisEntry['properties']['contentStreamMimeType']['value']));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $typeString 
                                                        . '/' . $cmisEntry['properties']['objectId']['value'] 
                                                        . '/' . $cmisEntry['properties']['contentStreamFilename']['value']));
            $entry->appendChild($link);

            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel', 'enclosure'));
            $link->appendChild($feed->newAttr('type', $cmisEntry['properties']['contentStreamMimeType']['value']));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $typeString 
                                                        . '/' . $cmisEntry['properties']['objectId']['value'] 
                                                        . '/' . $cmisEntry['properties']['contentStreamFilename']['value']));
            $entry->appendChild($link);
        }

        // according to spec this MUST be present, but spec says that links for function which are not supported
        // do not need to be present, so unsure for the moment
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel', 'http://docs.oasis-open.org/ns/cmis/link/200908/allowableactions'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $typeString . '/'
                                                    . $cmisEntry['properties']['objectId']['value'] . '/allowableactions'));
        $entry->appendChild($link);

        // according to spec this MUST be present, but spec says that links for function which are not supported
        // do not need to be present, so unsure for the moment
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel', 'http://docs.oasis-open.org/ns/cmis/link/200908/relationships'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $typeString . '/'
                                                    . $cmisEntry['properties']['objectId']['value'] . '/rels'));
        $entry->appendChild($link);
        
        // if there is no parent or parent is 0, do not add the parent link
        // also if this is specifically the root folder, do not add the parent link
//        if (!empty($cmisEntry['properties']['parentId']['value']) && !CMISUtil::isRootFolder(self::$repositoryId, $cmisEntry['properties']['objectId']['value']))

        if (!CMISUtil::isRootFolder(self::$repositoryId, $cmisEntry['properties']['objectId']['value'], self::$ktapi))
        {
            // TODO check parent link is correct, fix if needed
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel', 'up'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/folder/'
                                                        . $cmisEntry['properties']['parentId']['value']));
            $entry->appendChild($link);
        }

        // Folder/Document specific links
        if (strtolower($cmisEntry['properties']['objectTypeId']['value']) == 'cmis:folder')
        {
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel', 'down'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/'
                                                        . $typeString
                                                        . '/' . $cmisEntry['properties']['objectId']['value']
                                                        . '/children'));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel', 'down'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/'
                                                        . $typeString
                                                        . '/' . $cmisEntry['properties']['objectId']['value']
                                                        . '/descendants'));
            $entry->appendChild($link);
            
            // TODO add folder tree link when we have folder tree implemented
            //      this will probably use (much) the same code as the folder children functionality
        }
        else if (strtolower($cmisEntry['properties']['objectTypeId']['value']) == 'cmis:document')
        {
            // if there is a content stream, this link MUST be present
            // not sure yet where it must point...
            if (!empty($cmisEntry['properties']['contentStreamLength']['value']))
            {
                $link = $feed->newElement('link');
                $link->appendChild($feed->newAttr('rel', 'stream'));
                $link->appendChild($feed->newAttr('type', $cmisEntry['properties']['contentStreamMimeType']['value']));
                $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $type 
                                                        . '/' . $cmisEntry['properties']['objectId']['value']
                                                        . '/' . $cmisEntry['properties']['contentStreamFilename']['value']));
                $entry->appendChild($link);
            }

            // if the document is checked out and this is NOT the PWC, this link MUST be present
            // NOTE at the moment the document and the PWC are the same object, so we always show it for a checked out document
            // TODO separated code for PWC and actual document object
            if (!empty($cmisEntry['properties']['versionSeriesCheckedOutId']['value']))
            {
                $link = $feed->newElement('link');
                $link->appendChild($feed->newAttr('rel', 'pwc'));
                $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $type 
                                                                                . '/' . $cmisEntry['properties']['objectId']['value'] 
                                                                                . '/' . $cmisEntry['properties']['contentStreamFilename']['value']));
                $entry->appendChild($link);
                $link = $feed->newElement('link');
                $link->appendChild($feed->newAttr('rel', 'source'));
                $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $type 
                                                                                . '/' . $cmisEntry['properties']['objectId']['value'] 
                                                                                . '/' . $cmisEntry['properties']['contentStreamFilename']['value']));
                $entry->appendChild($link);
            }

//            $link = $feed->newElement('link');
//            $link->appendChild($feed->newAttr('rel', 'stream'));
//            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $type 
//                                                          . '/' . $cmisEntry['properties']['objectId']['value'] 
//                                                          . '/' . $cmisEntry['properties']['contentStreamFilename']['value']));
        }        

        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel', 'describedby'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/type/' . $type));
        $entry->appendChild($link);

        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel', 'service'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . '/servicedocument'));
        $entry->appendChild($link);

        // TODO proper date
        $entry->appendChild($feed->newField('published', self::formatDatestamp()));
        $entry->appendChild($feed->newElement('summary', $cmisEntry['properties']['name']['value']));
        $entry->appendChild($feed->newElement('title', $cmisEntry['properties']['name']['value']));
        $entry->appendChild($feed->newField('updated', self::formatDatestamp()));

        // main CMIS entry
        $objectElement = $feed->newElement('cmisra:object');
        $objectElement->appendChild(self::createEntryPropertiesElement($feed, $cmisEntry['properties']));
        $entry->appendChild($objectElement);
        
        // TODO check determination of when to add app:edited tag
//        if ($method == 'POST') {
            $entry->appendChild($feed->newElement('app:edited', self::formatDatestamp()));
//        }

        // TODO pathSegment entry
        
        // deal with child objects
        if (isset($cmisEntry['children'])) {
            // add children node and fill with child entries
            $childrenFeed = $feed->newElement('feed');
            self::createObjectChildrenFeed($childrenFeed, $cmisEntry['children'], $workspace, $feed, '' /*folderName not passed through*/);
            
            $childrenElement = $feed->newElement('cmisra:children');
            $childrenElement->appendChild($childrenFeed);
            $entry->appendChild($childrenElement);
        }
    }
    
    /**
     * Shared function for creating an object properties node
     *
     * @param object $feed AtomPub response feed 
     * @param array $properties CMIS object properties
     * @return object $propertiesElement AtomPub node
     */
    // TODO leave out unset properties?
    static public function createEntryPropertiesElement(&$feed, $properties)
    {
        $propertiesElement = $feed->newElement('cmis:properties');
        foreach($properties as $propertyName => $property)
        {
            $propElement = $feed->newElement('cmis:' . $property['type']);
//            $propElement->appendChild($feed->newAttr('localName', 'rep-cmis:' . $propertyName));
            $propElement->appendChild($feed->newAttr('propertyDefinitionId', 'cmis:' . $propertyName));
            if (!empty($property['value']))
            {
                if ($propertyName == 'contentStreamUri') {
                    $property['value'] = CMIS_APP_BASE_URI . $workspace . '/' . $type . '/' .$property['value'];
                }
                $feed->newField('cmis:value', CMISUtil::boolToString($property['value']), $propElement);
            }
            $propertiesElement->appendChild($propElement);
        }
        
        return $propertiesElement;
    }

    /**
     * Retrieves the list of types|type definition as a CMIS AtomPub feed
     *
     * @param string $typeDef Type requested - 'All Types' indicates a listing, else only a specific type
     * @param array $types The types found
     * @return string CMIS AtomPub feed
     */
    static public function getTypeFeed($typeDef, $types)
    {
        global $default;
//        $default->log->info(print_r($types));
//        $default->log->info(print_r($types, true));
        $default->log->info($typeDef);
        $typesString = '';
        $typesHeading = '';
        switch($typeDef)
        {
            case 'all':
            case 'children':
            case 'descendants':
                $typesString = 'types-' . $typeDef;
                $typesHeading = 'All Types';
                break;
            default:
                $typesString = 'type-' . $typeDef;
                $typesHeading = $typeDef;
                break;
        }

        //Create a new response feed
        $feed = new KT_cmis_atom_responseFeed_GET(CMIS_APP_BASE_URI);
        $workspace = $feed->getWorkspace();
        
        $feed->newField('title', $typesHeading, $feed);
        $feed->newField('id', 'urn:uuid:' . $typesString, $feed);

        // TODO set page number correctly - to be done when we support paging the the API
        
        // author
        // TODO generate this dynamically (based on???)\
        $feedElement = $feed->newField('author');
        $element = $feed->newField('name', 'admin', $feedElement);
        $feed->appendChild($feedElement);

        foreach($types as $type)
        {
            $entry = $feed->newEntry();
            
            $feedElement = $feed->newField('author');
            $element = $feed->newField('name', 'admin', $feedElement);
            $entry->appendChild($feedElement);
            $feedElement = $feed->newField('content', 'Type definition for ' . $type['baseId']);
            $entry->appendChild($feedElement);

            $feed->newField('id', 'urn:uuid:type-' . $type['baseId'], $feed);
            
            // links
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','self'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/type/' . strtolower($type['baseId'])));
            $entry->appendChild($link);
            // TODO type link MUST point to base type
            //      KnowledgeTree currently only supports base types so this is not important
            //      at the present time as it will always point at the base type.
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','type'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/type/' . strtolower($type['baseId'])));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','repository'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . '/servicedocument'));
            $entry->appendChild($link);
            
            $entry->appendChild($feed->newElement('summary', 'Summary for ' . $type['baseId'] . ' type'));
            $entry->appendChild($feed->newElement('title', $type['baseId']));
            $entry->appendChild($feed->newElement('updated', self::formatDatestamp()));
            
            $objectElement = $feed->newElement('cmisra:type');
            foreach($type as $property => $value) {
                $feed->newField('cmis:' . $property, CMISUtil::boolToString($value), $objectElement);
            }
            $entry->appendChild($objectElement);
        }

        return $feed;
    }

    static public function getErrorFeed(&$service, $status, $message)
    {
        $service->setStatus($status);
        $feed = new KT_cmis_atom_responseFeed_GET(CMIS_APP_BASE_URI);
        
        $feed->newField('title', 'Error: ' . $status, $feed);
        $entry = $feed->newEntry();
        $feed->newField('error', $message, $entry);

        return $feed;
    }

    /**
     * Fetches the CMIS objectId based on the path
     *
     * @param array $path
     * @param object $ktapi KTAPI instance
     */
    // TODO make this much more efficient than this method
    static public function getFolderId($path, &$ktapi)
    {
        // lose first item
        array_shift($path);

        $numQ = count($path);
        $numFolders = $numQ;
        $folderId = 1;

        $start = 0;
        while($start < $numFolders)
        {
            $name = $path[$numQ-$numFolders+$start];
            // fix for possible url encoding issue
            $name = str_replace('%2520', '%20', $name);

            $folderName = urldecode($name);
            $folder = $ktapi->get_folder_by_name($folderName, $folderId);
            $folderId = $folder->get_folderid();
            ++$start;
        }
        
        return CMISUtil::encodeObjectId(FOLDER, $folderId);
    }
    
    static public function getCmisObjectProperties(&$xml)
    {
        $xmlReader = new XMLReader();
        $xmlReader->XML($xml);
        $object = false;
        $objectProperties = false;
        $cmisObjectProperty = null;
        $cmisObjectPropertiesCollection = array();
        while ($xmlReader->read()) {
            // get cmis object properties
            if ($xmlReader->name == 'cmisra:object') {
                $object = ($xmlReader->nodeType == XMLReader::ELEMENT);
                // exit if we have finished reading the cmis object node
                if (!$object) {
                    break;
                }
            }
            else if ($object && ($xmlReader->name == 'cmis:properties')) {
                $objectProperties = ($xmlReader->nodeType == XMLReader::ELEMENT);
            }
            else if ($objectProperties && ($xmlReader->nodeType == XMLReader::ELEMENT)) {
                if (strstr($xmlReader->name, 'cmis:property') && $xmlReader->nodeType == XMLReader::ELEMENT) {
                    $cmisObjectProperty = $xmlReader->getAttribute('propertyDefinitionId');
                }
                else if ($xmlReader->name == 'cmis:value' && $xmlReader->nodeType == XMLReader::ELEMENT) {
                    // push to next read, which will be the text contained within the node
                    $xmlReader->read();
                    $cmisObjectPropertiesCollection[$cmisObjectProperty] = $xmlReader->value;
                    // reset for next value - may leave this out of final code
                    $cmisObjectProperty = null;
                }
            }
        }
        
        return $cmisObjectPropertiesCollection;
    }
    
    static public function getCmisContent(&$xml)
    {
        $xmlReader = new XMLReader();
        $xmlReader->XML($xml);
        $content = false;
        $cmisContentProperty = null;
        $cmisObjectContent = array();
        while ($xmlReader->read()) {
            if ($xmlReader->name == 'cmisra:content') {
                $content = ($xmlReader->nodeType == XMLReader::ELEMENT);
                // exit if we have finished reading the cmis content node
                if (!$content) {
                    break;
                }
            }
            else if ($content && ($xmlReader->nodeType == XMLReader::ELEMENT)) {
                $cmisContentProperty = $xmlReader->name;
                //  push to next read, which will be the text contained within the node
                $xmlReader->read();
                $cmisObjectContent[$cmisContentProperty] = $xmlReader->value;
            }
        }
        
        return $cmisObjectContent;
    }
    
    static public function getAtomValues(&$xml, $tag)
    {
        $returnTag = null;
        
        $xmlReader = new XMLReader();
        $xmlReader->XML($xml);
        $foundTag = false;
        while ($xmlReader->read()) {
            // using strstr because we may or may not have the tag preceded by "atom:"
            // TODO ensure that this does not return incorrect matches
            if (strstr($xmlReader->name, $tag)) {
                $foundTag = ($xmlReader->nodeType == XMLReader::ELEMENT);
                // exit if we have finished reading the cmis content node
                if ($foundTag) {
                    $xmlReader->read();
                    $returnTag = $xmlReader->value;
                }
                else {
                    break;
                }
            }
        }
        
        return $returnTag;
    }

    static public function getCmisPropertiesOld($xmlArray)
    {
        $properties = array();
        
        // find cmisra:object tag
        $baseCmisObject = KT_cmis_atom_service_helper::findTag('cmisra:object', $xmlArray, null, false);
        if(count($baseCmisObject) <= 0)
        {
            $entryObject = KT_cmis_atom_service_helper::findTag('entry', $xmlArray, null, false);
            $baseCmisObject = KT_cmis_atom_service_helper::findTag('cmisra:object', $entryObject['@children'], null, true);
        }
        
        if(count($baseCmisObject) > 0)
        {
            foreach($baseCmisObject[0]['@children'] as $key => $childElement)
            {
                if ($key == 'cmis:properties')
                {
                    foreach($childElement[0]['@children'] as $cmisPropertyDefinition)
                    {
                        foreach($cmisPropertyDefinition as $propertyType => $propertyDefinition)
                        {
                            $properties[$propertyDefinition['@attributes']['cmis:name']] 
                                    = $propertyDefinition['@children']['cmis:value'][0]['@value'];
                        }
                    }
                }
            }
        }

        return $properties;
    }

    static public function getAtomValuesOld($xmlArray, $tag)
    {
        if (!is_null($xmlArray['atom:'.$tag]))
            return $xmlArray['atom:'.$tag][0]['@value'];
        else if (!is_null($xmlArray[$tag]))
            return $xmlArray[$tag][0]['@value'];

        return null;
    }

    /**
	 * Get the KT singleton instance
	 *
	 * @return object
	 */
	public static function getKt()
    {
		if(!isset(self::$ktapi))
        {
        	self::$ktapi = new KTAPI();
			$active = self::$ktapi->get_active_session(session_id());
			
			if (PEAR::isError($active))
			{
				// invoke auth code, session must be restarted
				if(!KT_atom_HTTPauth::isLoggedIn()) {
					KT_atom_HTTPauth::login('KnowledgeTree DMS', 'You must authenticate to enter this realm');
				}
			}
		}
		return self::$ktapi;
	}

    // TODO adjust for time zones?
    static public function formatDatestamp($time = null)
    {
        if (is_null($time)) $time = time();
        return date('Y-m-d H:i:s', $time);
    }
    
    /**
     * Fetches the document content stream for internal use
     * 
     * @param object $ObjectService
     * @param string $repositoryId
     * @return null | string $contentStream
     */
    static public function getContentStream(&$service, &$ObjectService, $repositoryId)
    {
        $response = $ObjectService->getProperties($repositoryId, $service->params[0], false, false);
        if ($response['status_code'] == 1) {
            return null;
        }
        
        $contentStream = $ObjectService->getContentStream($repositoryId, $service->params[0]);
        
        // hack for removing one level of access
        $contentStream = $contentStream['results'];
        
        return $contentStream;
    }
    /**
     * Fetches and prepares the document content stream for download/viewing
     * 
     * @param object $ObjectService
     * @param string $repositoryId
     * @return null | nothing
     */
    static public function downloadContentStream(&$service, &$ObjectService, $repositoryId)
    {
        $response = $ObjectService->getProperties($repositoryId, $service->params[0], false, false);
        if ($response['status_code'] == 1) {
            $feed = KT_cmis_atom_service_helper::getErrorFeed($service, KT_cmis_atom_service::STATUS_SERVER_ERROR, $response['message']);
            $service->responseFeed = $feed;
            return null;
        }
        else {
            $response = $response['results'];
        }
        
        // TODO also check If-Modified-Since?
//        $service->headers['If-Modified-Since'] => 2009-07-24 17:16:54

        $service->setContentDownload(true);
        $eTag = md5($response['properties']['lastModificationDate']['value'] . $response['properties']['contentStreamLength']['value']);
        
        if ($service->headers['If-None-Match'] == $eTag)
        {
            $service->setStatus(KT_cmis_atom_service::STATUS_NOT_MODIFIED);
            $service->setContentDownload(false);
            return null;
        }
        
        $contentStream = $ObjectService->getContentStream($repositoryId, $service->params[0]);
        
        // hack for removing one level of access
        $contentStream = $contentStream['results'];
        
        // headers specific to output
        $service->setEtag($eTag);
        $service->setHeader('Last-Modified', $response['properties']['lastModificationDate']['value']);

        if (!empty($response['properties']['contentStreamMimeType']['value'])) {
    		$service->setHeader('Content-type', $response['properties']['contentStreamMimeType']['value'] . ';charset=utf-8');
        }
        else {
    		$service->setHeader('Content-type', 'text/plain;charset=utf-8');
        }
        
        $service->setHeader('Content-Disposition', 'attachment;filename="' . $response['properties']['contentStreamFilename']['value'] . '"');
		$service->setHeader('Content-Length', $response['properties']['contentStreamLength']['value']);
        $service->setOutput($contentStream);
    }
    
    //TODO: Add key information to be able to find the same tag in the original struct (MarkH)
    static public function findTag($tagName=NULL,$xml=array(),$tagArray=NULL,$deep=false)
    {
        $tagArray=is_array($tagArray)?$tagArray:array();
        foreach($xml as $xmlTag=>$content){
            if($xmlTag===$tagName){
                $tagArray[]=$content;
            }
            if($deep){
                foreach($content as $contentTags){
                    if(is_array($contentTags['@children'])) {
                        if(count($contentTags['@children'])>0) $tagArray=self::findTag($tagName,$contentTags['@children'],$tagArray);
                    }
                }
            }
        }
        //TODO: this is very ugly. Change it. (MarkH)
        return self::rebaseArray($tagArray);
    }
    
    static public function rebaseArray($arr=array()){
        //Force Array
        $arr=is_array($arr)?$arr:array();
        
        //Rebase recursively
        if(count($arr)===1)$arr=self::rebaseArray($arr[0]);
        return $arr;
    }

}

?>
