<?php
/**
 * CMIS Helper class for KnowledgeTree.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008,2009 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 *
 * @copyright 2008-2009, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package KTCMIS
 * @version Version 0.1
 */

require_once(CMIS_DIR . '/objecttypes/CMISDocumentObject.inc.php');
require_once(CMIS_DIR . '/objecttypes/CMISFolderObject.inc.php');

class CMISUtil {

    /**
     * Adds a text identifier to the object ID in order to easily
     * verify which object type we are working with on a return request
     *
     * @param string $typeId
     * @param string $objectId
     * @return string $encoded
     */
    static public function encodeObjectId($typeId, $objectId)
    {
        $encoded = null;

        switch ($typeId)
        {
            case 'D':
            case 'Document':
                $encoded = 'D' . $objectId;
                break;
            case 'F':
            case 'Folder':
                $encoded = 'F' . $objectId;
                break;
            default:
                $encoded = $objectId;
                break;
        }

        return $encoded;
    }

    /**
     * Decodes the identifier created by encodeObjectId to return an object type
     * and a system useable ID
     *
     * The decoded object ID is returned by reference via the argument list
     *
     * @param string $objectId
     * @param string &$typeId
     * @return string $objectId
     */
    static public function decodeObjectId($objectId, &$typeId = null)
    {
        if (!is_string($objectId))
        {
            $typeId = 'Unknown';
            return null;
        }
        
        $typeId = null;

        // NOTE Not sure whether this really belongs here, but probably this is the safest and most reliable place
        // If we find that the folderId is in fact the name of the repository root folder, we will not be able to
        // decode it, but we still need to return a valid id :).  This is because the root folder name is returned
        // by the repository configuration rather than the actual CMIS folder id.
        // TODO consider just setting F1 as the root in the config?  Originally didn't based on Alfresco, but...
        $RepositoryService = new CMISRepositoryService();
        $repositories = $RepositoryService->getRepositories();
        $repositoryInfo = $repositories[0]->getRepositoryInfo();
        // the string replace is a hack for the drupal module, yay...
        if ($repositoryInfo->getRootFolderId() == urldecode(str_replace('%2520', '%20', $objectId))) {
            // NOTE that we may want to check the KnowledgeTree (not CMIS) repository for the root folder id.
            //      This will be vital if we ever implement a way for people to have multiple roots depending
            //      on who is logged in or what they select.  Obviously the CMIS API in general will need a
            //      method of doing this.
            //      meantime this minor hack will get things working for the existing system structure, as the root
            //      folder should always be id 1.
            return '1';
        }

        preg_match('/(\D)(\d*)/', $objectId, $matches);
        $type = $matches[1];
        $objectId = $matches[2];

        switch($type)
        {
            case 'D':
                $typeId = 'Document';
                break;
            case 'F':
                $typeId = 'Folder';
                break;
            default:
                $typeId = 'Unknown';
                break;
        }

        return $objectId;
    }

    /**
     * Takes an array of KnowledgeTree KTAPI objects and returns an array of CMIS objects of the same type
     *
     * Utilises the descending structure already present within KTAPI
     *
     * @param array $input
     * @param string $repositoryURI
     * @param object $ktapi // reference to ktapi instance
     * @return array $CMISArray
     */
    static public function createChildObjectHierarchy($input, $repositoryURI, &$ktapi)
    {
        $CMISArray = array();

        $count = -1;
        foreach($input as $object)
        {
            ++$count;
            if (is_array($object))
            {
                if (isset($object['id']))
                {
                    switch($object['item_type'])
                    {
                        case 'D':
                            $CMISObject = new CMISDocumentObject($object['id'], $ktapi, $repositoryURI);
                            break;
                        case 'F':
                            $CMISObject = new CMISFolderObject($object['id'], $ktapi, $repositoryURI);
                            break;
                    }
                    
                    $CMISArray[$count]['object'] = $CMISObject;
                    
                    // if sub-array
                    if (count($object['items']) > 0)
                    {
                        $CMISArray[$count]['items'] = CMISUtil::createChildObjectHierarchy($object['items'], $repositoryURI, $ktapi);
                    }
                }
                else
                {
                    // NOTE why is this necessary?  That's what you get for not commenting it at the time
                    // TODO comment this properly
                    $CMISArray[$count] = CMISUtil::createChildObjectHierarchy($object, $repositoryURI, $ktapi);
                }
            }
        }

        return $CMISArray;
    }

