<?php
/**
 * CMIS Repository Document Object API class for KnowledgeTree.
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
 * @version Version 0.1
 */

require_once(CMIS_DIR . '/classes/CMISObject.inc.php');
require_once(CMIS_DIR . '/classes/CMISDocumentPropertyCollection.inc.php');
require_once(CMIS_DIR . '/util/CMISUtil.inc.php');

// TODO Property Type Definitions (only done Attributes up to now)

class CMISDocumentObject extends CMISObject {

    protected $versionable; // Bolean; indicates whether objects of this type are versionable
    protected $contentStreamAllowed; // Enum; notallowed, allowed, required
    protected $ktapi;
    
    // TODO some of this should probably come from configuration files as it is repository specific
    public function __construct($documentId = null, &$ktapi = null, $uri = null)
    {
        $this->ktapi = $ktapi;

        // attributes
        $this->id = 'cmis:document';
        $this->localName = null; // <repository-specific>
        $this->localNamespace = null; // <repository-specific>
        $this->queryName = 'cmis:document';
        $this->displayName = 'Document'; // <repository-specific>
        $this->baseId = 'cmis:document';
        $this->parentId = null; // MUST NOT be set for base document object-type
        $this->description = null; // <repository-specific>
        $this->creatable = true; // <repository-specific>
        $this->fileable = true;
        $this->queryable = true; // SHOULD be true
        $this->controllablePolicy = false; // <repository-specific>
        $this->includedInSupertypeQuery = true; // <repository-specific>
        $this->versionable = true; // <repository-specific>
        $this->contentStreamAllowed = 'required'; // <repository-specific> notallowed/allowed/required
        $this->controllableACL = false; // <repository-specific>
        $this->fulltextIndexed = false; // <repository-specific>
        
        // properties
        $this->properties = new CMISDocumentPropertyCollection();

        // set base object property definitions
//        parent::__construct();

        // set document specific property definitions

        if (!is_null($documentId))
        {
            try {
                $this->_get($documentId);
            }
            catch (exception $e) {
                throw new ObjectNotFoundException($e->getMessage());
            }
        }
        
        parent::__construct();
    }
    
    // TODO abstract shared stuff to base class where possible
    protected function _get($documentId)
    {
        $object = $this->ktapi->get_document_by_id((int)$documentId);

        // document does not exist?
        if (PEAR::isError($object)) {
            throw new ObjectNotFoundException('The document you are trying to access does not exist or is inaccessible');
        }

        $objectProperties = $object->get_detail();

        $this->_setPropertyInternal('objectId', CMISUtil::encodeObjectId($this->id, $objectProperties['document_id']));
        // prevent doubled '/' chars
        $uri = preg_replace_callback('/([^:]\/)\//',
                                     create_function('$matches', 'return $matches[1];'),
                                     $this->uri
                                     . 'action.php?kt_path_info=ktnetwork.inlineview.actions.view&fDocumentId='
                                     . $objectProperties['document_id']);
        // NOTE what about instead creating a downloadable version with appropriate link?  see ktapi::download_document
        //      also ktapidocument::get_download_url
//        $this->_setPropertyInternal('uri', $uri);
        $this->_setPropertyInternal('uri', '');
        $this->_setPropertyInternal('createdBy', $objectProperties['created_by']);
        $this->_setPropertyInternal('creationDate', $objectProperties['created_date']);
        $this->_setPropertyInternal('lastModifiedBy', $objectProperties['modified_by']);
        $this->_setPropertyInternal('lastModificationDate', $objectProperties['modified_date']);
        $this->_setPropertyInternal('changeToken', null);
        $this->_setPropertyInternal('name', $objectProperties['title']);
        $this->_setPropertyInternal('parentId', CMISUtil::encodeObjectId(CMIS_FOLDER, $objectProperties['folder_id']));
        $this->_setPropertyInternal('isImmutable', $objectProperties['is_immutable']);
        // NOTE if access to older versions is allowed, this will need to be checked, else just set to yes
        //      see ktapi::get_document_version_history
        // NOTE see ktapi::is_latest_version
        $this->_setPropertyInternal('isLatestVersion', true);
        $this->_setPropertyInternal('isMajorVersion', (strstr($objectProperties['version'], '.') ? false : true));
        // NOTE if access to older versions is allowed, this will need to be checked, else just set to yes
        //      see ktapi::get_document_version_history
        // NOTE see ktapi::is_latest_version
        $this->_setPropertyInternal('isLatestMajorVersion', true);
        $this->_setPropertyInternal('versionLabel', $objectProperties['version']);
        // VersionSeriesId should be the id of the latest version
        // NOTE this may change in the future but is easiest for the current implementation
        $this->_setPropertyInternal('versionSeriesId', $objectProperties['version']);
        if ($objectProperties['checked_out_by'] != 'n/a')
        {
            $checkedOut = true;
            $checkedOutBy = $objectProperties['checked_out_by'];
            // TODO this is not what it will actually be, just a convenient placeholder
            $checkedOutId = $objectProperties['version'];
        }
        else
        {
            $checkedOut = false;
            $checkedOutBy = null;
            $checkedOutId = null;
        }
        $this->_setPropertyInternal('isVersionSeriesCheckedOut', $checkedOut);
        $this->_setPropertyInternal('versionSeriesCheckedOutBy', $checkedOutBy);
        // TODO presumably this is the ID of the Private Working Copy created on checkout?
        //      will find out more when we do checkout/checkin
        $this->_setPropertyInternal('versionSeriesCheckedOutId', $checkedOutId);
        // TODO currently not returned by KnowledgeTree?
        $this->_setPropertyInternal('checkinComment', null);
        $this->_setPropertyInternal('contentStreamLength', $objectProperties['filesize']);
        $this->_setPropertyInternal('contentStreamMimeType', $objectProperties['mime_type']);
        $this->_setPropertyInternal('contentStreamFilename', $objectProperties['filename']);
        $this->_setPropertyInternal('contentStreamUri', $this->getProperty('objectId') . '/' . $objectProperties['filename']);
        $this->_setPropertyInternal('author', $objectProperties['created_by']);
    }
    
    /**
     * Returns a listing of all attributes in an array
     *
     * @return array $attributes
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();

        // add document object-type specific attributes
        $attributes['versionable'] = $this->versionable;
        $attributes['contentStreamAllowed'] = $this->contentStreamAllowed;

        return $attributes;
    }

}

?>
