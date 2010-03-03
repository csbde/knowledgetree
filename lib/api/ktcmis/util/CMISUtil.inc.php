<?php
/**
 * CMIS Helper class for KnowledgeTree.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
 * 
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
 * Contributor( s): ______________________________________
 */

/**
 *
 * @copyright 2008-2010, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package KTCMIS
 * @version Version 0.1
 */

define('UNKNOWN', -1);
define('DOCUMENT', 1);
define('FOLDER', 2);

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
            case 'cmis:document':
            case DOCUMENT:
                $encoded = 'D' . $objectId;
                break;
            case 'F':
            case 'Folder':
            case 'cmis:folder':
            case FOLDER:
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
    static public function decodeObjectId($objectId, &$typeId = null, &$className = '')
    {
        if (!is_string($objectId))
        {
            $typeId = 'unknown';
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
            $typeId = 'cmis:folder';
            $className = 'Folder';
            return '1';
        }

        preg_match('/(\D)(\d*)/', $objectId, $matches);
        $type = $matches[1];
        $objectId = $matches[2];

        switch($type)
        {
            case 'D':
                $typeId = 'cmis:document';
                $className = 'Document';
                break;
            case 'F':
                $typeId = 'cmis:folder';
                $className = 'Folder';
                break;
            default:
                $typeId = 'unknown';
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
                    if (count($object['items']) > 0) {
                        $CMISArray[$count]['children'] = self::createChildObjectHierarchy($object['items'], $repositoryURI, $ktapi);
                    }
                }
                else
                {
                    // NOTE why is this necessary?  That's what you get for not commenting it at the time
                    // TODO comment this properly, once we know why it is happening
                    //                    $CMISArray[$count] = self::createChildObjectHierarchy($object, $repositoryURI, $ktapi);
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
            if (count($input) > 0) {
                $CMISElement['parents'] = self::createParentObjectHierarchy($input, $repositoryURI, $ktapi);
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
     * @param string $linkText // 'children' or 'parents' - indicates direction of hierarchy => descending or ascending
     * @return array $hierarchy
     */
    static public function decodeObjectHierarchy($input, $linkText = 'children')
    {
        $hierarchy = array();

        // first, run through the base array to get the initial children
        foreach ($input as $key => $entry)
        {
            $object = $entry['object'];
            $properties = $object->getProperties();
            $hierarchy[$key] = self::createObjectPropertiesEntry($properties);

            if (isset($entry[$linkText]) && count($entry[$linkText])) {
                $hierarchy[$key][$linkText] = self::decodeObjectHierarchy($entry[$linkText], $linkText);
            }
        }

        return $hierarchy;
    }

    /**
     * Creates a properties entry array for the given entry
     * 
     * TODO make this just dynamically convert all properties instead of selected.
     * NOTE current version is a legacy of how we thought things needed to be for the SOAP webservices.
     * 
     * @param object $properties
     * @return 
     */
    static public function createObjectPropertiesEntry($properties)
    {
        $object = array();

        foreach(CMISPropertyCollection::$propertyTypes as $property => $type)
        {
            // hack for Author property
            if ($property == 'author') {
                $object[$property] = array('value' => $properties->getValue($property));
            }
            else {
                $object['properties'][$property] = array('type' => $type, 'value' => $properties->getValue($property));
            }
        }

        return $object;
    }

    /**
     * This function takes a class object and converts it to an array structure
     * via var_export (which returns a useable PHP string for creating the object from array content)
     * and regular expressions to extract the array definitions and structure without the class specific code
     *
     * NOTE this function is not reliable for objects which contain ktapi instances, as it appears there is a recursive reference;
     *      this will apply to any class which contains recursive references (which is bad practice and should not be done - the
     *      problem is with the object and not this code
     * 
     * TODO attempt to deal with recursive references? - better to fix the objects in question, if possible
     *
     * @param object $data
     * @return array $array
     */
    static public function objectToArray($data)
    {
        $array = array();

        $stringdata = var_export($data, true);
        // clean up ", )" - NOTE this may not be necessary, but is included for safety
        $stringdata = preg_replace('/, *\r?\n? *\)/', ')', $stringdata);

        // NOTE is this while loop even needed, or can we just run a preg_replace without the while?
        // TODO find out...
        while (preg_match('/\b[\w]*::__set_state\(/', $stringdata, $matches)) {
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

    // TODO more robust base64 encoding detection, if possible

    /**
     * Checks the contentStream and ensures that it is a correct base64 string;
     * This is purely for clients such as CMISSpaces breaking the content into 
     * chunks before base64 encoding each and this coming through as a single string containing multiple base64 chunks.
     * 
     * If the stream is chunked, it is decoded in chunks and sent back as a single stream.
     * If it is not chunked it is decoded as is and sent back as a single stream.
     * 
     * NOTE there is an alternative version of this function called decodeChunkedContentStreamLong.
     *      that version checks line lengths, which should not be necessary.
     *      this version merely splits on one or two "=" which is less complex and appears to be faster
     *      (one or two "=" signs is the specified padding used for base64 encoding at the end of an encoded string, when needed)
     * 
     * @param object $contentStream
     * @return string decoded
     */
    static public function decodeChunkedContentStream($contentStream)
    {
        // always trim content, just in case, as the AtomPub specification says content may be padded with whitespace at the start and end.
        $contentStream = trim($contentStream);

        // check whether the content is encoded first, return as is if not
        // A–Z, a–z, 0–9, +, /
        // NOTE this makes the (fairly reasonable) assumption that text content contains at least one space or punctuation character.
        //      of course this may fail should something be sent in plain text such as a passwords file containing sha1 or md5 hashes only.
        if (preg_match('/[^\w\/\+=\n]+/', $content)) return $contentStream;

        $decoded = '';

        // split the content stream on ={1,2}
        $parts = preg_split('/(={1,2})/', $contentStream, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        foreach($parts as $part)
        {
            if (preg_match('/={1,2}/', $part)) {
                continue;
            }

            // lookahead for delimiter, because we may need it back.
            // NOTE that decoding appears to work fine without this, so this is just an "in case".
            // NOTE that even with this it seems the present function works faster than the alternative below.
            if (isset($parts[$key+1])) {
                if (preg_match('/={1,2}/', $parts[$key+1])) {
                    $part .= $parts[$key+1];
                }
            }

            // decode, append to output
            $decoded .= base64_decode($part);
        }

        return $decoded;
    }

    /**
     * Checks the contentStream and ensures that it is a correct base64 string;
     * This is purely for clients such as CMISSpaces breaking the content into 
     * chunks before base64 encoding.
     * 
     * If the stream is chunked, it is decoded in chunks and sent back as a single stream.
     * If it is not chunked it is decoded as is and sent back as a single stream.
     * 
     * NOTE this function and the above need to be checked for efficiency.
     *      The other one appears to be miles better (1/0/3 vs 14/4/57 on respective test files)
     * 
     * @param object $contentStream
     * @return string decoded
     */
    static public function decodeChunkedContentStreamLong($contentStream)
    {
        // always trim content, just in case, as the AtomPub specification says content may be padded with whitespace at the start and end.
        $contentStream = trim($contentStream);

        // check whether the content is encoded first, return as is if not
        // A–Z, a–z, 0–9, +, /
        // NOTE this makes the (fairly reasonable) assumption that text content contains at least one space or punctuation character.
        //      of course this may fail should something be sent in plain text such as a passwords file containing sha1 or md5 hashes only.
        if (preg_match('/[^\w\/\+=\n]+/', $content)) return $contentStream;

        // check the content stream for any lines of unusual length (except the last line, which can be any length)
        $count = -1;
        $length = 0;
        $b64String = '';
        $outputStream = '';
        $decode = array();
        $chunks = 1;
        $decoded = '';
        $chunked = '';

        $splitStream = explode("\n", $contentStream);
        foreach ($splitStream as $line)
        {
            $curlen = strlen($line);

            if ($length == 0) {
                $length = $curlen;
            }

            // if we find one we know that we must split the line here and end the previous base64 string
            if ($curlen > $length)
            {
                // check for a new chunk
                // either we have an equals sign (or two)
                if (preg_match('/([^=]*={1,2})(.*)/', $line, $matches))
                {
                    $lastChunk = $matches[1];
                    $nextChunk = $matches[2];
                }
                // or we need to try by line length
                else {
                    $lastChunk = substr($line, 0, $curlen - $length);
                    $nextChunk = substr($line, $curlen - $length);
                }

                $decode[++$count] = $b64String . $lastChunk;

                $b64String = $nextChunk . "\n";
                $length = strlen($nextChunk);

                ++$chunks;
            }
            else {
                $b64String .= $line . "\n";
            }
        }

        // anything left over
        if (!empty($b64String)) {
            $decode[] = $b64String;
        }

        if ($chunks > 1)
        {
            foreach($decode as $code) {
                // decode, append to output to be re-encoded
                $chunked .= base64_decode($code);
            }

            $decoded = $chunked;
        }
        else {
            $decoded = base64_decode($decode[0]);
        }

        return $decoded;
    }

    /**
     * Function to check whether a specified object exists within the KnowledgeTree system
     * 
     * @param string $typeId
     * @param string $objectId
     * @param object $ktapi
     * @return boolean
     */
    public function contentExists($typeId, $objectId, &$ktapi)
    {
        $exists = true;
        if ($typeId == 'cmis:folder')
        {
            $object = $ktapi->get_folder_by_id($objectId);
            if (PEAR::isError($object)) {
                $exists = false;
            }
        }
        else if ($typeId == 'cmis:document')
        {
            $object = $ktapi->get_document_by_id($objectId);
            if (PEAR::isError($object)) {
                $exists = false;
            }
            // TODO check deleted status?
        }
        else {
            $exists = false;
        }

        return $exists;
    }

    /**
     * Creates a temporary file
     * Cleanup is the responsibility of the calling code
     *
     * @param string $contentStream The content to be stored (assumed to be base64)
     * @return string The path to the created file (for reference and cleanup.)
     */
    static public function createTemporaryFile($contentStream)
    {
        // if contentStream is empty, cannot create file
        if (empty($contentStream)) return null;

        // TODO consider checking whether content is encoded (currently we expect encoded)
        // TODO choose between this and the alternative decode function (see CMISUtil class)
        //      this will require some basic benchmarking
        $contentStream = self::decodeChunkedContentStream($contentStream);

        // NOTE There is a function in CMISUtil to do this, written for the unit tests but since KTUploadManager exists
        //      and has more functionality which could come in useful at some point I decided to go with that instead
        //      (did not know this existed when I wrote the CMISUtil function)
        $uploadManager = new KTUploadManager();
        // assumes already decoded from base64, should use store_base64_file if not
        $tempfilename = $uploadManager->store_file($contentStream, 'cmis_');

        return $tempfilename;
    }

    /**
     * attempts to fetch the folder id from a name
     * 
     * NOTE this won't be reliable if there is more than one folder in the system with the same name
     *      the only reason this exists is to accomodate the method of browsing used by the drupal module
     *
     * @param string $name
     * @param object $ktapi
     * @return string
     */
    static public function getIdFromName($name, &$ktapi)
    {
        $folder = $ktapi->get_folder_by_name($name);

        return self::encodeObjectId(FOLDER, $folder->get_folderid());
    }

    /**
     * Checks for the root folder
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param object $ktapi
     * @return boolean
     */
    static public function isRootFolder($repositoryId, $folderId, &$ktapi)
    {
        $repository = new CMISRepository($repositoryId);
        $repositoryInfo = $repository->getRepositoryInfo();

        // NOTE this call is required to accomodate the definition of the root folder id in the config as required by the drupal module
        //      we should try to update the drupal module to not require this, but this way is just easier at the moment, and most of
        //      the code accomodates it without any serious hacks
        $rootFolder = self::getIdFromName($repositoryInfo->getRootFolderId(), $ktapi);

        return $folderId == $rootFolder;
    }

}

?>
