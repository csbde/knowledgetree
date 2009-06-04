<?php
/**
* Implements a CMIS wrapper API for KnowledgeTree.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
* Portions copyright The Jam Warehouse Software (Pty) Limited
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

// TODO implement exceptions in various calls (in the underlying classes)
// FIXME none of the error handling actually does anything, it's leftover from copy/paste of some ktapi code

require_once(realpath(dirname(__FILE__) . '/../config/dmsDefaults.php'));
require_once(KT_DIR . '/ktapi/ktapi.inc.php');

define ('CMIS_DIR', KT_DIR . '/ktcmis');
require_once(CMIS_DIR . '/services/CMISRepositoryService.inc.php');
require_once(CMIS_DIR . '/services/CMISNavigationService.inc.php');
require_once(CMIS_DIR . '/services/CMISObjectService.inc.php');
require_once(CMIS_DIR . '/util/CMISUtil.inc.php');

class KTCMIS {

    /**
     * Class for CMIS Repository Services
     *
     * @var object
     */
    protected $RepositoryService;
    /**
     * Class for CMIS Navigation Services
     *
     * @var object
     */
    protected $NavigationService;
    /**
     * Class for CMIS Object Services
     *
     * @var object
     */
    protected $ObjectService;
    /**
     * KnowledgeTree API instance
     *
     * @var object
     */
    protected $ktapi;
    /**
     * KnowledgeTree API Session Identifier
     *
     * @var object
     */
    protected $session;

    function __construct(&$ktapi = null, $user = '', $password = '')
    {
        // ktapi interface
        $this->session = null;
//        if (is_null($ktapi))
//        {
//            // TODO this should probably throw an exception instead
//            return PEAR::RaiseError('Cannot continue without KTAPI instance');
            // FIXME this CANNOT be allowed in a live environment
            //       possibly we should insist on a ktapi instance being passed
            //       or at least user/pass for logging in here, if one or other
            //       not sent, return error
            $user = 'admin';
            $password = 'admin';
            $this->ktapi = new KTAPI();
            $this->session = $this->ktapi->start_session($user, $password);
//        }
//        else
//        {
//            $this->ktapi = $ktapi;
//        }
        
        // instantiate services
        $this->RepositoryService = new CMISRepositoryService();
        $this->NavigationService = new CMISNavigationService($this->ktapi);
        $this->ObjectService = new CMISObjectService($this->ktapi);
    }

    function __destruct()
    {
//        if ($this->session instanceOf KTAPI_UserSession)
//        {
//            try
//            {
//                $this->session->logout();
//            }
//            catch (Exception $e)
//            {
//                // no output
//            }
//        }
    }

    // Repository service functions

    /**
     * Fetch a list of all available repositories
     *
     * NOTE Since we only have one repository at the moment, this is expected to only return one result
     *
     * @return repositoryList[]
     */
    function getRepositories()
    {
        $repositories = $this->RepositoryService->getRepositories();
        if (PEAR::isError($repositories))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting repositories"
            );
        }

        // extract the required info fields into array format for easy encoding;
        $count = 0;
        $repositoryList = array();
        foreach ($repositories as $repository)
        {
            $repositoryList[$count]['repositoryId'] = $repository->getRepositoryId();
            $repositoryList[$count]['repositoryName'] = $repository->getRepositoryName();
            $repositoryList[$count]['repositoryURI'] = $repository->getRepositoryURI();
            ++$count;
        }

        return array(
            "status_code" => 0,
            "results" => $repositoryList
        );
    }

    /**
     * Fetches information about the selected repository
     *
     * @param string $repositoryId
     */
    function getRepositoryInfo($repositoryId)
    {
        $repositoryInfo = $this->RepositoryService->getRepositoryInfo($repositoryId);
        if (PEAR::isError($repositoryInfo))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting repository information"
            );
        }

        // TODO output this manually, the function works but only for some objects so rather avoid it completely
        // NOTE the fact that it works for this instance is irrelevant...
        return array (
            "status_code" => 0,
            "results" => CMISUtil::objectToArray($repositoryInfo)
        );
    }

    /**
     * Fetch the list of supported object types for the selected repository
     *
     * @param string $repositoryId
     */
    function getTypes($repositoryId, $typeId = '', $returnPropertyDefinitions = false,
                      $maxItems = 0, $skipCount = 0, &$hasMoreItems = false)
    {
        $repositoryObjectTypeResult = $this->RepositoryService->getTypes($repositoryId, $typeId, $returnPropertyDefinitions,
                                                                    $maxItems, $skipCount, $hasMoreItems);
        if (PEAR::isError($repositoryObjectTypeResult))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting supported object types"
            );
        }

        // format as array style output
        // NOTE only concerned with attributes at this time
        // TODO add support for properties
        foreach($repositoryObjectTypeResult as $key => $objectType)
        {
            $repositoryObjectTypes[$key] = $objectType['attributes'];
            // TODO properties
            // $repositoryObjectTypes[$key]['properties'] = $objectType['properties'];
        }

        return array (
            "status_code" => 0,
            "results" => $repositoryObjectTypes
        );
    }

    /**
     * Fetch the object type definition for the requested type
     *
     * @param string $repositoryId
     * @param string $typeId
     */
    function getTypeDefinition($repositoryId, $typeId)
    {
        $typeDefinitionResult = $this->RepositoryService->getTypeDefinition($repositoryId, $typeId);
        
        if (PEAR::isError($typeDefinitionResult))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting object type definition for $typeId"
            );
        }

        // format as array style output
        // NOTE only concerned with attributes at this time
        // TODO add support for properties
        $typeDefinition = $typeDefinitionResult['attributes'];

        return array (
            "status_code" => 0,
            "results" => $typeDefinition
        );
    }

    // Navigation service functions

    /**
     * Get descendents of the specified folder, up to the depth indicated
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param bool $includeAllowableActions
     * @param bool $includeRelationships
     * @param string $typeID
     * @param int $depth
     * @param string $filter
     * @return array $descendants
     */
    function getDescendants($repositoryId, $folderId, $includeAllowableActions, $includeRelationships,
                            $depth = 1, $typeID = 'Any', $filter = '')
    {
        // TODO optional parameters
        $descendantsResult = $this->NavigationService->getDescendants($repositoryId, $folderId, $includeAllowableActions,
                                                                        $includeRelationships, $depth);

        if (PEAR::isError($descendantsResult))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting descendants for folder"
            );
        }

        // format for webservices consumption
        // NOTE this will almost definitely be changing in the future, this is just to get something working
        $descendants = CMISUtil::decodeObjectHierarchy($descendantsResult, 'child');
