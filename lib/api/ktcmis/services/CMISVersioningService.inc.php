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
     * Checks out a document and creates the PWC (Private Working Copy) which will represent the checked out document
     * 
     * @param string $repositoryId
     * @param string $objectId
     * @return string $objectId The id of the PWC object
     * @return boolean $contentCopied TRUE if contentStream is a copy of the document content stream, FALSE if contentStream not set
     */
    // NOTE since we need to return two values, we return one via argument by reference
    //      since $objectId already exists in the argument list, that was chosen as the "return by reference" value
    // TODO set up delivery of content stream? or is that up to the CMIS client?
    public function checkOut($repositoryId, &$objectId)
    {
        $contentCopied = false;

        $objectId = CMISUtil::decodeObjectId($objectId, $typeId);

        // NOTE We are not planning on persisting the PWC beyond the current session, it will be re-created on access of the checked out document
        // TODO consider persisting in the database?  How will this relate to JSR if we are switching to that?
        // NOTE within the current system it is assumed if a new document metadata version is created that this is the latest version of the document
        // TODO see if there is an easy way to modify this, else we may not have an easy way to persist PWC objects

        // throw updateConflictException if the operation is attempting to update an object that is no longer current (as determined by the repository).
        try {
            $pwc = new CMISDocumentObject($objectId, $this->ktapi);
        }
        catch (exception $e) {
            throw new UpdateConflictException($e->getMessage());
        }

        // throw exception if the object is not versionable
        if (!$pwc->getAttribute('versionable')) {
            throw new ConstraintViolationException('This document is not versionable and may not be checked out');
        }

        // check that this is the latest version
        if ($pwc->getProperty('isLatestVersion') != true) {
            throw new VersioningException('The document is not the latest version and cannot be checked out');
        }

        // NOTE KTAPI as currently implemented does not give a direct response which indicates if the document is already checked out,
        //      as long as the same user is calling the checkout again, so should we add a check here specifically?

        // run checkout process - set $download = false (third function argument) as we want to return the document content via the contentStream
        $response = $this->ktapi->checkout_document($objectId, 'CMIS Checkout Action', false, $sig_username, $sig_password);
        // if there was an error, throw an exception
        if ($response['status_code'] == 1) {
            throw new StorageException($response['message']);
        }

        // if successful, set $contentCopied = true; unless contentStream is not set
        if ($pwc->getProperty('contentStreamFilename') != '') {
            $contentCopied = true;
        }
        $objectId = CMISUtil::encodeObjectId(CMIS_DOCUMENT, $objectId);

        // mark document object as checked out
        $pwc->setProperty('isVersionSeriesCheckedOut', true);
        $userName = '';
        $user = $this->ktapi->get_user();
        if (!PEAR::isError($user)) {
            $userName = $user->getName();
        }
        $pwc->setProperty('versionSeriesCheckedOutBy', $userName);
        $pwc->setProperty('versionSeriesCheckedOutId', $objectId);

        return $contentCopied;
    }
    
    /**
     * Reverses the effect of a checkout: I.E. deletes the PWC (Private Working Copy) and re-sets the status of the document to "not checked out" 
     * 
     * @param string $repositoryId
     * @param string $objectId
     */
    // TODO exceptions:
    //      â€¢	versioningException - The repository MAY throw this exception if the object is a non-current Document Version.
    public function cancelCheckOut($repositoryId, $objectId)
    {
        $objectId = CMISUtil::decodeObjectId($objectId, $typeId);
        
        /* re-generate PWC object */
        // throw updateConflictException if the operation is attempting to update an object that is no longer current (as determined by the repository).
        try {
            $pwc = new CMISDocumentObject($objectId, $this->ktapi);
        }
        catch (exception $e) {
            throw new UpdateConflictException($e->getMessage());
        }
        
        // throw exception if the object is not versionable
        if (!$pwc->getAttribute('versionable')) {
            throw new ConstraintViolationException('This document is not versionable and may not be checked out');
        }
        
        // check that this is the latest version
        if ($pwc->getProperty('isLatestVersion') != true) {
            throw new VersioningException('The document is not the latest version');
        }
        
        // TODO delete PWC - since we are not persisting the PWC this is not necessary at the moment
        
        // cancel checkout
        $response = $this->ktapi->undo_document_checkout($objectId, 'CMIS Cancel Checkout Action', $sig_username, $sig_password);
        
        // if there was any error in cancelling the checkout
        if ($response['status_code'] == 1) {
            throw new RuntimeException('There was an error cancelling the checkout: ' . $response['message']);
        }
    }
    
    /**
     * Checks in a checked out document
     * 
     * @param string $repositoryId
     * @param string $objectId
     * @param boolean $major [optional] defaults to true
     * @param array $properties [optional]
     * @param contentStream $contentStream [optional]
     * @param string $checkinComment [optional]
     * @param array $policies
     * @param array $addACEs
     * @param array $removeACEs
     * @return string $objectId
     */
    // NOTE For repositories that do NOT support the optional “capabilityPWCUpdatable” capability, the properties
    //      and contentStream input parameters MUST be provided on the checkIn method for updates to happen as part
    //      of checkIn.
    // NOTE Only those properties whose values are different than the original value of the object need to be submitted.
    // NOTE we are not actually doing anything with the properties at this time, only the content stream
    // TODO filename changes and anything else supported in web interface, possibly additional supported by CMIS clients
    public function checkIn($repositoryId, $objectId, $major = true, $properties = array(), $contentStream = null,
                            $checkinComment = '', $policies = array(), $addACEs = array(), $removeACEs = array())
    {
        
        $objectId = CMISUtil::decodeObjectId($objectId, $typeId);
        
        // throw updateConflictException if the operation is attempting to update an object that is no longer current (as determined by the repository).
        try {
            $pwc = new CMISDocumentObject($objectId, $this->ktapi);
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
        
        // if content stream is required (capabilityPWCUpdatability == false) and no content stream is supplied, 
        // throw a ConstraintViolationException
        if (($typeDefinition['attributes']['contentStreamAllowed'] == 'required') && is_null($contentStream)) {
            throw new RuntimeException('This repository requires a content stream for document update on checkin.  '
                                     . 'Refusing to checkin an empty document');
        }
        else if (($typeDefinition['attributes']['contentStreamAllowed'] == 'notAllowed') && !empty($contentStream)) {
            throw new StreamNotSupportedException('Content Streams are not supported');
        }

        // check that this is the latest version
        if ($pwc->getProperty('isLatestVersion') != true) {
            throw new VersioningException('The document is not the latest version and cannot be checked in');
        }
        
        // now do the checkin
        $tempfilename = CMISUtil::createTemporaryFile($contentStream);
        $reason = 'CMIS object checkin';
        $response = $this->ktapi->checkin_document($objectId, $pwc->getProperty('contentStreamFilename'), $reason, $tempfilename, $major,
                                                   $sig_username, $sig_password);

        // if there was any error checking in
        if ($response['status_code'] == 1) {
            throw new RuntimeException('There was an error checking in the document: ' . $response['message']);
        }
        
        return CMISUtil::encodeObjectId(CMIS_DOCUMENT, $objectId);
    }
    
}

?>