    /**
     * Takes an array of KnowledgeTree KTAPI objects and returns an array of CMIS objects of the same type
     *
     * As there is no corresponding hierarchy setup for parent trees, this works differently to the child
     * hirerarchy function
     *
     * @param array $input
     * @param string $repositoryURI
     * @param object $ktapi // reference to ktapi instance
     * @return array $CMISArray
     */
    // NOTE this will have to change if we implement multi-filing
    static public function createParentObjectHierarchy($input, $repositoryURI, &$ktapi)
    {
        $CMISArray = array();

        if (count($input) <= 0) return $CMISArray;

        $object = array_shift($input);
        $detail = $object->get_detail();

        if (isset($detail['id']))
        {
            $CMISObject = new CMISFolderObject($detail['id'], $ktapi, $repositoryURI);
            $CMISElement['object'] = $CMISObject;

            // if more parent elements
            if (count($input) > 0)
            {
                $CMISElement['items'] = CMISUtil::createParentObjectHierarchy($input, $repositoryURI, $ktapi);
            }

            $CMISArray[] = $CMISElement;
        }

        return $CMISArray;
    }

    /**
     * Parses a hierarchy of CMIS objects to return an array format of a subset of information
     * required for a webservice response
     *
     * Essentially a reversal of createChildObjectHierarchy and createParentObjectHierarchy,
     * though the output may well be different to what went into that function
     *
     * @param array $input // input hierarchy to decode
     * @param string $linkText // 'child' or 'parent' - indicates direction of hierarchy => descending or ascending
     * @return array $hierarchy
     */
    static public function decodeObjectHierarchy($input, $linkText)
    {
        $hierarchy = array();
        
        // first, run through the base array to get the initial children
        foreach ($input as $key => $entry)
        {
            $object = $entry['object'];
            $properties = $object->getProperties();

            $hierarchy[$key] = CMISUtil::createObjectPropertiesEntry($properties);
        }

        return $hierarchy;
    }