//        $descendants = array(array('properties' => array('objectId' => 'D2', 'typeId' => 'Document', 'name' => 'test document'),
//                                   'child' => array(array('properties' => array('objectId' => 'D7',
//                                                          'typeId' => 'Document', 'name' => 'CHILD document'), 'child' => null),
//                                                    array('properties' => array('objectId' => 'F34',
//                                                          'typeId' => 'Folder', 'name' => 'CHILD FOLDER'), 'child' => null))));

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
     * @param bool $includeAllowableActions
     * @param bool $includeRelationships
     * @param string $typeID
     * @param string $filter
     * @param int $maxItems
     * @param int $skipCount
     * @return array $descendants
     */
    function getChildren($repositoryId, $folderId, $includeAllowableActions, $includeRelationships,
                         $typeID = 'Any', $filter = '', $maxItems = 0, $skipCount = 0)
    {
        // TODO paging
        // TODO optional parameters
        $childrenResult = $this->NavigationService->getChildren($repositoryId, $folderId, $includeAllowableActions, $includeRelationships);

        if (PEAR::isError($childrenResult))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting descendants for folder"
            );
        }

        $children = CMISUtil::decodeObjectHierarchy($childrenResult, 'child');
//        $children = array(array('properties' => array('objectId' => 'D2', 'typeId' => 'Document', 'name' => 'test document'),
//                                'child' => null));

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
     * @param bool $includeAllowableActions
     * @param bool $includeRelationships
     * @param bool $returnToRoot
     * @param string $filter
     * @return ancestry[]
     */
    function getFolderParent($repositoryId, $folderId, $includeAllowableActions, $includeRelationships, $returnToRoot, $filter = '')
    {
        $ancestryResult = $this->NavigationService->getFolderParent($repositoryId, $folderId, $includeAllowableActions,
                                                              $includeRelationships, $returnToRoot);

        if (PEAR::isError($ancestryResult))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting ancestry for folder"
            );
        }

        $ancestry = CMISUtil::decodeObjectHierarchy($ancestryResult, 'child');
