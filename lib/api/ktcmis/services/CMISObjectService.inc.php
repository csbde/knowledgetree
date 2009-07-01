<?php

require_once(KT_DIR . '/ktapi/ktapi.inc.php');
require_once(CMIS_DIR . '/exceptions/ConstraintViolationException.inc.php');
require_once(CMIS_DIR . '/exceptions/ContentAlreadyExistsException.inc.php');
require_once(CMIS_DIR . '/exceptions/ObjectNotFoundException.inc.php');
require_once(CMIS_DIR . '/exceptions/StorageException.inc.php');
require_once(CMIS_DIR . '/exceptions/StreamNotSupportedException.inc.php');
require_once(CMIS_DIR . '/exceptions/UpdateConflictException.inc.php');
require_once(CMIS_DIR . '/exceptions/VersioningException.inc.php');
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
    // TODO throw ConstraintViolationException if:
    //      The Object-Type definition specified by the typeId parameter's "contentStreamAllowed" attribute
    //      is set to "required" and no contentStream input parameter is provided
    // TODO throw ConstraintViolationException if:
    //      The Object-Type definition specified by the typeId parameter's "versionable" attribute
    //      is set to "false" and a value for the versioningState input parameter is provided
    function createDocument($repositoryId, $typeId, $properties, $folderId = null,
                            $contentStream = null, $versioningState = 'Major')
    {
        $objectId = null;

        // fetch type definition of supplied type and check for base type "document", if not true throw exception
        $RepositoryService = new CMISRepositoryService();
        try {
            $typeDefinition = $RepositoryService->getTypeDefinition($repositoryId, $typeId);
        }
        // NOTE Not sure that we should throw this specific exception, maybe just let the underlying
        //      exception propogate upward...
        //      Alternatively: throw new exception with original exception message appended
        // NOTE The latter method has been adopted for the moment
        catch (Exception $e)
        {
            throw new ConstraintViolationException('Object is not of base type document. ' . $e->getMessage());
        }

        if ($typeDefinition['attributes']['baseType'] != 'document')
        {
            throw new ConstraintViolationException('Object is not of base type document');
        }

        // if no $folderId submitted and repository does not support "unfiling" throw exception
        if (empty($folderId))
        {
            $repositoryInfo = $RepositoryService->getRepositoryInfo($repositoryId);
            $capabilities = $repositoryInfo->getCapabilities();
            if (!$capabilities->hasCapabilityUnfiling())
            {
                throw new ConstraintViolationException('Repository does not support the Unfiling capability and no folder id was supplied');
            }
        }

        // Attempt to decode $folderId, use as is if not detected as encoded
        $tmpObjectId = $folderId;
        $tmpTypeId = CMISUtil::decodeObjectId($tmpObjectId);
        if ($tmpTypeId != 'Unknown')
            $folderId = $tmpObjectId;

        // if parent folder is not allowed to hold this type, throw exception
        $CMISFolder = new CMISFolderObject($folderId, $this->ktapi);
        $folderProperties = $CMISFolder->getProperties();
        $allowed = $folderProperties->getValue('AllowedChildObjectTypeIds');
        if (!is_array($allowed) || !in_array($typeId, $allowed))
        {
            throw new ConstraintViolationException('Parent folder may not hold objects of this type (' . $typeId . ')');
        }

        // set title and name identical if only one submitted
        if ($properties['title'] == '')
        {
            $properties['title'] = $properties['name'];
        }
        else if ($properties['name'] == '')
        {
            $properties['name'] = $properties['title'];
        }

        if ($properties['type'] == '')
        {
            $properties['type'] = $properties['Default'];
        }

        // if content stream is required and no content stream is supplied, throw a ConstraintViolationException
        if (($typeDefinition['attributes']['contentStreamAllowed'] == 'required') && empty($contentStream))
        {
            throw new ConstraintViolationException('The Knowledgetree Repository requires a content stream for document creation.  '
                                                 . 'Refusing to create an empty document');
        }
        else if (($typeDefinition['attributes']['contentStreamAllowed'] == 'notAllowed') && !empty($contentStream))
        {
            throw new StreamNotSupportedException('Content Streams are not supported');
        }

        // TODO deal with $versioningState when supplied

        // TODO Use add_document_with_metadata instead if metadata content submitted
        $response = $this->ktapi->add_document($folderId, $properties['title'], $properties['name'], $properties['type'], $uploadedFile);
        if ($response['status_code'] != 0)
        {
            throw new StorageException('The repository was unable to create the document - ' . $response['message']);
        }
        else
        {
            $objectId = CMISUtil::encodeObjectId('Document', $response['results']['id']);
        }

        // now that the document object exists, create the content stream from the supplied data
        if (!empty($contentStream))
        {
            // TODO changeToken support
            $changeToken = null;
            $this->setContentStream($repositoryId, $objectId, false, $contentStream, $changeToken);
        }

        return $objectId;
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
    // TODO throw ConstraintViolationException if:
    //      value of any of the properties violates the min/max/required/length constraints
    //      specified in the property definition in the Object-Type.
    function createFolder($repositoryId, $typeId, $properties, $folderId)
    {
        $objectId = null;
        
        // fetch type definition of supplied type and check for base type "folder", if not true throw exception
        $RepositoryService = new CMISRepositoryService();
        try {
            $typeDefinition = $RepositoryService->getTypeDefinition($repositoryId, $typeId);
        }
        // NOTE Not sure that we should throw this specific exception, maybe just let the underlying
        //      exception propogate upward...
        //      Alternatively: throw new exception with original exception message appended
        // NOTE The latter method has been adopted for the moment
        catch (Exception $e)
        {
            throw new ConstraintViolationException('Object is not of base type folder. ' . $e->getMessage());
        }
        
        if ($typeDefinition['attributes']['baseType'] != 'folder')
        {
            throw new ConstraintViolationException('Object is not of base type folder');
        }

        // Attempt to decode $folderId, use as is if not detected as encoded
        $tmpObjectId = $folderId;
        $tmpTypeId = CMISUtil::decodeObjectId($tmpObjectId);
        if ($tmpTypeId != 'Unknown')
            $folderId = $tmpObjectId;
        
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
            throw new StorageException('The repository was unable to create the folder - ' . $response['message']);
        }
        else
        {
            $objectId = CMISUtil::encodeObjectId('Folder', $response['results']['id']);
        }

        return $objectId;
    }

    /**
     * Sets the content stream data for an existing document
     *
     * if $overwriteFlag = TRUE, the new content stream is applied whether or not the document has an existing content stream
     * if $overwriteFlag = FALSE, the new content stream is applied only if the document does not have an existing content stream
     *
     * NOTE A Repository MAY automatically create new Document versions as part of this service method.
     *      Therefore, the documentId output NEED NOT be identical to the documentId input.
     *
     * @param string $repositoryId
     * @param string $documentId
     * @param boolean $overwriteFlag
     * @param string $contentStream
     * @param string $changeToken
     * @return string $documentId
     */
    // TODO exceptions:
    //      updateConflictException: The operation is attempting to update an object that is no longer current
    //                               (as determined by the repository).
    //      versioningException: The repository MAY throw this exception if the object is a non-current Document Version.
    function setContentStream($repositoryId, $documentId, $overwriteFlag, $contentStream, $changeToken = null)
    {
        // fetch type definition of supplied document
        $CMISDocument = new CMISDocumentObject($documentId, $this->ktapi);
        
        // if content stream is not allowed for this object type definition, throw a ConstraintViolationException
        if (($CMISDocument->getAttribute('contentStreamAllowed') == 'notAllowed'))
        {
            // NOTE spec version 0.61c specifies both a ConstraintViolationException and a StreamNotSupportedException
            //      for this case.  Choosing to throw StreamNotSupportedException until the specification is clarified
            //      as it is a more specific exception
            throw new StreamNotSupportedException('Content Streams are not allowed for this object type');
        }

        $properties = $CMISDocument->getProperties();
        if (!empty($properties->getValue('ContentStreamFilename')) && (!$overwriteFlag))
        {
            throw new ContentAlreadyExistsException('Unable to overwrite existing content stream');
        }

        // in order to set the stream we need to do the following:
        // 1. decode the stream from the supplied base64 encoding
        // 2. create a temporary file as if it were uploaded via a file upload dialog
        // 3. create the document content as per usual
        // 4. link to the created document object? this perhaps only happens on read anyway

        // if there is any problem updating the content stream, throw StorageException
        // TODO real test parameter instead of hard-coded FALSE
        if (false)
        {
            throw new StorageException('Unable to update the content stream');
        }

        return $documentId;
    }

}

?>
