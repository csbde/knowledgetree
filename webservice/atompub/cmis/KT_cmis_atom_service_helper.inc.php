<?php

class KT_cmis_atom_service_helper {

    /**
     * Creates an AtomPub entry for a CMIS entry and adds it to the supplied feed
     *
     * @param object $feed The feed to which we add the entry
     * @param array $cmisEntry The entry data
     * @param string $parent The parent folder
     */
    static public function createObjectEntry(&$feed, $cmisEntry, $parent, $path)
    {
        // TODO next two lots of code (1 commented 1 not) must be replaced with something better
//        $feed->newId('urn:uuid:' . $cmisEntry['properties']['Name']['value'] . '-'
//                                 . strtolower($cmisEntry['properties']['ObjectTypeId']['value']), $entry);

//        echo $_SERVER['QUERY_STRING']."<BR>\n";
//        preg_match('/^\/?[^\/]*\/folder\/(.*)\/[^\/]*\/?$/', trim($_SERVER['QUERY_STRING'], '/'), $matches);
//        $path = $matches[1];
//        $parent = preg_replace('/\/[^\/]*$/', '', $path);
//        // TODO fix path to work on old method, after fixing drupal module to not require extended path
//
//        $path = '';

        $id = $cmisEntry['properties']['ObjectId']['value'];
        $entry = $feed->newEntry();
        $feed->newField('id', 'urn:uuid:' . $id, $entry);
//        print_r($cmisEntry);
        // links
        // TODO check parent link is correct, fix if needed
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','cmis-parent'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/folder/' . $cmisEntry['properties']['ParentId']['value']));
        $entry->appendChild($link);

        if (strtolower($cmisEntry['properties']['ObjectTypeId']['value']) == 'folder')
        {
            // TODO check parent link is correct, fix if needed
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-folderparent'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/folder/' . $cmisEntry['properties']['ParentId']['value']));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-children'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/'
                                                    . strtolower($cmisEntry['properties']['ObjectTypeId']['value'])
                                                    . '/' . $cmisEntry['properties']['ObjectId']['value']
                                                    . '/children'));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-descendants'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/'
                                                    . strtolower($cmisEntry['properties']['ObjectTypeId']['value'])
                                                    . '/' . $cmisEntry['properties']['ObjectId']['value']
                                                    . '/descendants'));
            $entry->appendChild($link);
        }

        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','cmis-type'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/type/' . strtolower($cmisEntry['properties']['ObjectTypeId']['value'])));
        $entry->appendChild($link);
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','cmis-repository'));
        $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/servicedocument'));
        $entry->appendChild($link);
        // end links

        $entry->appendChild($feed->newElement('summary', $cmisEntry['properties']['Name']['value']));
        $entry->appendChild($feed->newElement('title', $cmisEntry['properties']['Name']['value']));

        // main CMIS entry
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

        foreach($types as $type)
        {
            $entry = $feed->newEntry();
            $feed->newId('urn:uuid:type-' . strtolower($type['typeId']), $entry);

            // links
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','self'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/type/' . strtolower($type['typeId'])));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-type'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/type/' . strtolower($type['typeId'])));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-children'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/type/' . strtolower($type['typeId']) . '/children'));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-descendants'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/type/' . strtolower($type['typeId']) . '/descendants'));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-repository'));
            $link->appendChild($feed->newAttr('href', CMIS_APP_BASE_URI . $feed->workspace . '/servicedocument'));
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

        return $feed;
    }

    /**
     * Fetches the CMIS objectId based on the path
     *
     * @param array $path
     * @param object $ktapi KTAPI instance
     */
    // TODO make this much more efficient than this messy method
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

}

?>
