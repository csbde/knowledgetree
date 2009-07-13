<?php

require_once KT_LIB_DIR . '/api/ktcmis/ktcmis.inc.php';

/**
 * CMIS Service class which hooks into the KnowledgeTree interface
 * for processing of CMIS queries and responses via atompub/webservices
 */

class ObjectService extends KTObjectService {

    /**
     * Fetches the properties for the specified object
     *
     * @param string $repositoryId
     * @param string $objectId
     * @param boolean $includeAllowableActions
     * @param boolean $includeRelationships
     * @param boolean $returnVersion
     * @param string $filter
     * @return object CMIS object properties
     */
    public function getProperties($repositoryId, $objectId, $includeAllowableActions, $includeRelationships,
                                  $returnVersion = false, $filter = '')
    {
        $result = parent::getProperties($repositoryId, $objectId, $includeAllowableActions,
                                        $returnVersion, $filter);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }

    /**
     * Creates a new folder within the repository
     *
     * @param string $repositoryId The repository to which the folder must be added
     * @param string $typeId Object Type id for the folder object being created
     * @param array $properties Array of properties which must be applied to the created folder object
     * @param string $folderId The id of the folder which will be the parent of the created folder object
     * @return string $objectId The id of the created folder object
     */
    function createFolder($repositoryId, $typeId, $properties, $folderId)
    {
        $result = parent::createFolder($repositoryId, $typeId, $properties, $folderId);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
        else
        {
            return $result;
        }
    }

    /**
     * Creates a new document within the repository
     *
     * @param string $repositoryId The repository to which the document must be added
     * @param string $typeId Object Type id for the document object being created
     * @param array $properties Array of properties which must be applied to the created document object
     * @param string $folderId The id of the folder which will be the parent of the created document object
     *                         This parameter is optional IF unfilingCapability is supported
     * @param contentStream $contentStream optional content stream data
     * @param string $versioningState optional version state value: checkedout/major/minor
     * @return string $objectId The id of the created folder object
     */
    // TODO throw ConstraintViolationException if:
    //      value of any of the properties violates the min/max/required/length constraints
    //      specified in the property definition in the Object-Type.
    function createDocument($repositoryId, $typeId, $properties, $folderId = null,
                            $contentStream = null, $versioningState = null)
    {
        $result = parent::createDocument($repositoryId, $typeId, $properties, $folderId, $contentStream, $versioningState);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
        else
        {
            return $result;
        }
    }

}

?>
