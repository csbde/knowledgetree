<?php

require_once KT_LIB_DIR . '/api/ktcmis/ktNavigationService.inc.php';

/**
 * CMIS Service class which hooks into the KnowledgeTree interface
 * for processing of CMIS queries and responses via atompub/webservices
 */

class NavigationService extends KTNavigationService {

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
    public function getDescendants($repositoryId, $folderId, $includeAllowableActions, $includeRelationships,
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
    public function getChildren($repositoryId, $folderId, $includeAllowableActions, $includeRelationships,
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
    public function getFolderParent($repositoryId, $folderId, $includeAllowableActions, $includeRelationships, $returnToRoot, $filter = '')
    {
        $result = parent::getFolderParent($repositoryId, $folderId, $includeAllowableActions, $includeRelationships, $returnToRoot, $filter);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
        else {
            return new PEAR_Error($result['message']);
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
    public function getObjectParents($repositoryId, $objectId, $includeAllowableActions, $includeRelationships, $filter = '')
    {
        $result = parent::getObjectParents($repositoryId, $objectId, $includeAllowableActions, $includeRelationships, $filter);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }

    /**
     * Returns a list of checked out documents from the selected repository
     *
     * @param string $repositoryId
     * @param string $folderId The folder for which checked out docs are requested
     * @param string $filter
     * @param int $maxItems
     * @param int $skipCount
     * @return array $checkedout The collection of checked out documents
     */
    function getCheckedOutDocs($repositoryId, $folderId = null, $filter = '', $maxItems = 0, $skipCount = 0)
    {
        $result = parent::getCheckedOutDocs($repositoryId, $folderId, $filter, $maxItems, $skipCount);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }

}

?>
