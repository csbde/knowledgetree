<?php

require_once(KT_DIR . '/ktapi/ktapi.inc.php');
require_once(KT_DIR . '/ktwebservice/KTUploadManager.inc.php');
require_once(CMIS_DIR . '/exceptions/ConstraintViolationException.inc.php');
require_once(CMIS_DIR . '/exceptions/ContentAlreadyExistsException.inc.php');
require_once(CMIS_DIR . '/exceptions/ObjectNotFoundException.inc.php');
require_once(CMIS_DIR . '/exceptions/StorageException.inc.php');
require_once(CMIS_DIR . '/exceptions/StreamNotSupportedException.inc.php');
require_once(CMIS_DIR . '/exceptions/UpdateConflictException.inc.php');
require_once(CMIS_DIR . '/exceptions/VersioningException.inc.php');
require_once(CMIS_DIR . '/services/CMISNavigationService.inc.php');
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

        $objectId = CMISUtil::decodeObjectId($objectId, $typeId);

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
     * @param string $contentStream optional content stream data - expected as a base64 encoded string
     * @param string $versioningState optional version state value: checkedout/major/minor
     * @return string $objectId The id of the created folder object
     */
    // TODO throw ConstraintViolationException if:
    //      value of any of the properties violates the min/max/required/length constraints
    //      specified in the property definition in the Object-Type. 
    function createDocument($repositoryId, $typeId, $properties, $folderId = null,
                            $contentStream = null, $versioningState = null)
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
        $tmpObjectId = CMISUtil::decodeObjectId($tmpObjectId, $tmpTypeId);
        if ($tmpTypeId != 'Unknown')
            $folderId = $tmpObjectId;

        // if parent folder is not allowed to hold this type, throw exception
        $CMISFolder = new CMISFolderObject($folderId, $this->ktapi);
        $allowed = $CMISFolder->getProperty('AllowedChildObjectTypeIds');
        $typeAllowed = false;

        if (is_array($allowed))
        {
            foreach($allowed as $type)
            {
                if (strtolower($type) == strtolower($typeId))
                {
                    $typeAllowed = true;
                    break;
                }
            }
        }

        if (!$typeAllowed)
        {
            throw new ConstraintViolationException('Parent folder may not hold objects of this type (' . $typeId . ')');
        }

        // if content stream is required and no content stream is supplied, throw a ConstraintViolationException
        if (($typeDefinition['attributes']['contentStreamAllowed'] == 'required') && empty($contentStream))
        {
            throw new ConstraintViolationException('This repository requires a content stream for document creation.  '
                                                 . 'Refusing to create an empty document');
        }
        else if (($typeDefinition['attributes']['contentStreamAllowed'] == 'notAllowed') && !empty($contentStream))
        {
            throw new StreamNotSupportedException('Content Streams are not supported');
        }

        // if versionable attribute is set to false and versioningState is supplied, throw a ConstraintViolationException
        if (!$typeDefinition['attributes']['versionable'] && !empty($versioningState))
        {
            throw new ConstraintViolationException('This repository does not support versioning');
        }

        // TODO deal with $versioningState when supplied

        // set title and name identical if only one submitted
        if ($properties['title'] == '')
        {
            $properties['title'] = $properties['name'];
        }
        else if ($properties['name'] == '')
        {
            $properties['name'] = $properties['title'];
        }

        // if name is blank throw exception (check type) - using invalidArgument Exception for now
        if (trim($properties['name']) == '')
        {
            throw new InvalidArgumentException('Refusing to create an un-named document');
        }

        // TODO also set to Default if a non-supported type is submitted
        if ($properties['type'] == '')
        {
            $properties['type'] = 'Default';
        }

        // create the content stream from the supplied data
        // NOTE since the repository is set to require a content stream and we don't currently have another way to get the document data
        //      this check isn't strictly necessary;  however it is needed for a repository which does not support content streams
        if (!empty($contentStream))
        {
            // TODO consider checking whether content is encoded (currently we expect encoded)
            // TODO choose between this and the alternative decode function (see CMISUtil class)
            //      The current one appears to be miles better (1/0/3 vs 14/4/57 on respective test files)
            $contentStream = CMISUtil::decodeChunkedContentStream($contentStream);
         
            // NOTE There is a function in CMISUtil to do this, written for the unit tests but since KTUploadManager exists
            //      and has more functionality which could come in useful at some point I decided to go with that instead
            //      (did not know this existed when I wrote the CMISUtil function)
            $uploadManager = new KTUploadManager();
            // assumes already decoded from base64, should use store_base64_file if not
            $tempfilename = $uploadManager->store_file($contentStream, 'cmis_');

            // metadata
            $metadata = array();
            $metaFields = array();
            $sysdata = array();

            if (!empty($properties['summary']))
            {
                $metadata[] = array('fieldset' => 'Tag Cloud',
                                    'fields' => array(
                                                      array(
                                                        'name' => 'Tag',
                                                        'value' => $properties['summary']
                                                      )
                                                )
                              );
            }

            // TODO fetch author name based on logged in user
            $user = $this->ktapi->get_user();
            if (!PEAR::isError($user))
            {
                $metaFields['General Information'][] = array(
                                    'name' => 'Document Author',
                                    'value' => $user->getName()
                                );
            }

            if (!empty($properties['category']))
            {
                $category = $properties['category'];
            }
            else
            {
                $category = 'Miscellaneous';
            }

            $metaFields['General Information'][] =  array(
                                 'name' => 'Category',
                                 'value' => $category
                             );

            /**
             * Try to determine mime type which maps to one of the following:
             *
             * Audio
             * Image
             * Text
             * Video
             *
             * example mime types:
             *
             * text/plain
             * image/gif
             * application/x-dosexec
             * application/pdf
             * application/msword
             * audio/mpeg
             * application/octet-stream
             * application/zip
             */
            // TODO check extension for types which are not obvious?  e.g. wmv video returns application/octet-stream
            $mediatype = null;
            include_once(KT_LIB_DIR . 'mime.inc.php');
            $KTMime = new KTMime();
            $mimetype = $KTMime->getMimeTypeFromFile($tempfilename);
            preg_match('/^([^\/]*)\/([^\/]*)/', $mimetype, $matches);
            if (($matches[1] == 'text') || ($matches[1] == 'image') || ($matches[1] == 'audio'))
            {
                $mediatype = ucwords($matches[1]);
            }
            else if (($matches[2] == 'pdf') || ($matches[2] == 'msword'))
            {
                $mediatype = 'Text';
            }

            if (!is_null($mediatype))
            {
                $metaFields['General Information'][] = array(
                                 'name' => 'Media Type',
                                 'value' => $mediatype
                             );
            }

            if (count($metaFields['General Information']) > 0)
            {
                foreach($metaFields['General Information'] as $field)
                {
                    $fields[] = $field;
                }

                $metadata[] = array('fieldset' => 'General Information',
                                    'fields' => $fields);
            }

            $response = $this->ktapi->add_document_with_metadata((int)$folderId, $properties['title'], $properties['name'],
                                                                 $properties['type'], $tempfilename, $metadata, $sysdata);

            if ($response['status_code'] != 0)
            {
                throw new StorageException('The repository was unable to create the document.  ' . $response['message']);
            }
            else
            {
                $objectId = CMISUtil::encodeObjectId('Document', $response['results']['document_id']);
            }

            // remove temporary file
            @unlink($tempfilename);
        }
        // else create the document object in the database but don't actually create any content since none was supplied
        // NOTE perhaps this content could be supplied in the $properties array?
        else
        {
            // TODO creation of document without content.  leaving this for now as we require content streams and any code
            //      here will therefore never be executed; if we implement some form of template based document creation
            //      then we may have something else to do here;
            //      for now we just throw a general RuntimeException, since we should not
            //      actually reach this code unless something is wrong; this may be removed or replaced later
            throw new RuntimeException('Cannot create empty document');
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
        $tmpObjectId = CMISUtil::decodeObjectId($tmpObjectId, $tmpTypeId);
        if ($tmpTypeId != 'Unknown')
            $folderId = $tmpObjectId;
        
        // if parent folder is not allowed to hold this type, throw exception
        $CMISFolder = new CMISFolderObject($folderId, $this->ktapi);
        $allowed = $CMISFolder->getProperty('AllowedChildObjectTypeIds');
        if (!is_array($allowed) || !in_array($typeId, $allowed))
        {
            throw new ConstraintViolationException('Parent folder may not hold objects of this type (' . $typeId . ')');
        }

        // TODO if name is blank! throw another exception (check type) - using invalidArgument Exception for now
        if (trim($properties['name']) == '')
        {
            throw new InvalidArgumentException('Refusing to create an un-named folder');
        }

        $response = $this->ktapi->create_folder((int)$folderId, $properties['name'], $sig_username = '', $sig_password = '', $reason = '');
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

    // NOTE this function is presently incomplete and untested.  Completion deferred to implementation of Checkout/Checkin
    //      functionality
    // NOTE I am not sure yet when this function would ever be called - checkin would be able to update the content stream
    //      already and the only easy method we have (via KTAPI as it stands) to update the content is on checkin anyway.
    //      Additionally this function doesn't take a value for the versioning status (major/minor) and so cannot pass it
    //      on to the ktapi checkin function.
    //      I imagine this function may be called if we ever allow updating document content independent of checkin,
    //      or if we change some of the underlying code and call direct to the document functions and not via KTAPI.
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
        // if no document id was supplied, we are going to create the underlying physical document
        // NOTE while it might have been nice to keep this out of here, KTAPI has no method for creating a document without
        //      a physical upload, so we cannot create the document first and then add the upload as a content stream, the
        //      entire creation step needs to happen here.
        
        // Attempt to decode $documentId, use as is if not detected as encoded
        $tmpObjectId = $documentId;
        $tmpObjectId = CMISUtil::decodeObjectId($tmpObjectId, $tmpTypeId);
        if ($tmpTypeId != 'Unknown')
            $documentId = $tmpObjectId;

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

        $csFileName = $CMISDocument->getProperty('ContentStreamFilename');
        if (!empty($csFileName) && (!$overwriteFlag))
        {
            throw new ContentAlreadyExistsException('Unable to overwrite existing content stream');
        }

        // NOTE There is a function in CMISUtil to do this but since KTUploadManager exists and has more functionality
        //      which could come in useful at some point I decided to go with that instead (did not know it existed when
        //      I wrote the CMISUtil function)
        $uploadManager = new KTUploadManager();
        $tempfilename = $uploadManager->store_base64_file($contentStream, 'cmis_');
        // update the document content from this temporary file as per usual
        // TODO Use checkin_document_with_metadata instead if metadata content submitted || update metadata separately?
        $response = $this->ktapi->checkin_document($documentId,  $csFileName, 'CMIS setContentStream action', $tempfilename, false);
        if ($response['status_code'] != 0)
        {
            throw new StorageException('Unable to update the content stream.  ' . $response['message']);
        }
//        else
//        {
//            $objectId = CMISUtil::encodeObjectId('Document', $response['results']['id']);
//        }

        @unlink($csFile);
        // update the CMIS document object with the content stream information
//        $CMISDocument->reload($document['result']['document_id']);

        return $CMISDocument->getProperty('ObjectId');
    }

}

?>