    static public function createObjectPropertiesEntry($properties)
    {
        // TODO better dynamic style fetching of object properties into array for output
        $object = array();

        $object['Author'] = array('value' => $properties->getValue('Author'));
        
        // TODO additional properties to be returned
        $object['properties']['BaseType'] = array('type' => $properties->getFieldType('BaseType'),
                                                           'value' => $properties->getValue('BaseType'));
        $object['properties']['ObjectId'] = array('type' => $properties->getFieldType('ObjectId'),
                                                           'value' => $properties->getValue('ObjectId'));
        $object['properties']['ObjectTypeId'] = array('type' => $properties->getFieldType('ObjectTypeId'),
                                                           'value' => $properties->getValue('ObjectTypeId'));
        $object['properties']['Name'] = array('type' => $properties->getFieldType('Name'),
                                                           'value' => $properties->getValue('Name'));
        // TODO ensure format of date is always correct
        $object['properties']['LastModificationDate'] = array('type' => $properties->getFieldType('LastModificationDate'),
                                                           'value' => $properties->getValue('LastModificationDate'));
        $object['properties']['Uri'] = array('type' => $properties->getFieldType('Uri'),
                               'value' => $properties->getValue('Uri'));

        $object['properties']['ParentId'] = array('type' => $properties->getFieldType('ParentId'),
                                                  'value' => CMISUtil::encodeObjectId('Folder',
                                                  $properties->getValue('ParentId')));

        $object['properties']['AllowedChildObjectTypeIds'] = array('type' => $properties->getFieldType('AllowedChildObjectTypeIds'),
                                                                   'value' => $properties->getValue('AllowedChildObjectTypeIds'));
        
//        $object['properties']['AllowedChildObjectTypeIds'] = array('type' => $properties->getFieldType('AllowedChildObjectTypeIds'),
//                                                                   'value' => $properties->getValue('AllowedChildObjectTypeIds'));

        $object['properties']['CreatedBy'] = array('type' => $properties->getFieldType('CreatedBy'),
                                                   'value' => $properties->getValue('CreatedBy'));

        $object['properties']['CreationDate'] = array('type' => $properties->getFieldType('CreationDate'),
                                                       'value' => $properties->getValue('CreationDate'));

        $object['properties']['ChangeToken'] = array('type' => $properties->getFieldType('ChangeToken'),
                                                       'value' => $properties->getValue('ChangeToken'));

        if (strtolower($properties->getValue('ObjectTypeId')) == 'document')
        {

        $object['properties']['ChangeToken'] = array('type' => $properties->getFieldType('ChangeToken'),
                                                                   'value' => $properties->getValue('ChangeToken'));
            $contentStreamLength = $properties->getValue('ContentStreamLength');
            if (!empty($contentStreamLength))
            {
                $contentStreamLength = $properties->getValue('ContentStreamLength');
                $object['properties']['ContentStreamLength'] = array('type' => $properties->getFieldType('ContentStreamLength'),
                                                               'value' => $properties->getValue('ContentStreamLength'));
                $object['properties']['ContentStreamMimeType'] = array('type' => $properties->getFieldType('ContentStreamMimeType'),
                                                               'value' => $properties->getValue('ContentStreamMimeType'));
            }
        }

        // if we have found a child/parent with one or more children/parents, recurse into the child/parent object
        if (count($entry['items']) > 0)
        {
            $object[$linkText] = CMISUtil::decodeObjectHierarchy($entry['items'], $linkText);
        }
        // NOTE may need to set a null value here in case webservices don't like it unset
        //      so we'll set it just in case...
        else
        {
            $object[$linkText] = null;
        }

        return $object;
    }

    /**
     * This function takes a class object and converts it to an array structure
     * via var_export (which returns a useable PHP string for creating the object from array content)
     * and regular expressions to extract the array definitions and structure without the class specific code
     *
     * NOTE this function is not reliable for objects which contain ktapi instances, as it appears there is a recursive reference
     * TODO attempt to deal with recursive references?
     *
     * @param object $data
     * @return array $array
     */
    static public function objectToArray($data)
    {
        $array = array();

        $stringdata = var_export($data, true);
        // clean up ", )" - NOTE this may not be necessary
        $stringdata = preg_replace('/, *\r?\n? *\)/', ')', $stringdata);

        // NOTE is this while loop even needed?
        while (preg_match('/\b[\w]*::__set_state\(/', $stringdata, $matches))
        {
            $stringdata = preg_replace('/\b[\w]*::__set_state\(/', $matches[1], $stringdata);
        }

        // remove end parentheses, should come in pairs to be reduced by 1
        $stringdata = '$array = ' . preg_replace('/\)\)/', ')', $stringdata) . ';';
        eval($stringdata);

        return $array;
    }

    /**
     * Converts a boolean value to string representation
     * If input is not true or false, return unaltered
     *
     * @param boolean/other $input
     * @return string
     */
    static public function boolToString($input)
    {
        return (($input === true) ? 'true' : (($input === false) ? 'false' : $input));
    }

    /**
     * Creates a temporary file
     * Cleanup is the responsibility of the calling code
     *
     * @param string|binary $content The content to be written to the file.
     * @param string $uploadDir Optional upload directory.  Will use the KnowledgeTree system tmp directory if not supplied.
     * @return string The path to the created file (for reference and cleanup.)
     */
    static public function createTemporaryFile($content, $encoding = null, $uploadDir = null)
    {
        if(is_null($uploadDir))
        {
            $oKTConfig =& KTConfig::getSingleton();
            $uploadDir = $oKTConfig->get('webservice/uploadDirectory');
        }

        $temp = tempnam($uploadDir, 'myfile');
        $fp = fopen($temp, 'wb');
        fwrite($fp, ($encoding == 'base64' ? base64_decode($content) : $content));
        fclose($fp);

        return $temp;
    }

}

?>
