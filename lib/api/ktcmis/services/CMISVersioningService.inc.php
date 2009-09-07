<?php

require_once(KT_DIR . '/ktapi/ktapi.inc.php');
require_once(CMIS_DIR . '/exceptions/ConstraintViolationException.inc.php');
require_once(CMIS_DIR . '/exceptions/StorageException.inc.php');
require_once(CMIS_DIR . '/exceptions/StreamNotSupportedException.inc.php');
require_once(CMIS_DIR . '/exceptions/UpdateConflictException.inc.php');
require_once(CMIS_DIR . '/exceptions/VersioningException.inc.php');
require_once(CMIS_DIR . '/services/CMISObjectService.inc.php');
require_once(CMIS_DIR . '/objecttypes/CMISDocumentObject.inc.php');
require_once(CMIS_DIR . '/util/CMISUtil.inc.php');

class CMISVersioningService {

    protected $ktapi;

    /**
     * Sets the interface to be used to interact with the repository
     *
     * @param object $ktapi The KnowledgeTree API interface
     */
    public function setInterface(&$ktapi)
    {
        $this->ktapi = $ktapi;
    }
    
    /**
     * Deletes all Document Objects in the specified Version Series, including the Private Working Copy if it exists
     * 
     * @param string $repositoryId
     * @param string $versionSeriesId
     * @return boolean true if successful
     */
    // NOTE For KnowledgeTree the $versionSeriesId should be the latest version, if not it will be taken as implied.
    //      Should we decide to implement the ability to delete individual versions, 
    //      then an exception may be thrown under certain circumstances (to be determined)
    // NOTE I am not really sure how this is going to be handled by CMIS clients.
    //      Testing with CMISSpaces we have it sending the actual document id, not a version series id.
    //      This may be due to the data sent back from our code, or it may just be how CMISSpaces does it.
    //      There is a note in their source code about this.
    //      Meantime we will try based on document id and adjust as needed later
    public function deleteAllVersions($repositoryId, $versionSeriesId)
    {
        // attempt to delete based on versionSeriesId as document/object id
        // determine object type and internal id
        $objectId = CMISUtil::decodeObjectId($versionSeriesId, $typeId);
        
        // if not a versionable object, throw exception
        // NOTE that we are assuming only documents are versionable at the moment
        if ($typeId != 'Document') {
            throw new RuntimeException('The object type is not versionable and cannot be deleted using deleteAllVersions.');
        }
        
        // try to delete
        // TODO add a default reason
        // TODO add the electronic signature capability
        $auth_sig = true;
        $result = $this->ktapi->delete_document($objectId, $reason, $auth_sig, $sig_username, $sig_password);
        
        // TODO delete any PWC which may exist (NOTE added 24 August 2009 - we did not have any PWC functionality when this function was originally created)

        // if there was an error performing the delete, throw exception
        if ($result['status_code'] == 1) {
            throw new RuntimeException('There was an error deleting the object: ' . $result['message']);
        }
        
        return true;
    }
    
    /**
     * Checks out a document and creates the PWC (Private Working Copy) which will represent the checked out document
     * 
     * @param string $repositoryId
     * @param string $documentId
     * @param string $changeToken [optional]
     * @return string $documentId The id of the PWC object
     * @return boolean $contentCopied TRUE if contentStream is a copy of the document content stream, FALSE if contentStream not set
     */
    // TODO exceptions:
    //      •	versioningException: The repository MAY throw this exception if the object is a non-current Document Version.
    // NOTE since we need to return two values, we return one via argument by reference
    //      since $documentId already exists in the argument list, that was chosen as the "return by reference" value
    // TODO set up delivery of content stream? or is that up to the CMIS client?
    public function checkOut($repositoryId, &$documentId, $changeToken = '')
    {
        $contentCopied = false;
        
        $documentId = CMISUtil::decodeObjectId($documentId, $typeId);

        // NOTE We are not planning on persisting the PWC beyond the current session, it will be re-created on access of the checked out document
        // TODO consider persisting in the database?  How will this relate to JSR if we are switching to that?
        // NOTE within the current system it is assumed if a new document metadata version is created that this is the latest version of the document
        // TODO see if there is an easy way to modify this, else we may not have an easy way to persist PWC objects
        
        // throw updateConflictException if the operation is attempting to update an object that is no longer current (as determined by the repository).
        try {
            $pwc = new CMISDocumentObject($documentId, $this->ktapi);
        }
        catch (exception $e) {
            throw new UpdateConflictException($e->getMessage());
        }
        
        // throw exception if the object is not versionable
        if (!$pwc->getAttribute('versionable')) {
            throw new ConstraintViolationException('This document is not versionable and may not be checked out');
        }
        
        // NOTE KTAPI as currently implemented does not give a direct response which indicates if the document is already checked out,
        //      as long as the same use is calling the checkout again, so should we add a check here specifically?

        // run checkout process - set $download = false (third function argument) as we want to return the document content via the contentStream
        $response = $this->ktapi->checkout_document($documentId, 'CMIS Checkout Action', false, $sig_username, $sig_password);
        // if there was an error, throw an exception
        if ($response['status_code'] == 1) {
            throw new StorageException($response['message']);
        };
        
        // if successful, set $contentCopied = true; unless contentStream is not set
        if ($pwc->getProperty('ContentStreamFilename') != '') $contentCopied = true;
        $documentId = CMISUtil::encodeObjectId('Document', $documentId);
        
        // mark document object as checked out
        $pwc->setProperty('IsVersionSeriesCheckedOut', true);
        $userName = '';
        $user = $this->ktapi->get_user();
        if (!PEAR::isError($user)) {
            $userName = $user->getName();
        }
        $pwc->setProperty('VersionSeriesCheckedOutBy', $userName);
        $pwc->setProperty('VersionSeriesCheckedOutId', $documentId);
        
        return $contentCopied;
    }
    
