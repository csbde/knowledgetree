<?php

/**
 * CMIS Repository Info API class for KnowledgeTree.
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

require_once(CMIS_DIR . '/classes/CMISRepositoryCapabilities.inc.php');

/**
 * CMIS Repository Service.
 */
class CMISRepositoryInfo {

    protected $repositoryId; // The identifier for the Repository
    protected $repositoryName; // A display name for the Repository
    protected $repositoryRelationship; // A string that MAY describe how this repository relates to other repositories.
    protected $repositoryDescription; // A display description for the Repository.
    protected $vendorName; // A display name for the vendor of the Repository’s underlying application.
    protected $productName; // A display name for the Repository’s underlying application.
    protected $productVersion; // A display name for the version number of the Repository’s underlying application.
    protected $rootFolderId; // The ID of the Root Folder Object for the Repository.

    protected $capabilities;

    protected $cmisVersionsSupported; // String that indicates what versions of the CMIS specification the repository can support
    protected $repositorySpecificInformation; // XML format; MAY be used by the Repository to return additional XML.

    function __construct()
    {
        $this->capabilities = new CMISRepositoryCapabilities();
    }

    /**
     * Set a single field value
     *
     * @param string $field
     * @param string/int $value
     * @return a collection of repository entries
     */
    function setFieldValue($field, $value)
    {
        $this->{$field} = $value;
    }

    /**
     * Set multiple field values
     *
     * @param array $info
     */
    function setInfo($info)
    {
        foreach($info as $field => $value)
        {
            $this->setFieldValue($field, $value);
        }
    }

    /**
     * Set a single capability field value
     *
     * @param string $field
     * @param string/int $value
     * @return a collection of repository entries
     */
    function setCapabilityValue($field, $value)
    {
        $this->capabilities->setFieldValue($field, $value);
    }

    /**
     * Sets the capabilities from an existing capabilities object
     *
     * @param object $capabilities
     */
    function setCapabilities($capabilities)
    {
        $this->capabilities = $capabilities;
    }

    function getRepositoryId()
    {
        return $this->repositoryId;
    }

    function getRepositoryName()
    {
        return $this->repositoryName;
    }

    function getCapabilities()
    {
        return $this->capabilities;
    }

    function getRepositoryRelationship()
    {
        return $this->repositoryRelationship;
    }

    function getRepositoryDescription()
    {
        return $this->repositoryDescription;
    }

    function getVendorName()
    {
        return $this->vendorName;
    }

    function getProductName()
    {
        return $this->productName;
    }

    function getProductVersion()
    {
        return $this->productVersion;
    }

    function getRootFolderId()
    {
        return $this->rootFolderId;
    }

    function getcmisVersionsSupported()
    {
        return $this->cmisVersionsSupported;
    }

}

?>
