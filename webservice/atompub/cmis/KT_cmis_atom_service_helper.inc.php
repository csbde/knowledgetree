<?php

class KT_cmis_atom_service_helper {

    protected static $kt = null;

    /**
     * Creates an AtomPub entry for a CMIS entry and adds it to the supplied feed
     *
     * @param object $feed The feed to which we add the entry
     * @param array $cmisEntry The entry data
     * @param string $parent The parent folder
     */
    static public function createObjectEntry(&$feed, $cmisEntry, $parent, $path)
    {
    	// create entry
        $entry = $feed->newEntry();
		
        // TODO dynamic actual creator name
        $feedElement = $feed->newField('author');
        $element = $feed->newField('name', 'admin', $feedElement);
        $entry->appendChild($feedElement);
		
		// content & id tags
        $id = $cmisEntry['properties']['ObjectId']['value'];
        $entry->appendChild($feed->newField('content', $id));
        $feed->newField('id', 'urn:uuid:' . $id, $entry);

        // links
        /*
<link rel="allowableactions" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/66f02c27-379e-4782-a0c4-b12f2d5bc543/permissions"/>
<link rel="relationships" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/66f02c27-379e-4782-a0c4-b12f2d5bc543/rels"/>
<link rel="parents" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/66f02c27-379e-4782-a0c4-b12f2d5bc543/parent"/>

        */
        //

        /*
<cmis:propertyUri cmis:name="Uri"/>
<cmis:propertyId cmis:name="AllowedChildObjectTypeIds"/>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-07-13T13:59:20.724+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="ChangeToken"/>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyId cmis:name="ObjectTypeId"><cmis:value>folder</cmis:value></cmis:propertyId>

<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/b30ea6e5-1e3f-4133-82f4-172bc240a9a2</cmis:value></cmis:propertyId>
         */

        $type = strtolower($cmisEntry['properties']['ObjectTypeId']['value']);

        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','self'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/' . $type . '/' . $cmisEntry['properties']['ObjectId']['value']));
        $entry->appendChild($link);

        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','edit'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/' . $type . '/' . $cmisEntry['properties']['ObjectId']['value']));
        $entry->appendChild($link);

        // according to spec this MUST be present, but spec says that links for function which are not supported
        // do not need to be present, so unsure for the moment
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel', 'allowableactions'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/' . $type . '/' 
                                                . $cmisEntry['properties']['ObjectId']['value'] . '/permissions'));
        $entry->appendChild($link);

        // according to spec this MUST be present, but spec says that links for function which are not supported
        // do not need to be present, so unsure for the moment
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel', 'relationships'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/' . $type . '/' 
                                                . $cmisEntry['properties']['ObjectId']['value'] . '/rels'));
        $entry->appendChild($link);
        
        // TODO check parent link is correct, fix if needed
        // TODO leave out if at root folder
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel', 'parents'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/folder/' 
                                                . $cmisEntry['properties']['ObjectId']['value'] . '/parent'));
        $entry->appendChild($link);

        // Folder/Document specific links
        if (strtolower($cmisEntry['properties']['ObjectTypeId']['value']) == 'folder')
        {
            // no longer valid, remove...
//            // TODO check parent link is correct, fix if needed
//            $link = $feed->newElement('link');
//            $link->appendChild($feed->newAttr('rel','folderparent'));
//            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/folder/' . $cmisEntry['properties']['ParentId']['value']));
//            $entry->appendChild($link);

            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','children'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/'
                                                    . $type
                                                    . '/' . $cmisEntry['properties']['ObjectId']['value']
                                                    . '/children'));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','descendants'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/'
                                                    . $type
                                                    . '/' . $cmisEntry['properties']['ObjectId']['value']
                                                    . '/descendants'));
            $entry->appendChild($link);
        }
        else if (strtolower($cmisEntry['properties']['ObjectTypeId']['value']) == 'document')
        {
            // according to spec this MUST be present, but spec says that links for function which are not supported
            // do not need to be present, so unsure for the moment
//            $link = $feed->newElement('link');
//            $link->appendChild($feed->newAttr('rel', 'allversions'));
//            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/' . $type . '/' . $cmisEntry['properties']['ParentId']['value']));
//            $entry->appendChild($link);

            // according to spec this MUST be present, but spec says that links for function which are not supported
            // do not need to be present, so unsure for the moment
//            $link = $feed->newElement('link');
//            $link->appendChild($feed->newAttr('rel', 'latestversion'));
//            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/' . $type . '/' . $cmisEntry['properties']['ParentId']['value']));
//            $entry->appendChild($link);

            // if there is a content stream, this link MUST be present
            // not sure yet where it must point...
            if (!empty($cmisEntry['properties']['ContentStreamLength']['value']))
            {
                $link = $feed->newElement('link');
                $link->appendChild($feed->newAttr('rel', 'stream'));
                $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/' . $type . '/'
                                                        . $cmisEntry['properties']['ObjectId']['value']));
                $entry->appendChild($link);
            }

            // if the document is checked out and this is NOT the PWC, this link MUST be present
