<?php

/**
* CMIS Repository Service API class for KnowledgeTree.
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
* @version Version 0.1
*/

require_once(CMIS_DIR . '/classes/CMISRepository.inc.php');
require_once(CMIS_DIR . '/classes/CMISObjectTypes.inc.php');

/**
 * CMIS Repository Service.
 */
class CMISRepositoryService {

    /**
     * Gets a list of available repositories.
     *
     * @return a collection of repository entries
     */
    function getRepositories()
    {
        $repositories = array();

        // read the repositories config file to get the list of available repositories
        // TODO what if file does not exist?
        $xml = simplexml_load_file(CMIS_DIR . '/config/repositories.xml');

        foreach($xml->repository as $repositoryXML)
        {
            $repositoryId = (string)$repositoryXML->repositoryInfo[0]->repositoryId;
            $Repository = new CMISRepository($repositoryId, $repositoryXML);
            $repositories[] = $Repository;
        }

        return $repositories;
    }

    /**
     * Fetches the RepositoryInfo object for a specified repository
     *
     * @param string $repositoryId
     * @return object $repositoryInfo
     */
    function getRepositoryInfo($repositoryId)
    {
        $Repository = new CMISRepository($repositoryId);
        $repositoryInfo = $Repository->getRepositoryInfo();

        return $repositoryInfo;
    }

    /**
     * Gets a list of object types supported by the repository
     *
     * @param string $repositoryId The ID of the repository for which object types must be returned
     * @param string $typeId The type to return, ALL if not set
     * @param boolean $returnPropertyDefinitions Return property definitions as well if TRUE
     * @param int $maxItems The maximum number of items to return
     * @param int $skipCount The number of items to skip before starting to return results
     * @param boolean $hasMoreItems TRUE if there are more items to return than were requested
     * @return array $objectTypes
     */
    // NOTE this code may fit better within the Repository Class
    // TODO return for specific type when $typeId is specified
    // TODO other optional parameters
    function getTypes($repositoryId, $typeId = '', $returnPropertyDefinitions = false,
                      $maxItems = 0, $skipCount = 0, &$hasMoreItems = false)
    {        
        if ($typeId != '')
        {
            try {
                $typeDefinition = $this->getTypeDefinition($repositoryId, $typeId);
            }
            catch (Exception $e)
            {
                throw new InvalidArgumentException('Type ' . $typeId . ' is not supported');
            }
        }

        $repository = new CMISRepository($repositoryId);
        $supportedTypes = $repository->getTypes();
        
        $types = array();

        // determine which types are actually supported based on available class definitions
        // compared with the repository's declaration of the types it supports
        $objectTypes = new CMISObjectTypes();
        $types = $objectTypes->getObjectTypes();

        foreach ($types as $key => $objectType)
        {
            // filter this list according to what is defined for the selected repository
            // additionally filter based on typeId if set
            if (!in_array($objectType, $supportedTypes) || (($typeId != '') && ($typeId != $objectType)))
            {
                unset($types[$key]);
                continue;
            }
            $types[$key] = $this->getTypeDefinition($repositoryId, $objectType);
            // only return properties if explicitly requested
            if (!$returnPropertyDefinitions)
            {
                unset($types[$key]['properties']);
            }
        }

        return $types;
    }

    /**
     * Fetches the object type definition for the requested type
     *
     * @param string $repositoryId The ID of the repository
     * @param string $typeId The ID of the object type requested
     * @return $array $typeDefinition
     */
    // NOTE this code may fit better in the Repository Class
    function getTypeDefinition($repositoryId, $typeId)
    {
        $object = 'CMIS' . $typeId . 'Object';
        
        // check whether the object type exists, return error if not
        // consider throwing an exception instead (see General Exceptions)
        if (!file_exists(CMIS_DIR . '/objecttypes/' . $object . '.inc.php'))
        {
            throw new InvalidArgumentException('Type ' . $typeId . ' is not supported');
        }

        $typeDefinition = array();

        require_once(CMIS_DIR . '/objecttypes/' . $object . '.inc.php');
        $cmisObject = new $object;
        $typeDefinition['attributes'] = $cmisObject->getAttributes();
        $typeDefinition['properties'] = $cmisObject->getProperties();

        return $typeDefinition;
    }
    
}

?>