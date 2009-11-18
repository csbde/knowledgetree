<?php
/**
* Repository Service CMIS wrapper API for KnowledgeTree.
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
require_once(CMIS_DIR . '/services/CMISRepositoryService.inc.php');

/**
 * Handles low level repository information queries
 */
class KTRepositoryService extends KTCMISBase {

    protected $RepositoryService;

    public function __construct()
    {
        // we don't need to call the parent constructor here as there is no ktapi involved
        // instantiate underlying CMIS service
        $this->RepositoryService = new CMISRepositoryService();
    }

    /**
     * Fetch a list of all available repositories
     *
     * NOTE Since we only have one repository at the moment, this is expected to only return one result
     *
     * @return repositoryList[]
     */
    public function getRepositories()
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
    public function getRepositoryInfo($repositoryId)
    {
        $repositoryInfo = $this->RepositoryService->getRepositoryInfo($repositoryId);
        if (PEAR::isError($repositoryInfo))
        {
            return array(
                "status_code" => 1,
                "message" => "Failed getting repository information"
            );
        }

        // TODO output this manually, the function works but only for some objects so rather avoid it completely?
        // NOTE the problems appear to be due to recursive objects
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
    public function getTypes($repositoryId, $typeId = '', $returnPropertyDefinitions = false,
                             $maxItems = 0, $skipCount = 0, &$hasMoreItems = false)
    {
        try {
            $repositoryObjectTypeResult = $this->RepositoryService->getTypes($repositoryId, $typeId, $returnPropertyDefinitions,
                                                                             $maxItems, $skipCount, $hasMoreItems);
        }
        catch (Exception $e)
        {
            return array(
                "status_code" => 1,
                "message" => $e->getMessage()
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
    public function getTypeDefinition($repositoryId, $typeId)
    {
        try {
            $typeDefinitionResult = $this->RepositoryService->getTypeDefinition($repositoryId, $typeId);
        }
        catch (Exception $e)
        {
            return array(
                "status_code" => 1,
                "message" => $e->getMessage()
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

}

?>