    /**
     * Reverses the effect of a checkout: I.E. deletes the PWC (Private Working Copy) and re-sets the status of the document to "not checked out" 
     * 
     * @param string $repositoryId
     * @param string $documentId
     * @param string $changeToken [optional]
     */
    // TODO exceptions:
    //      •	versioningException - The repository MAY throw this exception if the object is a non-current Document Version.
    public function cancelCheckOut($repositoryId, $documentId, $changeToken = '')
    {
        $documentId = CMISUtil::decodeObjectId($documentId, $typeId);
        
        /* re-generate PWC object */
        // throw updateConflictException if the operation is attempting to update an object that is no longer current (as determined by the repository).
        try {
            $pwc = new CMISDocumentObject($documentId, $this->ktapi);
        }
        catch (exception $e) {
            throw new UpdateConflictException($e->getMessage());
        }
        
        // throw exception if the object is not versionable
        if (!$pwc->getAttribute('versionable')) {
            throw new ConstraintViolationException('This document is not versionable and may not be checked out');
        }
        
        // TODO delete PWC - since we are not persisting the PWC this is not necessary at the moment
        
        // cancel checkout
        $response = $this->ktapi->undo_document_checkout($documentId, 'CMIS Cancel Checkout Action', $sig_username, $sig_password);
        
        // if there was any error in cancelling the checkout
        if ($response['status_code'] == 1) {
            throw new RuntimeException('There was an error cancelling the checkout: ' . $response['message']);
        }
    }
    
    /**
     * Checks in a checked out document
     * 
     * @param string $repositoryId
     * @param string $documentId
     * @param boolean $major
     * @param string $changeToken [optional]
     * @param array $properties [optional]
     * @param contentStream $contentStream [optional]
     * @param string $checkinComment [optional]
     * @return string $documentId
     */
    // TODO Exceptions:
    //        •	versioningException - The repository MAY throw this exception if the object is a non-current Document Version
    public function checkIn($repositoryId, $documentId, $major, $contentStream = null, $changeToken = '', $properties = array(), $checkinComment = '')
    {
        $documentId = CMISUtil::decodeObjectId($documentId, $typeId);
        
        // throw updateConflictException if the operation is attempting to update an object that is no longer current (as determined by the repository).
        try {
            $pwc = new CMISDocumentObject($documentId, $this->ktapi);
        }
        catch (exception $e) {
            throw new UpdateConflictException($e->getMessage());
        }
        
        // throw exception if the object is not versionable
        if (!$pwc->getAttribute('versionable')) {
            throw new ConstraintViolationException('This document is not versionable and may not be checked in');
        }
        
        $RepositoryService = new CMISRepositoryService();
        try {
            $typeDefinition = $RepositoryService->getTypeDefinition($repositoryId, $typeId);
        }
        catch (exception $e) {
            // if we can't get the type definition, then we can't store the content
            throw new StorageException($e->getMessage());
        }
        
        if (($typeDefinition['attributes']['contentStreamAllowed'] == 'notAllowed') && !empty($contentStream)) {
            throw new StreamNotSupportedException('Content Streams are not supported');
        }
        
        // check that this is the latest version
        if ($pwc->getProperty('IsLatestVersion') != true) {
            throw new VersioningException('The document is not the latest version and cannot be checked in');
        }
        
        // now do the checkin
        $tempfilename = CMISUtil::createTemporaryFile($contentStream);
        $response = $this->ktapi->checkin_document($documentId, $pwc->getProperty('ContentStreamFilename'), $reason, $tempfilename, $major,
                                                   $sig_username, $sig_password);
                                       
        // if there was any error in cancelling the checkout
        if ($response['status_code'] == 1) {
            throw new RuntimeException('There was an error checking in the document: ' . $response['message']);
        }
        
        return CMISUtil::encodeObjectId(DOCUMENT, $documentId);
    }
    
}

?>
