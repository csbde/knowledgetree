<?php

require_once(KT_DIR . '/ktapi/ktapi.inc.php');
//require_once(CMIS_DIR . '/exceptions/ConstraintViolationException.inc.php');
//require_once(CMIS_DIR . '/exceptions/ContentAlreadyExistsException.inc.php');
//require_once(CMIS_DIR . '/exceptions/ObjectNotFoundException.inc.php');
//require_once(CMIS_DIR . '/exceptions/StorageException.inc.php');
//require_once(CMIS_DIR . '/exceptions/StreamNotSupportedException.inc.php');
//require_once(CMIS_DIR . '/exceptions/UpdateConflictException.inc.php');
//require_once(CMIS_DIR . '/exceptions/VersioningException.inc.php');
//require_once(CMIS_DIR . '/services/CMISRepositoryService.inc.php');
//require_once(CMIS_DIR . '/objecttypes/CMISDocumentObject.inc.php');
//require_once(CMIS_DIR . '/objecttypes/CMISFolderObject.inc.php');
//require_once(CMIS_DIR . '/classes/CMISRepository.inc.php');
//require_once(CMIS_DIR . '/util/CMISUtil.inc.php');

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
     * Deletes all Document Objects in the specified Version Series, including the Private Working Copy
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

        // if there was an error performing the delete, throw exception
        if ($result['status_code'] == 1) {
            throw new RuntimeException('There was an error deleting the object: ' . $result['message']);
        }
        
        return true;
    }

}

?>
