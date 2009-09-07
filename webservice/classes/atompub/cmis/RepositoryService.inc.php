<?php

/**
 * CMIS Service class which hooks into the KnowledgeTree interface
 * for processing of CMIS queries and responses via atompub/webservices
 */

require_once KT_LIB_DIR . '/api/ktcmis/ktRepositoryService.inc.php';

class RepositoryService extends KTRepositoryService {

    /**
    * Fetches a list of available repositories
    *
    * @return cmisRepositoryEntryType[]
    */
    public function getRepositories()
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
    public function getRepositoryInfo($repositoryId)
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
    public function getTypes($repositoryId, $typeId = '', $returnPropertyDefinitions = false,
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
    public function getTypeDefinition($repositoryId, $typeId)
    {
        $result = parent::getTypeDefinition($repositoryId, $typeId);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }

}

?>
