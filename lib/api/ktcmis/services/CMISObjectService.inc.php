<?php

require_once(KT_DIR . '/ktapi/ktapi.inc.php');
require_once(CMIS_DIR . '/exceptions/ConstraintViolationException.inc.php');
require_once(CMIS_DIR . '/exceptions/ObjectNotFoundException.inc.php');
require_once(CMIS_DIR . '/exceptions/StorageException.inc.php');
require_once(CMIS_DIR . '/services/CMISRepositoryService.inc.php');
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

        if ($typeId == 'Unknown')
        {
            throw new ObjectNotFoundException('The type of the requested object could not be determined');
        }

        switch($typeId)
        {
            case 'Document':
                $CMISObject = new CMISDocumentObject($objectId, $this->ktapi, $repository->getRepositoryURI());
                break;
            case 'Folder':
                $CMISObject = new CMISFolderObject($objectId, $this->ktapi, $repository->getRepositoryURI());
                break;
        }

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
    //      value of any of the properties violates the min/max/required/length constraints
    //      specified in the property definition in the Object-Type.
    function createFolder($repositoryId, $typeId, $properties, $folderId)
    {
        // fetch type definition of supplied type and check for base type "folder", if not true throw exception
        $RepositoryService = new CMISRepositoryService();
        try {
            $typeDefinition = $RepositoryService->getTypeDefinition($repositoryId, $typeId);
        }
        catch (Exception $e)
        {
            throw new ConstraintViolationException('Object is not of base type folder.');
        }
        
        if ($typeDefinition['attributes']['baseType'] != 'folder')
        {
            throw new ConstraintViolationException('Object is not of base type folder');
        }

        // TODO determine whether this is in fact necessary or if we should require decoding in the calling code
        // Attempt to decode $folderId, use as is if not detected as encoded
        $objectId = $folderId;
        $tmpTypeId = CMISUtil::decodeObjectId($objectId);
        if ($tmpTypeId != 'Unknown')
            $folderId = $objectId;
        
        // if parent folder is not allowed to hold this type, throw exception
        $CMISFolder = new CMISFolderObject($folderId, $this->ktapi);
        $folderProperties = $CMISFolder->getProperties();
        $allowed = $folderProperties->getValue('AllowedChildObjectTypeIds');
        if (!is_array($allowed) || !in_array($typeId, $allowed))
        {
            throw new ConstraintViolationException('Parent folder may not hold objects of this type (' . $typeId . ')');
        }

        $response = $this->ktapi->create_folder($folderId, $properties['name'], $sig_username = '', $sig_password = '', $reason = '');
        if ($response['status_code'] != 0)
        {
            // throw storageException
            throw new StorageException('The repository was unable to create the folder - ' . $response['message']);
        }
        else
        {
            $objectId = CMISUtil::encodeObjectId('Folder', $response['results']['id']);
        }

        return $objectId;
    }

}

?>
