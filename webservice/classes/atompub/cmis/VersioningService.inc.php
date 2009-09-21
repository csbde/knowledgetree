<?php

require_once KT_LIB_DIR . '/api/ktcmis/ktVersioningService.inc.php';

/**
 * CMIS Service class which hooks into the KnowledgeTree interface
 * for processing of CMIS queries and responses via atompub/webservices
 */

class VersioningService extends KTVersioningService {
    
    /**
     * Deletes all Document Objects in the specified Version Series, including the Private Working Copy
     * 
     * @param string $repositoryId
     * @param string $versionSeriesId
     * @return boolean true if successful
     */
    public function deleteAllVersions($repositoryId, $versionSeriesId)
    {
        $result = parent::deleteAllVersions($repositoryId, $versionSeriesId);

        if ($result['status_code'] == 0) {
            return $result['results'];
        }
        else {
            return new PEAR_Error($result['message']);
        }
    }
    
    /**
     * Checks out a document and creates the PWC (Private Working Copy) which will represent the checked out document
     * 
     * @param string $repositoryId
     * @param string $documentId
     * @param string $changeToken [optional]
     * @return array results
     */
    // TODO set up delivery of content stream? or is that up to the CMIS client?
    public function checkOut($repositoryId, $documentId, $changeToken = '')
    {
        $result = parent::checkOut($repositoryId, $documentId, $changeToken);

        if ($result['status_code'] == 0) {
            return $result['results'];
        }
        else {
            return new PEAR_Error($result['message']);
        }
    }
    
    /**
     * Reverses the effect of a checkout: I.E. deletes the PWC (Private Working Copy) and re-sets the status of the document to "not checked out" 
     * 
     * @param string $repositoryId
     * @param string $documentId
     * @param string $changeToken [optional]
     */
    // TODO exceptions:
    //      •	ConstraintViolationException: The Repository SHALL throw this exception if ANY of the following conditions are met:
    //      o	The Document’s Object-Type definition’s versionable attribute is FALSE. 
    //      •	updateConflictException
    //      •	versioningException
    public function cancelCheckOut($repositoryId, $documentId, $changeToken = '')
    {
        $result = parent::cancelCheckOut($repositoryId, $documentId, $changeToken);

        if ($result['status_code'] == 0) {
            return $result['results'];
        }
        else {
            return new PEAR_Error($result['message']);
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
    public function checkIn($repositoryId, $documentId, $major, $contentStream = null, $changeToken = '', $properties = array(), $checkinComment = '')
    {
        $result = parent::checkIn($repositoryId, $documentId, $major, $contentStream, $changeToken, $properties, $checkinComment);

        if ($result['status_code'] == 0) {
            return $result['results'];
        }
        else {
            return new PEAR_Error($result['message']);
        }
    }

}

?>