//        $ancestry = array(array('properties' => array('objectId' => 'D2', 'typeId' => 'Document', 'name' => 'test document'),
//                                'child' => null));

        return array(
			"status_code" => 0,
			"results" => $ancestry
		);
    }

    /**
     * Gets the parents for the selected object
     *
     * @param string $repositoryId
     * @param string $folderId
     * @param bool $includeAllowableActions
     * @param bool $includeRelationships
     * @param string $filter
     * @return ancestry[]
     */
    function getObjectParents($repositoryId, $objectId, $includeAllowableActions, $includeRelationships, $filter = '')
    {
        $ancestryResult = $this->NavigationService->getObjectParents($repositoryId, $objectId, $includeAllowableActions,
                                                                     $includeRelationships);

        if (PEAR::isError($ancestryResult))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting ancestry for object"
            );
        }

        $ancestry = CMISUtil::decodeObjectHierarchy($ancestryResult, 'child');
//        $ancestry = array(array('properties' => array('objectId' => 'D2', 'typeId' => 'Document', 'name' => 'test document'),
//                                'child' => null));

//        $ancestry = array(array('properties' => array(array('property' => array('name' => 'objectId', $value => 'D2')),
//                                                      array('property' => array('name' => 'typeId', $value => 'Document')),
//                                                      array('property' => array('name' => 'name', $value => 'test document')))),
//                                'child' => null);

        return array(
			"status_code" => 0,
			"results" => $ancestry
		);
    }

    /**
     * Gets the properties for the selected object
     *
     * @param string $repositoryId
     * @param string $objectId
     * @param bool $includeAllowableActions
     * @param bool $includeRelationships
     * @param string $returnVersion
     * @param string $filter
     * @return properties[]
     */
    function getProperties($repositoryId, $objectId, $includeAllowableActions, $includeRelationships,
                           $returnVersion = false, $filter = '')
    {
        $propertiesResult = $this->ObjectService->getProperties($repositoryId, $objectId, $includeAllowableActions, $includeRelationships);

        if (PEAR::isError($propertiesResult))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting properties for object"
            );
        }
//        echo '<pre>'.print_r($propertiesResult, true).'</pre>';
        // will need to convert to array format, so:
        $propertyCollection['objectId'] = $propertiesResult->getValue('objectId');
        $propertyCollection['URI'] = $propertiesResult->getValue('URI');
        $propertyCollection['typeId'] = $propertiesResult->getValue('typeId');
        $propertyCollection['createdBy'] = $propertiesResult->getValue('createdBy');
        $propertyCollection['creationDate'] = $propertiesResult->getValue('creationDate');
        $propertyCollection['lastModifiedBy'] = $propertiesResult->getValue('lastModifiedBy');
        $propertyCollection['lastModificationDate'] = $propertiesResult->getValue('lastModificationDate');
        $propertyCollection['changeToken'] = $propertiesResult->getValue('changeToken');

        $properties = array(array('properties' => $propertyCollection, 'child' => null));
//        echo '<pre>'.print_r($properties, true).'</pre>';
//
//        $properties = array(array('properties' => array('objectId' => 'F2', 'URI' => '', 'typeId' => 'Document',
//                                                        'createdBy' => 'Administrator', 'creationDate' => '1 June 2009',
//                                                        'lastModifiedBy' => 'Administrator', 'lastModificationDate' => '1 June 2009',
//                                                        'changeToken' => ''),
//                                  'child' => null));

        return array(
			"status_code" => 0,
			"results" => $properties
		);
    }
}

?>
