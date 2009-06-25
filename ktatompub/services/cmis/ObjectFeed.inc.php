<?php

class CMISObjectFeed {

    /**
     * Creates an AtomPub entry for a CMIS entry and adds it to the supplied feed
     *
     * @param object $feed The feed to which we add the entry
     * @param array $cmisEntry The entry data
     * @param string $parent The parent folder
     */
    static public function createEntry(&$feed, $cmisEntry, $parent, $path)
    {
        preg_match('/^\/?cmis\/folder\/(.*)\/[^\/]*\/?$/', trim($_SERVER['QUERY_STRING'], '/'), $matches);
        $path = $matches[1];
        $parent = preg_replace('/\/[^\/]*$/', '', $path);
        
        $entry = $feed->newEntry();
        $feed->newId('urn:uuid:' . $cmisEntry['properties']['Name']['value'] . '-'
                                 . strtolower($cmisEntry['properties']['ObjectTypeId']['value']), $entry);

                        /*
<link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c"/>
<link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c/permissions"/>
<link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c/associations"/>
                         */

        // links
//            $link = $feed->newElement('link');
//            $link->appendChild($feed->newAttr('rel','self'));
//            $link->appendChild($feed->newAttr('href', CMIS_BASE_URI . strtolower($cmisEntry['properties']['ObjectTypeId']['value'])
//                                                                    . '/' . $cmisEntry['properties']['ObjectId']['value']));
//            $entry->appendChild($link);
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','cmis-parent'));
        $link->appendChild($feed->newAttr('href', CMIS_BASE_URI . 'folder/' . $path));
        $entry->appendChild($link);

        if (strtolower($cmisEntry['properties']['ObjectTypeId']['value']) == 'folder')
        {
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-folderparent'));
            $link->appendChild($feed->newAttr('href', CMIS_BASE_URI . 'folder/' . $path));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-children'));
            $link->appendChild($feed->newAttr('href', CMIS_BASE_URI
                                                    . strtolower($cmisEntry['properties']['ObjectTypeId']['value'])
                                                    . '/' . $path . '/' . urlencode($cmisEntry['properties']['Name']['value'])
                                                    . '/children'));
            $entry->appendChild($link);
            $link = $feed->newElement('link');
            $link->appendChild($feed->newAttr('rel','cmis-descendants'));
            $link->appendChild($feed->newAttr('href', CMIS_BASE_URI
                                                    . strtolower($cmisEntry['properties']['ObjectTypeId']['value'])
                                                    . '/' . $path . '/' . urlencode($cmisEntry['properties']['Name']['value']) 
                                                    . '/descendants'));
            $entry->appendChild($link);
        }

        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','cmis-type'));
        $link->appendChild($feed->newAttr('href', CMIS_BASE_URI . 'type/' . strtolower($cmisEntry['properties']['ObjectTypeId']['value'])));
        $entry->appendChild($link);
        $link = $feed->newElement('link');
        $link->appendChild($feed->newAttr('rel','cmis-repository'));
        $link->appendChild($feed->newAttr('href', CMIS_BASE_URI . 'repository'));
        $entry->appendChild($link);
        // end links

        $entry->appendChild($feed->newElement('summary', $cmisEntry['properties']['Name']['value']));
        $entry->appendChild($feed->newElement('title', $cmisEntry['properties']['Name']['value']));

        // main CMIS entry
        $objectElement = $feed->newElement('cmis:object');
        $propertiesElement = $feed->newElement('cmis:properties');
        // <cmis:propertyId cmis:name="ObjectId"><cmis:value>D2</cmis:value></cmis:propertyId>

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

}

?>