//            if (!empty($cmisEntry['properties']['ContentStreamLength']['value']))
//            {
//                $link = $feed->newElement('link');
//                $link->appendChild($feed->newAttr('rel', 'stream'));
//                $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/' . $type . '/' . $cmisEntry['properties']['ParentId']['value']));
//                $entry->appendChild($link);
//            }
        }        

        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','type'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/type/' . $type));
        $entry->appendChild($link);

        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','repository'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . '/servicedocument'));
        $entry->appendChild($link);

        // according to spec this MUST be present, but spec says that links for function which are not supported
        // do not need to be present, so unsure for the moment - policies are being abandoned, or so I thought...
//        $link = $feed->newElement('link');
//        $link->appendChild($feed->newAttr('rel', 'policies'));
//        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/' . $type . '/' . $cmisEntry['properties']['ParentId']['value']));
//        $entry->appendChild($link);
        // end links

        // TODO proper date
        $entry->appendChild($feed->newField('published', self::formatDatestamp()));
        $entry->appendChild($feed->newElement('summary', $cmisEntry['properties']['Name']['value']));
        $entry->appendChild($feed->newElement('title', $cmisEntry['properties']['Name']['value']));
        $entry->appendChild($feed->newField('updated', self::formatDatestamp()));

        // main CMIS entry
        $objectElement = $feed->newElement('cmis:object');
        $propertiesElement = $feed->newElement('cmis:properties');

        foreach($cmisEntry['properties'] as $propertyName => $property)
        {
            $propElement = $feed->newElement('cmis:' . $property['type']);
            $propElement->appendChild($feed->newAttr('cmis:name', $propertyName));
            if (!empty($property['value'])) {
                $feed->newField('cmis:value', CMISUtil::boolToString($property['value']), $propElement);
            }
            $propertiesElement->appendChild($propElement);
        }

        $objectElement->appendChild($propertiesElement);
        $entry->appendChild($objectElement);
        
        // after every entry, append a cmis:terminator tag
        $entry->appendChild($feed->newElement('cmis:terminator'));
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
        $feed = new KT_cmis_atom_responseFeed(CMIS_APP_BASE_URI, $typesHeading, null, null, null, 'urn:uuid:' . $typesString);

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
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/type/' . strtolower($type['typeId'])));
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

            // hack, force to lower case
            $type['typeId'] = strtolower($type['typeId']);

            // NOTE should this be strtolower?  thought so but maybe not always, Alfresco is not consistent...
            $feed->newId('urn:uuid:type-' . strtolower($type['typeId']), $entry);

            // TODO add parents link when not selecting a base type.
            // TODO add children link when type has children
            // TODO add descendants link when type has children
            // NOTE KnowledgeTree currently only supports base types so these are not important at the present time.

            // links
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','self'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/type/' . strtolower($type['typeId'])));
            $entry->appendChild($link);
            // TODO type link MUST point to base type
            //      KnowledgeTree currently only supports base types so this is not important
            //      at the present time as it will always point at the base type.
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','type'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/type/' . strtolower($type['typeId'])));
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
        $feed = new KT_cmis_atom_responseFeed(CMIS_APP_BASE_URI, 'Error: ' . $status);
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
	 * Log in to KT easily
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $ip
	 * @return object Containing the status_code of the login and session id
	 */
	static public function login($username, $password, $ip=null){
		$kt = self::getKt();

		$session = $kt->start_session($username,$password, $ip);
		if (PEAR::isError($session)){
			$response['status_code']=KT_atom_server_FAILURE;
			$response['session_id']='';
		}else{
			$session= $session->get_session();
			$response['status_code'] = KT_atom_server_SUCCESS;
			$response['session_id'] = $session;
		}
		return $response;
	}


	/**
	 * Log out of KT using the session id
	 *
	 * @param string $session_id
	 * @return object Containing the status_code of the logout attempt
	 */
	static public function logout($session_id){
		$kt = self::getKt();
		$session = $kt->get_active_session($session_id, null);

		if (PEAR::isError($session)){
			$response['status_code']=KT_atom_server_FAILURE;
		}else{
			$session->logout();
			$response['status_code'] = KT_atom_server_SUCCESS;
		}
		return $response;
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
