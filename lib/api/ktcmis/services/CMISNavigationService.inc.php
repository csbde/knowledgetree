<?php
/**
 * CMIS Repository Navigation API class for KnowledgeTree.
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

require_once(KT_DIR . '/ktapi/ktapi.inc.php');
require_once(CMIS_DIR . '/util/CMISUtil.inc.php');

class CMISNavigationService {

    protected $ktapi;

    /**
     * Sets the interface to be used to interact with the repository
     *
     * @param object $ktapi The KnowledgeTree API interface
     */
    function setInterface(&$ktapi)
    {
        $this->ktapi = $ktapi;
    }

    /**
     * Get direct children of the specified folder
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param boolean $includeAllowableActions
     * @param boolean $includeRelationships
     * @param string $typeId
     * @param string $filter
     * @param int $maxItems
     * @param int $skipCount
     * @return array $descendants
     *               MUST include (unless not requested) for each object:
     *               array $properties
     *               array $relationships
     *               array $renditions
     *               $allowableActions
     *               string $pathSegment
     *        boolean $hasMoreItems
     *        int $numItems [optional]
     */
    // NOTE If the Repository supports the optional “VersionSpecificFiling��? capability,
    //      then the repository SHALL return the document versions filed in the specified folder or its descendant folders.
    //      Otherwise, the latest version of the documents SHALL be returned.
    // TODO FilterNotValidException: The Repository SHALL throw this exception if this property filter input parameter is not valid
    function getChildren($repositoryId, $folderId, $includeAllowableActions = null, $includeRelationships = null,
    $typeId = 'Any', $filter = '', $maxItems = 0, $skipCount = 0, $orderBy = '', $renditionFilter = null,
    $includePathSegment = false)
    {
        // TODO paging
        // TODO optional parameters
        $children = array();
        $repository = new CMISRepository($repositoryId);

        // if this is not a folder, cannot get children
        $folderId = CMISUtil::decodeObjectId($folderId, $type);

        if ($type != 'cmis:folder') {
            throw new InvalidArgumentException('The specified object is not a folder');
        }

        $folder = $this->ktapi->get_folder_by_id($folderId);
        if (PEAR::isError($folder)) {
            throw new ObjectNotFoundException('The requested folder does not exist or cannot be accessed');
        }
        $children = $folder->get_listing();

        $children = CMISUtil::createChildObjectHierarchy($children, $repository->getRepositoryURI, $this->ktapi);
        return $children;
    }

    /**
     * Get descendents of the specified folder, up to the depth indicated
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param int $depth
     * @param string $filter
     * @param boolean $includeRelationships
     * @param string $renditionFilter
     * @param boolean $includeAllowableActions
     * @param boolean $includePathSegment
     * @return array $descendants
     *               MUST include (unless not requested) for each object:
     *               array $properties
     *               array $relationships
     *               array $renditions
     *               $allowableActions
     *               string $pathSegment
     */

    // NOTE This method does NOT support paging as defined in the paging section
    // NOTE If the Repository supports the optional “VersionSpecificFiling��? capability,
    //      then the repository SHALL return the document versions filed in the specified folder or its descendant folders.
    //      Otherwise, the latest version of the documents SHALL be returned.
    // NOTE If the Repository supports the optional capability capabilityMutlifiling and the same document is encountered
    //      multiple times in the hierarchy, then the repository MUST return that document each time is encountered.
    // NOTE The default value for the $depth parameter is repository specific and SHOULD be at least 2 or -1
    //      Chosen 2 as the underlying code currently has no concept of digging all the way down
    // TODO FilterNotValidException: The Repository SHALL throw this exception if this property filter input parameter is not valid
    // TODO a depth of -1 (or possibly any negative number) should return all children - ktapi does not support arbitrary depths
    function getDescendants($repositoryId, $folderId, $depth = 2, $filter = '', $includeRelationships = false, $renditionFilter = '',
    $includeAllowableActions = false, $includePathSegment = false)
    {
        if ($depth == 0) {
            throw new InvalidArgumentException('Invalid depth argument supplied');
        }

        // if this is not a folder, cannot get descendants
        $folderId = CMISUtil::decodeObjectId($folderId, $type);

        if ($type != 'cmis:folder') {
            throw new InvalidArgumentException('The supplied object is not a folder, unable to return descendants');
        }

        // TODO optional parameters
        $descendants = array();
        $repository = new CMISRepository($repositoryId);

        $folder = $this->ktapi->get_folder_by_id($folderId);
        if (PEAR::isError($folder)) {
            throw new ObjectNotFoundException($folder->getMessage());
        }
        $descendants = $folder->get_listing($depth);

        // parse ktapi descendants result into a list of CMIS objects
        $descendants = CMISUtil::createChildObjectHierarchy($descendants, $repository->getRepositoryURI, $this->ktapi);
        return $descendants;
    }

    /**
     * Fetches the folder parent and optional ancestors
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param string $filter
     * @return object $parent The parent folder object
     */
    // TODO FilterNotValidException: The Repository SHALL throw this exception if this property filter input parameter is not valid
    function getFolderParent($repositoryId, $folderId, $filter = '')
    {
        // NOTE the root folder obviously has no parent, throw an ObjectNotFoundException here if this is the root folder
        if (CMISUtil::isRootFolder($repositoryId, $folderId, $this->ktapi)) {
            throw new InvalidArgumentException('Root folder has no parent');
        }

        $parent = null;

        // if this is not a folder, cannot get folder parent :)
        $folderId = CMISUtil::decodeObjectId($folderId, $type);
        // this exception is not indicated in the CMIS Specification, but it just makes sense and so we include it here
        if ($type != 'cmis:folder') {
            throw new InvalidArgumentException('The specified object is not a folder');
        }

        $ktapiFolder = $this->ktapi->get_folder_by_id($folderId);
        if (PEAR::isError($ktapiFolder)) {
            throw new ObjectNotFoundException($ktapiFolder->getMessage());
        }

        $parent = new CMISFolderObject($ktapiFolder->get_parent_folder_id(), $this->ktapi);
        return $parent;
    }

    /**
     * Gets the parent folder(s) for the specified non-folder, fileable object.
     * Multiple parents may exist if a repository supports multi-filing
     * It is also possible that linked documents/folders may qualify as having multiple parents
     * as they are essentially the same object
     *
     * @param string $repositoryId
     * @param string $objectId
     * @param string $filter [optional]
     * @param enum $includeRelationships [optional]
     * @param string $renditionFilter  [optional]
     * @param boolean $includeAllowableActions [optional]
     * @param boolean $includeRelativePathSegment [optional]
     * @return array $parents - empty for unfiled objects or the root folder
     *               MUST include (unless not requested) for each object:
     *               array $properties
     *               array $relationships
     *               array $renditions
     *               $allowableActions
     *               string $relativePathSegment
     */
    // TODO FilterNotValidException: The Repository SHALL throw this exception if this property filter input parameter is not valid.
    function getObjectParents($repositoryId, $objectId, $filter = '', $includeRelationships = null, $renditionFilter = '',
                              $includeAllowableActions = false, $includeRelativePathSegment = false)
    {
        $ancestry = array();

        $objectId = CMISUtil::decodeObjectId($objectId, $typeId);
        
        // if type is a folder, this function does not apply
        if ($typeId == 'cmis:folder') {
            throw new InvalidArgumentException('Cannot call this function for a folder object');
        }
        
        $objectTypeId = ucwords(str_replace('cmis:', '', $typeId));
        $object = 'CMIS' . $objectTypeId . 'Object';
        
        if (!file_exists(CMIS_DIR . '/objecttypes/' . $object . '.inc.php')) {
            throw new InvalidArgumentException('Type ' . $typeId . ' is not supported');
        }

        require_once(CMIS_DIR . '/objecttypes/' . $object . '.inc.php');
        $cmisObject = new $object;
        
        if (!$cmisObject->getAttribute('fileable')) {
            throw new ConstraintViolationException('Unable to get parents of non-filable object');
        }

        // TODO - what about other types?  only implementing folders and documents at the moment so ignore for now
        // NOTE this will change if we implement multi-filing and/or unfiling
        switch($typeId)
        {
            case 'cmis:document':
                $document = $this->ktapi->get_document_by_id($objectId);
                if ($document->is_deleted()) {
                    throw new InvalidArgumentException('The requested object has been deleted');
                }
                $ancestry[] = $document->ktapi_folder->get_folderid();
            break;
        }
        
        foreach ($ancestry as $key => $parentId) {
            $CMISObject = new CMISFolderObject($parentId, $this->ktapi, $repositoryURI);
            $ancestry[$key] = CMISUtil::createObjectPropertiesEntry($CMISObject->getProperties());
        }

        return $ancestry;
    }

    /**
     * Returns a list of checked out documents from the selected repository
     *
     * @param string $repositoryId
     * @param string $folderId The folder for which checked out docs are requested
     * @param int $maxItems
     * @param int $skipCount
     * @param string $filter
     * @param enum $includeRelationships
     * @param boolean $includeAllowableActions
     * @param string $renditionFilter
     * @return array $checkedout The collection of checked out document objects
     *               MUST include (unless not requested) for each object:
     *               array $properties
     *               array $relationships
     *               array $renditions
     *               $allowableActions
     * @return boolean $hasMoreItems
     * @return int $numItems [optional]
     */
    // TODO exceptions: •	FilterNotValidException: The Repository SHALL throw this exception if this property filter input parameter is not valid.
    // TODO $filter and paging
    function getCheckedOutDocs($repositoryId, $folderId = null, $maxItems = 0, $skipCount = 0, $orderBy = '',
    $filter = '', $includeRelationships = null, $includeAllowableActions = false, $renditionFilter = '')
    {
        $checkedout = array();

        $results = $this->ktapi->get_checkedout_docs(false);

        // not actually sure that a PEAR error ever could be returned, revisit when looking at error handling in KTAPI code
        if (PEAR::isError($results)) {
            throw new RuntimeException('Failed getting list of checked out documents');
        }

        foreach($results as $document) {
            $CMISDocument = new CMISDocumentObject($document->getId(), $this->ktapi);
            // set version label property - possibly belongs in document class
            $CMISDocument->setProperty('versionLabel', $CMISDocument->getProperty('versionSeriesCheckedOutId'));
            $checkedout[] = $CMISDocument->getProperties();
        }

        return $checkedout;
    }

}

?>
