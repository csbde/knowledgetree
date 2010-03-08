<?php
/**
* Navigation Service CMIS wrapper API for KnowledgeTree.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
* Contributor( s): ______________________________________
*/

/**
*
* @copyright 2008-2010, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package KTCMIS
* @version Version 0.9
*/

require_once(realpath(dirname(__FILE__) . '/ktService.inc.php'));
require_once(CMIS_DIR . '/services/CMISNavigationService.inc.php');

/*
 * Handles repository navigation
 */
class KTNavigationService extends KTCMISBase {

    protected $NavigationService;

    public function __construct(&$ktapi = null, $username = null, $password = null)
    {
        parent::__construct($ktapi, $username, $password);
        // instantiate underlying CMIS service
        $this->NavigationService = new CMISNavigationService();
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
        $this->NavigationService->setInterface(self::$ktapi);
    }

    /**
     * Get descendents of the specified folder, up to the depth indicated
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param int $depth
     * @param string $filter
     * @param boolean $includeRelationships
     * @param string $renditionFilter
     * @param boolean $includeAllowableAc
     * @return array $descendants
     */
    public function getDescendants($repositoryId, $folderId, $depth = 2, $filter = '', $includeRelationships = false, $renditionFilter = '', 
                                   $includeAllowableActions = false, $includePathSegment = false)
    {
        // TODO optional parameters
        $descendantsResult = $this->NavigationService->getDescendants($repositoryId, $folderId, $depth, $filter, 
                                                                      $includeRelationships = false, $renditionFilter = '', 
                                                                      $includeAllowableActions = false, $includePathSegment = false);

        if (PEAR::isError($descendantsResult))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting descendants for folder"
            );
        }
        
        // format for webservices consumption
        // NOTE this will almost definitely be changing in the future, this is just to get something working
        $descendants = CMISUtil::decodeObjectHierarchy($descendantsResult, 'children');
        
        return array (
            "status_code" => 0,
            "results" => $descendants
        );
    }

    /**
     * Get direct children of the specified folder
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param boolean $includeAllowableActions
     * @param boolean $includeRelationships
     * @param string $typeID
     * @param string $filter
     * @param int $maxItems
     * @param int $skipCount
     * @return array $descendants
     */
    public function getChildren($repositoryId, $folderId, $includeAllowableActions, $includeRelationships,
                                $typeID = 'Any', $filter = '', $maxItems = 0, $skipCount = 0)
    {
        // TODO paging
        // TODO optional parameters
        $childrenResult = $this->NavigationService->getChildren($repositoryId, $folderId, $includeAllowableActions, $includeRelationships);

        if (PEAR::isError($childrenResult))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting children for folder"
            );
        }

        $children = CMISUtil::decodeObjectHierarchy($childrenResult, 'children');

        return array(
			"status_code" => 0,
			"results" => $children
		);
    }

    /**
     * Gets the parent of the selected folder
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param string $filter
     * @return parent[]
     */
    public function getFolderParent($repositoryId, $folderId, $filter = '')
    {
        try {
            $parent = $this->NavigationService->getFolderParent($repositoryId, $folderId, $filter);
        }
        catch (Exception $e) {
            return array(
                "status_code" => 1,
                "message" => "Failed getting folder parent: " . $e->getMessage()
            );
        }
        
        if (PEAR::isError($parent))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting folder parent"
            );
        }
        
        return array(
			"status_code" => 0,
			"results" => CMISUtil::createObjectPropertiesEntry($parent->getProperties())
		);
    }

    /**
     * Gets the parents for the selected object
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param boolean $includeAllowableActions
     * @param boolean $includeRelationships
     * @param string $filter
     * @return ancestry[]
     */
    function getObjectParents($repositoryId, $objectId, $includeAllowableActions, $includeRelationships, $filter = '')
    {
        try {
            $ancestry = $this->NavigationService->getObjectParents($repositoryId, $objectId, $includeAllowableActions,
                                                                   $includeRelationships);
        }
        catch (Exception $e) {
            return array(
                "status_code" => 1,
                "message" => $e->getMessage()
            );
        }

        if (PEAR::isError($ancestry))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting ancestry for object"
            );
        }

        return array(
            "status_code" => 0,
            "results" => $ancestry
        );
    }

    /**
     * Returns a list of checked out documents from the selected repository
     *
     * @param string $repositoryId
     * @param string $folderId The folder for which checked out docs are requested
     * @param int $maxItems
     * @param int $skipCount
     * @param string $filter
     * @param enum $includeRelationships
     * @param boolean $includeAllowableActions
     * @param string $renditionFilter
     * @return array $checkedout The collection of checked out document objects
     *               MUST include (unless not requested) for each object:
     *               array $properties
     *               array $relationships
     *               array $renditions
     *               $allowableActions
     * @return boolean $hasMoreItems
     * @return int $numItems [optional]
     */
    function getCheckedOutDocs($repositoryId, $folderId = null, $maxItems = 0, $skipCount = 0, $orderBy = '', 
                               $filter = '', $includeRelationships = null, $includeAllowableActions = false, $renditionFilter = '')
    {
        $checkedout = $this->NavigationService->getCheckedOutDocs($repositoryId, $folderId = null, $maxItems = 0, $skipCount = 0, 
                                                                  $orderBy, $filter, $includeRelationships, $includeAllowableActions, 
                                                                  $renditionFilter);

        if (PEAR::isError($checkedout))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting list of checked out documents"
            );
        }

        // convert to array format for external code
        $co = array();
        foreach ($checkedout as $documentProperties)
        {
            $co[] = CMISUtil::createObjectPropertiesEntry($documentProperties);;
        }

        return array(
            "status_code" => 0,
            "results" => $co
        );
    }

}

?>