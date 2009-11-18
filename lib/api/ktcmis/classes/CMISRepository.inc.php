<?php

/**
* CMIS Repository API class for KnowledgeTree.
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

require_once(CMIS_DIR . '/classes/CMISRepositoryInfo.inc.php');

/**
 * A CMIS Repository.
 */
class CMISRepository {

    private $repositoryId;
    private $repositoryURI;

    /**
     * @var $RepositoryInfo The class holding information about this repository
     */
    private $RepositoryInfo;
    /**
     *
     * @var object $objectTypes The supported object types
     */
    private $objectTypes;

    function __construct($repositoryId, $config = null)
    {
        $this->repositoryId = $repositoryId;
        $this->RepositoryInfo = new CMISRepositoryInfo();
        $this->getConfig($config);
    }

    function getConfig($config = null)
    {
        // if not supplied config xml
        if (is_null($config))
        {
            // fetch configuration file
            // TODO what if file does not exist?
            $xml = simplexml_load_file(CMIS_DIR . '/config/repositories.xml');

            foreach($xml->repository as $repository)
            {
                $currentRepo = $repository->repositoryInfo[0]->repositoryId;
                if ((int)$currentRepo == $this->repositoryId)
                {
                    $config = $repository;
                    break;
                }
            }
        }

        // set URI
        $this->repositoryURI = (string)$config->repositoryURI[0];

        // set info
        foreach($config->repositoryInfo[0] as $field => $value)
        {
//            if ($field == 'rootFolderId') {
//                $value = CMISUtil::encodeObjectId(FOLDER, $value);
//            }
            $this->setRepositoryInfoField($field, (string)$value);
        }

        // set capabilities
        foreach($config->repositoryCapabilities[0] as $field => $value)
        {
            $this->setCapabilityField($field, (string)$value);
        }

        // set supported document types
        foreach($config->supportedTypes[0] as $field => $value)
        {
            $this->objectTypes[] = (string)$value;
        }
    }

    /**
     * Set a single value for RepositoryInfo
     *
     * @param string $field
     * @param string/int $value
     */
    function setRepositoryInfoField($field, $value)
    {
        $this->RepositoryInfo->setFieldValue($field, $value);
    }

    /**
     * Set multiple values for RepositoryInfo
     *
     * @param array $info
     */
    function setRepositoryInfo($info)
    {
        $this->RepositoryInfo->setInfo($info);
    }

    /**
     * Set a single value for RepositoryCapabilities
     *
     * @param string $field
     * @param string/int $value
     */
    function setCapabilityField($field, $value)
    {
        $this->RepositoryInfo->setCapabilityValue($field, $value);
    }

    function getRepositoryID()
    {
        return $this->RepositoryInfo->getRepositoryID();
    }

    function getRepositoryName()
    {
        return $this->RepositoryInfo->getRepositoryName();
    }

    function getRepositoryURI()
    {
        return $this->repositoryURI;
    }

    function getRepositoryInfo()
    {
        return $this->RepositoryInfo;
    }

    function getTypes()
    {
        return $this->objectTypes;
    }

}

?>
