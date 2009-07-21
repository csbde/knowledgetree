<?php

class KT_cmis_atom_service_helper {

    protected static $kt = null;

    /**
     * Retrieves data about a specific folder OR document within a folder
     *
     * @param object $ObjectService The CMIS service
     * @param string $repositoryId
     * @param string $folderId
     * @return string CMIS AtomPub feed
     */
    static public function getObjectFeed($ObjectService, $repositoryId, $objectId, $method = 'GET')
    {
        $cmisEntry = $ObjectService->getProperties($repositoryId, $objectId, false, false);

        if ($method == 'GET') {
            $response = new KT_cmis_atom_responseFeed_GET(CMIS_APP_BASE_URI);
            $response->newField('title', $cmisEntry['properties']['ObjectTypeId']['value'], $response);
            $response->newField('id', 'urn:uuid:' . $cmisEntry['properties']['ObjectId']['value'], $response);
        }
        else if ($method == 'POST') {
            $response = new KT_cmis_atom_response_POST(CMIS_APP_BASE_URI);
        }

        KT_cmis_atom_service_helper::createObjectEntry($response, $cmisEntry, $cmisEntry['properties']['ParentId']['value'], $method);

        if ($method == 'GET') {
            $response->newField('cmis:hasMoreItems', 'false', $response);
        }
        
        return $response;
    }

    /**
     * Creates an AtomPub entry for a CMIS entry and adds it to the supplied feed
     *
     * @param object $feed The feed to which we add the entry
     * @param array $cmisEntry The entry data
     * @param string $parent The parent folder
     */
    static public function createObjectEntry(&$response, $cmisEntry, $parent, $method = 'GET')
    {
        $workspace = $response->getWorkspace();

    	// create entry
        $entry = $response->newEntry();

        if ($method == 'POST')
        {
            // append attributes
            $entry->appendChild($response->newAttr('xmlns', 'http://www.w3.org/2005/Atom'));
            $entry->appendChild($response->newAttr('xmlns:app', 'http://www.w3.org/2007/app'));
            $entry->appendChild($response->newAttr('xmlns:cmis', 'http://docs.oasis-open.org/ns/cmis/core/200901'));
        }
		
        // TODO dynamic actual creator name
        $responseElement = $response->newField('author');
        $element = $response->newField('name', 'admin', $responseElement);
        $entry->appendChild($responseElement);
		
		// content & id tags
        $id = $cmisEntry['properties']['ObjectId']['value'];
        $entry->appendChild($response->newField('content', $id));
        $response->newField('id', 'urn:uuid:' . $id, $entry);

        $type = strtolower($cmisEntry['properties']['ObjectTypeId']['value']);

        // links
        $link = $response->newElement('link');
        $link->appendChild($response->newAttr('rel','self'));
        $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $type . '/' . $cmisEntry['properties']['ObjectId']['value']));
        $entry->appendChild($link);

        $link = $response->newElement('link');
        $link->appendChild($response->newAttr('rel','edit'));
        $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $type . '/' . $cmisEntry['properties']['ObjectId']['value']));
        $entry->appendChild($link);

        // according to spec this MUST be present, but spec says that links for function which are not supported
        // do not need to be present, so unsure for the moment
        $link = $response->newElement('link');
        $link->appendChild($response->newAttr('rel', 'allowableactions'));
        $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $type . '/'
                                                . $cmisEntry['properties']['ObjectId']['value'] . '/permissions'));
        $entry->appendChild($link);

        // according to spec this MUST be present, but spec says that links for function which are not supported
        // do not need to be present, so unsure for the moment
        $link = $response->newElement('link');
        $link->appendChild($response->newAttr('rel', 'relationships'));
        $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $type . '/'
                                                . $cmisEntry['properties']['ObjectId']['value'] . '/rels'));
        $entry->appendChild($link);
        
        // TODO check parent link is correct, fix if needed
        // TODO leave out if at root folder
        $link = $response->newElement('link');
        $link->appendChild($response->newAttr('rel', 'parents'));
        $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/folder/'
                                                . $cmisEntry['properties']['ObjectId']['value'] . '/parent'));
        $entry->appendChild($link);

        // Folder/Document specific links
        if (strtolower($cmisEntry['properties']['ObjectTypeId']['value']) == 'folder')
        {
            $link = $response->newElement('link');
            $link->appendChild($response->newAttr('rel','children'));
            $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/'
                                                    . $type
                                                    . '/' . $cmisEntry['properties']['ObjectId']['value']
                                                    . '/children'));
            $entry->appendChild($link);
            $link = $response->newElement('link');
            $link->appendChild($response->newAttr('rel','descendants'));
            $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/'
                                                    . $type
                                                    . '/' . $cmisEntry['properties']['ObjectId']['value']
                                                    . '/descendants'));
            $entry->appendChild($link);
        }
        else if (strtolower($cmisEntry['properties']['ObjectTypeId']['value']) == 'document')
        {
            // according to spec this MUST be present, but spec says that links for function which are not supported
            // do not need to be present, so unsure for the moment
//            $link = $response->newElement('link');
//            $link->appendChild($response->newAttr('rel', 'allversions'));
//            $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $type . '/' . $cmisEntry['properties']['ParentId']['value']));
//            $entry->appendChild($link);

            // according to spec this MUST be present, but spec says that links for function which are not supported
            // do not need to be present, so unsure for the moment
//            $link = $response->newElement('link');
//            $link->appendChild($response->newAttr('rel', 'latestversion'));
//            $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $type . '/' . $cmisEntry['properties']['ParentId']['value']));
//            $entry->appendChild($link);

            // if there is a content stream, this link MUST be present
            // not sure yet where it must point...
            if (!empty($cmisEntry['properties']['ContentStreamLength']['value']))
            {
                $link = $response->newElement('link');
                $link->appendChild($response->newAttr('rel', 'stream'));
                $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $type . '/'
                                                        . $cmisEntry['properties']['ObjectId']['value']));
                $entry->appendChild($link);
            }

            // if the document is checked out and this is NOT the PWC, this link MUST be present
