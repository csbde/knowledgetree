<?php

require_once(realpath(dirname(__FILE__)) . '/../../../../ktcmis/ktcmis.inc.php');

/**
 * CMIS Service class which hooks into the KnowledgeTree interface
 * for processing of CMIS queries and responses via webservices
 */

class CMISService extends KTCMIS {
    
    /**
    * Fetches a list of available repositories
    *
    * @return cmisRepositoryEntryType[]
    */
    function getRepositories()
    {
        $result = parent::getRepositories();

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }
    
    /**
     * Fetches information about the selected repository
     *
     * @param string $repositoryId
     * @return cmisRepositoryInfoType
     */
    function getRepositoryInfo($repositoryId)
    {
        $result = parent::getRepositoryInfo($repositoryId);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }

    /**
     * Fetch the list of supported object types for the selected repository
     *
     * @param string $repositoryId The ID of the repository for which object types must be returned
     * @param string $typeId The type to return, ALL if not set
     * @param boolean $returnPropertyDefinitions Return property definitions as well if TRUE
     * @param int $maxItems The maximum number of items to return
     * @param int $skipCount The number of items to skip before starting to return results
     * @param boolean $hasMoreItems TRUE if there are more items to return than were requested
     * @return cmisTypeDefinitionType[]
     */
    function getTypes($repositoryId, $typeId = '', $returnPropertyDefinitions = false,
                      $maxItems = 0, $skipCount = 0, &$hasMoreItems = false)
    {
        $result = parent::getTypes($repositoryId, $typeId, $returnPropertyDefinitions,
                                $maxItems, $skipCount, $hasMoreItems);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }

    /**
     * Fetch the object type definition for the requested type
     *
     * @param string $repositoryId
     * @param string $typeId
     * @return cmisTypeDefinitionType
     */
    function getTypeDefinition($repositoryId, $typeId)
    {
        $result = parent::getTypeDefinition($repositoryId, $typeId);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }

    // Navigation service functions

    /**
     * Get descendents of the specified folder, up to the depth indicated
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param boolean $includeAllowableActions
     * @param boolean $includeRelationships
     * @param string $typeID
     * @param int $depth
     * @param string $filter
     * @return cmisObjectType[]
     */
    function getDescendants($repositoryId, $folderId, $includeAllowableActions, $includeRelationships,
                            $depth = 1, $typeID = 'Any', $filter = '')
    {
        $result = parent::getDescendants($repositoryId, $folderId, $includeAllowableActions,
                                         $includeRelationships, $depth, $typeID, $filter);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }

    /**
     * Get direct children of the specified folder
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param boolean $includeAllowableActions
     * @param boolean $includeRelationships
     * @param string $typeID
     * @param string $filter
     * @param int $maxItems
     * @param int $skipCount
     * @return cmisObjectType[]
     */
    function getChildren($repositoryId, $folderId, $includeAllowableActions, $includeRelationships,
                         $typeID = 'Any', $filter = '', $maxItems = 0, $skipCount = 0)
    {
        $result =  parent::getChildren($repositoryId, $folderId, $includeAllowableActions, $includeRelationships,
                                       $typeID, $filter, $maxItems, $skipCount);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }

    /**
     * Gets the parent of the selected folder
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param boolean $includeAllowableActions
     * @param boolean $includeRelationships
     * @param boolean $returnToRoot
     * @param string $filter
     * @return cmisObjectType[]
     */
    function getFolderParent($repositoryId, $folderId, $includeAllowableActions, $includeRelationships, $returnToRoot, $filter = '')
    {
        $result = parent::getFolderParent($repositoryId, $folderId, $includeAllowableActions, $includeRelationships, $returnToRoot, $filter);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }

    /**
     * Gets the parents for the selected object
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param boolean $includeAllowableActions
     * @param boolean $includeRelationships
     * @param string $filter
     * @return cmisObjectType[]
     */
    function getObjectParents($repositoryId, $objectId, $includeAllowableActions, $includeRelationships, $filter = '')
    {
        $result = parent::getObjectParents($repositoryId, $objectId, $includeAllowableActions, $includeRelationships, $filter);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }

    /**
     * Gets the properties for the selected object
     *
     * @param string $repositoryId
     * @param string $objectId
     * @param boolean $includeAllowableActions
     * @param boolean $includeRelationships
     * @param string $returnVersion
     * @param string $filter
     * @return cmisObjectPropertiesType[]
     */
    function getProperties($repositoryId, $objectId, $includeAllowableActions, $includeRelationships,
                           $returnVersion = false, $filter = '')
    {
        $result = parent::getProperties($repositoryId, $objectId, $includeAllowableActions, $includeRelationships, $returnVersion, $filter);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }
}

?>
