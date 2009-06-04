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
    static function encodeObjectId($typeId, $objectId)
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
     * @return string $typeId
     */
    static function decodeObjectId(&$objectId)
    {
        $typeId = null;

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

        return $typeId;
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
    static function createChildObjectHierarchy($input, $repositoryURI, &$ktapi)
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
                            $CMISObject = new CMISDocumentObject($ktapi, $repositoryURI);
                            break;
                        case 'F':
                            $CMISObject = new CMISFolderObject($ktapi, $repositoryURI);
                            break;
                    }
                    $CMISObject->get($object['id']);
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
    static function createParentObjectHierarchy($input, $repositoryURI, &$ktapi)
    {
        $CMISArray = array();

        if (count($input) <= 0) return $CMISArray;

        $object = array_shift($input);
        $detail = $object->get_detail();

        if (isset($detail['id']))
        {
            $CMISObject = new CMISFolderObject($ktapi, $repositoryURI);
            $CMISObject->get($detail['id']);
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
    static function decodeObjectHierarchy($input, $linkText)
    {
        $hierarchy = array();
        
        // first, run through the base array to get the initial children
        foreach ($input as $key => $entry)
        {
            $object = $entry['object'];
            $properties = $object->getProperties();

            $hierarchy[$key]['properties']['objectId'] = $properties->getValue('objectId');
            $hierarchy[$key]['properties']['typeId'] = $properties->getValue('typeId');
            $hierarchy[$key]['properties']['name'] = $properties->getValue('name');
            
            // if we have found a child/parent with one or more children/parents, recurse into the child/parent object
            if (count($entry['items']) > 0)
            {
                $hierarchy[$key][$linkText] = CMISUtil::decodeObjectHierarchy($entry['items'], $linkText);
            }
            // NOTE may need to set a null value here in case webservices don't like it unset
            //      so we'll set it just in case...
            else
            {
                $hierarchy[$key][$linkText] = null;
            }
        }

        return $hierarchy;
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
    static function objectToArray($data)
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

}

?>