//            if (!empty($cmisEntry['properties']['ContentStreamLength']['value']))
//            {
//                $link = $response->newElement('link');
//                $link->appendChild($response->newAttr('rel', 'stream'));
//                $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $type . '/' . $cmisEntry['properties']['ParentId']['value']));
//                $entry->appendChild($link);
//            }
        }        

        $link = $response->newElement('link');
        $link->appendChild($response->newAttr('rel','type'));
        $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/type/' . $type));
        $entry->appendChild($link);

        $link = $response->newElement('link');
        $link->appendChild($response->newAttr('rel','repository'));
        $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . '/servicedocument'));
        $entry->appendChild($link);

        // according to spec this MUST be present, but spec says that links for function which are not supported
        // do not need to be present, so unsure for the moment - policies are being abandoned, or so I thought...
//        $link = $response->newElement('link');
//        $link->appendChild($response->newAttr('rel', 'policies'));
//        $link->appendChild($response->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/' . $type . '/' . $cmisEntry['properties']['ParentId']['value']));
//        $entry->appendChild($link);
        // end links

        // TODO proper date
        $entry->appendChild($response->newField('published', self::formatDatestamp()));
        $entry->appendChild($response->newElement('summary', $cmisEntry['properties']['Name']['value']));
        $entry->appendChild($response->newElement('title', $cmisEntry['properties']['Name']['value']));
        $entry->appendChild($response->newField('updated', self::formatDatestamp()));

        // main CMIS entry
        $objectElement = $response->newElement('cmis:object');
        $propertiesElement = $response->newElement('cmis:properties');

        foreach($cmisEntry['properties'] as $propertyName => $property)
        {
            $propElement = $response->newElement('cmis:' . $property['type']);
            $propElement->appendChild($response->newAttr('cmis:name', $propertyName));
            if (!empty($property['value'])) {
                $response->newField('cmis:value', CMISUtil::boolToString($property['value']), $propElement);
            }
            $propertiesElement->appendChild($propElement);
        }

        $objectElement->appendChild($propertiesElement);
        $entry->appendChild($objectElement);
        
        // after every entry, append a cmis:terminator tag
        $entry->appendChild($response->newElement('cmis:terminator'));
        
        if ($method == 'POST') {
            $entry->appendChild($response->newElement('app:edited', self::formatDatestamp()));
        }
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

        // NOTE spec says this link MUST be present but is vague on where it points
        //      as of 0.61c:
        //      "The source link relation points to the underlying CMIS Type Definition as Atom Entry"
        //      so what is the underlying CMIS Type Definition for a collection of base types?
        //      suspect that it only applies when not listing all types, i.e. a base type is asked for
        /*
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','source'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/type/' . strtolower($type['typeId'])));
        $feed->appendChild($link);
         */

        // current time: format = 2009-07-13T14:49:27.659+02:00
        $feed->appendChild($feed->newElement('updated', self::formatDatestamp()));

        foreach($types as $type)
        {
            $entry = $feed->newEntry();
            
            $feedElement = $feed->newField('author');
            $element = $feed->newField('name', 'admin', $feedElement);
            $entry->appendChild($feedElement);
            $feedElement = $feed->newField('content', $type['typeId']);
            $entry->appendChild($feedElement);

            $feed->newField('id', 'urn:uuid:type-' . $type['typeId'], $feed);

            // TODO add parents link when not selecting a base type.
            // TODO add children link when type has children
            // TODO add descendants link when type has children
            // NOTE KnowledgeTree currently only supports base types so these are not important at the present time.

            // links
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','self'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/type/' . strtolower($type['typeId'])));
            $entry->appendChild($link);
            // TODO type link MUST point to base type
            //      KnowledgeTree currently only supports base types so this is not important
            //      at the present time as it will always point at the base type.
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','type'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $workspace . '/type/' . strtolower($type['typeId'])));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','repository'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . '/servicedocument'));
            $entry->appendChild($link);

            $entry->appendChild($feed->newElement('summary', $type['typeId'] . ' Type'));
            $entry->appendChild($feed->newElement('title', $type['typeId']));
            $entry->appendChild($feed->newElement('updated', self::formatDatestamp()));

            // main CMIS entry
            $feedElement = $feed->newElement('cmis:' . strtolower($type['typeId']) . 'Type');
            foreach($type as $property => $value)
            {
                $feed->newField('cmis:' . $property, CMISUtil::boolToString($value), $feedElement);
            }

            $entry->appendChild($feedElement);
            
            // after every entry, append a cmis:terminator tag
            $entry->appendChild($feed->newElement('cmis:terminator'));
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
            // hack to fix drupal url encoding issue
            $name = str_replace('%2520', '%20', $name);

            $folderName = urldecode($name);
            $folder = $ktapi->get_folder_by_name($folderName, $folderId);
            $folderId = $folder->get_folderid();
            ++$start;
        }
        
        return CMISUtil::encodeObjectId('Folder', $folderId);
    }

    static public function getCmisProperties($xmlArray)
    {
        $properties = array();
        
        foreach($xmlArray as $cmisPropertyDefinition)
        {
            foreach($cmisPropertyDefinition as $propertyType => $propertyDefinition)
            {
                $properties[$propertyDefinition['@attributes']['cmis:name']] = $propertyDefinition['@children']['cmis:value'][0]['@value'];
            }
        }

        return $properties;
    }

    static public function getAtomValues($xmlArray, $tag)
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
		if(!isset(self::$kt))
        {
			self::$kt = new KTAPI();
			self::$kt->get_active_session(session_id());
		}
		return self::$kt;
	}

    // TODO adjust for time zones?
    static public function formatDatestamp($time = null)
    {
        if (is_null($time)) $time = time();
        return date('Y-m-d H:i:s', $time);
    }

}

?>
