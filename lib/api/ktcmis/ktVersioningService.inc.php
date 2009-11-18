<?php
/**
* Versioning Service CMIS wrapper API for KnowledgeTree.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
* 
*
* This program is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License version 3 as published by the
* Free Software Foundation.
*
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
* details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
* California 94120-7775, or email info@knowledgetree.com.
*
* The interactive user interfaces in modified source and object code versions
* of this program must display Appropriate Legal Notices, as required under
* Section 5 of the GNU General Public License version 3.
*
* In accordance with Section 7(b) of the GNU General Public License version 3,
* these Appropriate Legal Notices must retain the display of the "Powered by
* KnowledgeTree" logo and retain the original copyright notice. If the display of the
* logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
* must display the words "Powered by KnowledgeTree" and retain the original
* copyright notice.
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package KTCMIS
* @version Version 0.9
*/

/**
 * Split into individual classes to handle each section of functionality.
 * This is really just a handling layer between CMIS and the web services.
 */

require_once(realpath(dirname(__FILE__) . '/ktService.inc.php'));
require_once(CMIS_DIR . '/services/CMISVersioningService.inc.php');

/**
 * Handles requests for and actions on versionable objects
 */
class KTVersioningService extends KTCMISBase {

    protected $VersioningService;

    public function __construct(&$ktapi = null, $username = null, $password = null)
    {
        parent::__construct($ktapi, $username, $password);
        // instantiate underlying CMIS service
        $this->VersioningService = new CMISVersioningService();
        $this->setInterface();
    }

    public function startSession($username, $password)
    {
        parent::startSession($username, $password);
        $this->setInterface();
        return self::$session;
    }

    public function setInterface(&$ktapi = null)
    {
        parent::setInterface($ktapi);
        $this->VersioningService->setInterface(self::$ktapi);
    }
    
    /**
     * Deletes all Document Objects in the specified Version Series, including the Private Working Copy
     * 
     * @param string $repositoryId
     * @param string $versionSeriesId
     * @return boolean true if successful
     */
    public function deleteAllVersions($repositoryId, $versionSeriesId)
    {
        try {
            $result = $this->VersioningService->deleteAllVersions($repositoryId, $versionSeriesId);
        }
        catch (Exception $e)
        {
            return array(
                "status_code" => 1,
                "message" => $e->getMessage()
            );
        }

        return array(
            'status_code' => 0,
            'results' => $result
        );
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
        try {
            $result = $this->VersioningService->checkOut($repositoryId, $documentId, $changeToken);
        }
        catch (Exception $e)
        {
            return array(
                "status_code" => 1,
                "message" => $e->getMessage()
            );
        }

        return array(
            'status_code' => 0,
            'results' => (!empty($result) ? $result : 'Document Checked Out')
        );
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
        try {
            $result = $this->VersioningService->cancelCheckOut($repositoryId, $documentId, $changeToken);
        }
        catch (Exception $e)
        {
            return array(
                "status_code" => 1,
                "message" => $e->getMessage()
            );
        }

        return array(
            'status_code' => 0,
            'results' => (!empty($result) ? $result : 'Document Checkout Cancelled')
        );
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
        try {
            $result = $this->VersioningService->checkIn($repositoryId, $documentId, $major, $contentStream, $changeToken, $properties, $checkinComment);
        }
        catch (Exception $e)
        {
            return array(
                "status_code" => 1,
                "message" => $e->getMessage()
            );
        }

        return array(
            'status_code' => 0,
            'results' => (!empty($result) ? $result : 'Document Checked In Successfully')
        );
    }

}

?>