<?php
/**
 * CMIS Repository Document Object API class for KnowledgeTree.
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

require_once(CMIS_DIR . '/classes/CMISObject.inc.php');
require_once(CMIS_DIR . '/classes/CMISDocumentPropertyCollection.inc.php');
require_once(CMIS_DIR . '/util/CMISUtil.inc.php');

// TODO Property Type Definitions (only done Attributes up to now)

class CMISDocumentObject extends CMISObject {

    protected $versionable;
    private $ktapi;
    private $uri;

    // TODO some of this should probably come from configuration files as it is repository specific
    function __construct($documentId = null, &$ktapi = null, $uri = null)
    {
        $this->ktapi = $ktapi;
        // uri to use for document links
        $this->uri = $uri;

        // attributes
        $this->typeId = 'Document'; // <repository-specific>
        $this->queryName = 'Document';
        $this->displayName = ''; // <repository-specific>
        $this->baseType = 'document';
        $this->baseTypeQueryName = 'Document';
        $this->parentId = null; // MUST NOT be set
        $this->description = ''; // <repository-specific>
        $this->creatable = ''; // <repository-specific>
        /*
         * fileable SHOULD be set as follows:
         * If the repository does NOT support the “un-filing” capability:
         * TRUE
         * If the repository does support the “un-filing” capability:
         * <repository-specific>, but SHOULD be TRUE
         */
        $this->fileable = true; // TODO implement check for whether un-filing is supported
        $this->queryable = true; // SHOULD be true
        $this->includedInSupertypeQuery = true; //
        // TODO determine what these next 3 should be
        $this->controllable = false; // <repository-specific>
        $this->versionable = true; // <repository-specific>
        $this->contentStreamAllowed = 'required'; // <repository-specific> notAllowed/allowed/required

        // properties
        $this->properties = new CMISDocumentPropertyCollection();

        // set base object property definitions
//        parent::__construct();

        // set document specific property definitions

        if (!is_null($documentId))
        {
            try {
                $this->get($documentId);
            }
            catch (exception $e) {
                throw new ObjectNotFoundException($e->getMessage());
            }
        }
        
        // TODO throw exception if unable to create?
    }

    private function get($documentId)
    {
        $object = $this->ktapi->get_document_by_id((int)$documentId);

        // document does not exist?
        if (PEAR::isError($object)) {
            throw new ObjectNotFoundException('The document you are trying to access does not exist or is inaccessible');
        }

        $objectProperties = $object->get_detail();

        $this->_setPropertyInternal('ObjectId', CMISUtil::encodeObjectId($this->typeId, $objectProperties['document_id']));
        // prevent doubled '/' chars
        $uri = preg_replace_callback('/([^:]\/)\//',
                                     create_function('$matches', 'return $matches[1];'),
                                     $this->uri
                                     . 'action.php?kt_path_info=ktnetwork.inlineview.actions.view&fDocumentId='
                                     . $objectProperties['document_id']);
        // NOTE what about instead creating a downloadable version with appropriate link?  see ktapi::download_document
        //      also ktapidocument::get_download_url
//        $this->_setPropertyInternal('Uri', $uri);
        $this->_setPropertyInternal('Uri', '');
        // TODO what is this?  Assuming it is the object type id, and not OUR document type?
        $this->_setPropertyInternal('ObjectTypeId', $this->getAttribute('typeId'));
        // Needed to distinguish type
        $this->_setPropertyInternal('BaseType', strtolower($this->getAttribute('typeId')));
        $this->_setPropertyInternal('CreatedBy', $objectProperties['created_by']);
        $this->_setPropertyInternal('CreationDate', $objectProperties['created_date']);
        $this->_setPropertyInternal('LastModifiedBy', $objectProperties['modified_by']);
        $this->_setPropertyInternal('LastModificationDate', $objectProperties['modified_date']);
        $this->_setPropertyInternal('ChangeToken', null);
        $this->_setPropertyInternal('Name', $objectProperties['title']);
        $this->_setPropertyInternal('ParentId', $objectProperties['folder_id']);
        $this->_setPropertyInternal('IsImmutable', $objectProperties['is_immutable']);
        // NOTE if access to older versions is allowed, this will need to be checked, else just set to yes
        //      see ktapi::get_document_version_history
        // NOTE see ktapi::is_latest_version
        $this->_setPropertyInternal('IsLatestVersion', true);
        $this->_setPropertyInternal('IsMajorVersion', (strstr($objectProperties['version'], '.') ? false : true));
        // NOTE if access to older versions is allowed, this will need to be checked, else just set to yes
        //      see ktapi::get_document_version_history
        // NOTE see ktapi::is_latest_version
        $this->_setPropertyInternal('IsLatestMajorVersion', true);
        $this->_setPropertyInternal('VersionLabel', $objectProperties['version']);
        // VersionSeriesId should be the id of the latest version
        // NOTE this may change in the future but is easiest for the current implementation
        $this->_setPropertyInternal('VersionSeriesId', $objectProperties['version']);
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
        $this->_setPropertyInternal('IsVersionSeriesCheckedOut', $checkedOut);
        $this->_setPropertyInternal('VersionSeriesCheckedOutBy', $checkedOutBy);
        // TODO presumably this is the ID of the Private Working Copy created on checkout?
        //      will find out more when we do checkout/checkin
        $this->_setPropertyInternal('VersionSeriesCheckedOutId', $checkedOutId);
        // TODO currently not returned by KnowledgeTree?
        $this->_setPropertyInternal('CheckinComment', null);
        $this->_setPropertyInternal('ContentStreamLength', $objectProperties['filesize']);
        $this->_setPropertyInternal('ContentStreamMimeType', $objectProperties['mime_type']);
        $this->_setPropertyInternal('ContentStreamFilename', $objectProperties['filename']);
        $this->_setPropertyInternal('ContentStreamUri', $this->getProperty('ObjectId') . '/' . $objectProperties['filename']);
        $this->_setPropertyInternal('Author', $objectProperties['created_by']);
    }

}

?>
