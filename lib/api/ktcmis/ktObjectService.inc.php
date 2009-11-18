<?php
/**
* Object Service CMIS wrapper API for KnowledgeTree.
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

require_once(realpath(dirname(__FILE__) . '/ktService.inc.php'));
require_once(CMIS_DIR . '/services/CMISObjectService.inc.php');

/**
 * Handles requests for and actions on Folders and Documents
 */
class KTObjectService extends KTCMISBase {

    protected $ObjectService;

    public function __construct(&$ktapi = null, $username = null, $password = null)
    {
        parent::__construct($ktapi, $username, $password);
        // instantiate underlying CMIS service
        $this->ObjectService = new CMISObjectService();
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
        $this->ObjectService->setInterface(self::$ktapi);
    }

    /**
     * Gets the properties for the selected object
     *
     * @param string $repositoryId
     * @param string $objectId
     * @param boolean $includeAllowableActions
     * @param boolean $includeRelationships
     * @param string $returnVersion
     * @param string $filter
     * @return properties[]
     */
    public function getProperties($repositoryId, $objectId, $includeAllowableActions, $includeRelationships,
                           $returnVersion = false, $filter = '')
    {
        try {
            $propertyCollection = $this->ObjectService->getProperties($repositoryId, $objectId, $includeAllowableActions,
                                                                      $includeRelationships);
        }
        catch (Exception $e)
        {
            return array(
                "status_code" => 1,
                "message" => $e->getMessage()
            );
        }

        $properties = CMISUtil::createObjectPropertiesEntry($propertyCollection);

        return array(
			"status_code" => 0,
			"results" => $properties
		);
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
    public function createDocument($repositoryId, $typeId, $properties, $folderId = null,
                            $contentStream = null, $versioningState = null)
    {
        $objectId = null;

        try {
            $objectId = $this->ObjectService->createDocument($repositoryId, $typeId, $properties, $folderId,
                                                             $contentStream, $versioningState);
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
            'results' => $objectId
        );
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
    public function createFolder($repositoryId, $typeId, $properties, $folderId)
    {
        $objectId = null;

        try {
            $objectId = $this->ObjectService->createFolder($repositoryId, $typeId, $properties, $folderId);
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
            'results' => $objectId
        );
    }
    
    /**
     * Fetches the content stream data for an object
     *  
     * @param string $repositoryId
     * @param string $objectId
     * @return string $contentStream (binary or text data)
     */
    function getContentStream($repositoryId, $objectId)
    {
        try {
            $contentStream = $this->ObjectService->getContentStream($repositoryId, $objectId);
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
            'results' => $contentStream
        );
    }
    
    /**
     * Moves a fileable object from one folder to another.
     * 
     * @param object $repositoryId
     * @param object $objectId
     * @param object $changeToken [optional]
     * @param object $targetFolderId
     * @param object $sourceFolderId [optional] 
     */
    public function moveObject($repositoryId, $objectId, $changeToken = '', $targetFolderId, $sourceFolderId = null)
    {
        try {
            $this->ObjectService->moveObject($repositoryId, $objectId, $changeToken, $targetFolderId, $sourceFolderId);
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
            'results' => $objectId
        );
    }
    
    /**
     * Deletes an object from the repository
     * 
     * @param string $repositoryId
     * @param string $objectId
     * @param string $changeToken [optional]
     * @return array
     */
    // NOTE Invoking this service method on an object SHALL not delete the entire version series for a Document Object. 
    //      To delete an entire version series, use the deleteAllVersions() service
    public function deleteObject($repositoryId, $objectId, $changeToken = null)
    {
        try {
            $this->ObjectService->deleteObject($repositoryId, $objectId, $changeToken);
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
            'results' => $objectId
        );
    }
    
    public function deleteTree($repositoryId, $objectId, $changeToken = null, $unfileNonfolderObject = 'delete', $continueOnFailure = false)
    {
        try {
            $result = $this->ObjectService->deleteTree($repositoryId, $objectId, $changeToken, $unfileNonfolderObject, $continueOnFailure);
        }
        catch (Exception $e)
        {
            return array(
                "status_code" => 1,
                "message" => $e->getMessage()
            );
        }

        // check whether there is a list of items which did not delete
        if (count($result) > 0)
        {
            return array(
                "status_code" => 1,
                "message" => $result
            );          
        }
        
        return array(
            'status_code' => 0,
            'results' => $objectId
        );
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
    function setContentStream($repositoryId, $documentId, $overwriteFlag, $contentStream, $changeToken = null)
    {
        try {
            $documentId = $this->ObjectService->setContentStream($repositoryId, $documentId, $overwriteFlag, $contentStream, $changeToken);
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
            'results' => $documentId
        );
    }

}

?>