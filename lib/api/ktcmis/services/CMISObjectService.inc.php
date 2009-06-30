<?php

// really wanted to keep KT code out of here but I don't see how
require_once(KT_DIR . '/ktapi/ktapi.inc.php');
require_once(CMIS_DIR . '/objecttypes/CMISDocumentObject.inc.php');
require_once(CMIS_DIR . '/objecttypes/CMISFolderObject.inc.php');
require_once(CMIS_DIR . '/classes/CMISRepository.inc.php');
require_once(CMIS_DIR . '/util/CMISUtil.inc.php');

class CMISObjectService {

    protected $ktapi;

    /**
     * Sets the interface to be used to query the repository
     *
     * @param object $ktapi The KnowledgeTree API interface
     */
    function setInterface(&$ktapi)
    {
        $this->ktapi = $ktapi;
    }

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
    // TODO optional parameter support
    // TODO FilterNotValidException: The Repository SHALL throw this exception if this property filter input parameter is not valid
    function getProperties($repositoryId, $objectId, $includeAllowableActions, $includeRelationships,
                           $returnVersion = false, $filter = '')
    {
        $repository = new CMISRepository($repositoryId);

        // TODO a better default value?
        $properties = array();

        $typeId = CMISUtil::decodeObjectId($objectId);

        switch($typeId)
        {
            case 'Document':
                $CMISObject = new CMISDocumentObject($this->ktapi, $repository->getRepositoryURI());
                break;
            case 'Folder':
                $CMISObject = new CMISFolderObject($this->ktapi, $repository->getRepositoryURI());
                break;
        }

        $CMISObject->get($objectId);
        $properties = $CMISObject->getProperties();

        return $properties;
    }

    /**
     * Function to create a folder
     *
     * @param string $repositoryId The repository to which the folder must be added
     * @param string $typeId Object Type id for the folder object being created
     * @param array $properties Array of properties which must be applied to the created folder object
     * @param string $folderId The id of the folder which will be the parent of the created folder object
     * @return string $objectId The id of the created folder object
     */
    // TODO throw ConstraintViolationException if:
    //      typeID is not an Object-Type whose baseType is “Folder”.
    //      value of any of the properties violates the min/max/required/length constraints
    //      specified in the property definition in the Object-Type.
    //      typeID is NOT in the list of AllowedChildObjectTypeIds of the parent-folder specified by folderId
    // TODO throw storageException under conditions specified in "specific exceptions" section
    function createFolder($repositoryId, $typeId, $properties, $folderId)
    {
        $objectId = null;

        // TODO determine whether this is in fact necessary or if we should require decoding in the calling code
        // Attempt to decode $folderId, use as is if not detected as encoded
        $objectId = $folderId;
        $tmpTypeId = CMISUtil::decodeObjectId($objectId);
        if ($tmpTypeId != 'Unknown')
            $folderId = $objectId;

        $response = $this->ktapi->create_folder($folderId, $properties['name'], $sig_username = '', $sig_password = '', $reason = '');
        if ($response['status_code'] != 0)
        {
            // TODO add some error handling here
        }
        else
        {
            $objectId = CMISUtil::encodeObjectId('Folder', $response['results']['id']);
        }

        return $objectId;
    }

}

?>
