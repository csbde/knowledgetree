<?php
/**
 * CMIS Repository Folder Object API class for KnowledgeTree.
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
require_once(CMIS_DIR . '/classes/CMISFolderPropertyCollection.inc.php');
require_once(CMIS_DIR . '/util/CMISUtil.inc.php');

class CMISFolderObject extends CMISObject {

    private $ktapi;
    private $uri;

    public function __construct($folderId = null, &$ktapi = null, $uri = null)
    {
        $this->ktapi = $ktapi;
        $this->uri = $uri;

        $this->typeId = 'Folder'; // <repository-specific>
        $this->queryName = 'Folder';
        $this->displayName = ''; // <repository-specific>
        $this->baseType = 'folder';
        $this->baseTypeQueryName = 'Folder';
        $this->parentId = null; // MUST NOT be set
        $this->description = ''; // <repository-specific>
        $this->creatable = ''; // <repository-specific>
        $this->fileable = true;
        $this->queryable = true; // SHOULD be true
        $this->includedInSupertypeQuery = true; //
        $this->controllable = ''; // <repository-specific>
 
        // properties
        $this->properties = new CMISFolderPropertyCollection();

        if (!is_null($folderId))
        {
            try {
                $this->get($folderId);
            }
            catch (exception $e) {
                throw new ObjectNotFoundException($e->getMessage());
            }
        }
    }

    private function get($folderId)
    {
        $object = $this->ktapi->get_folder_by_id((int)$folderId);
        
        // folder does not exist?
        if (PEAR::isError($object)) {
            throw new ObjectNotFoundException('The folder you are trying to access does not exist or is inaccessible');
        }

//          static $allowedChildObjectTypeIds;

        $objectProperties = $object->get_detail();

        $this->_setPropertyInternal('ObjectId', CMISUtil::encodeObjectId($this->typeId, $objectProperties['id']));
        // prevent doubled '/' chars
        $uri = preg_replace_callback('/([^:]\/)\//',
                                     create_function('$matches', 'return $matches[1];'),
                                     $this->uri
                                     . '/browse.php?fFolderId='
                                     . $objectProperties['id']);
        // TODO this url is probably incorrect...needs to be checked
//        $this->_setPropertyInternal('Uri', $uri);
        $this->_setPropertyInternal('Uri', '');
        // TODO what is this?  Assuming it is the object type id, and not OUR document type?
        $this->_setPropertyInternal('ObjectTypeId', $this->getAttribute('typeId'));
        // Needed to distinguish type
        $this->_setPropertyInternal('BaseType', strtolower($this->getAttribute('typeId')));
        $this->_setPropertyInternal('CreatedBy', $objectProperties['created_by']);
        // TODO cannot currently retrieve via ktapi or regular folder code - add as with created by
        $this->_setPropertyInternal('CreationDate', $objectProperties['created_date']);
        // TODO cannot currently retrieve via ktapi or regular folder code - add as with created by
        $this->_setPropertyInternal('LastModifiedBy', $objectProperties['modified_by']);
        // TODO cannot currently retrieve via ktapi or regular folder code - add as with created by
        $this->_setPropertyInternal('LastModificationDate', $objectProperties['modified_date']);
        $this->_setPropertyInternal('ChangeToken', null);
        $this->_setPropertyInternal('Name', $objectProperties['folder_name']);
        $this->_setPropertyInternal('ParentId', $objectProperties['parent_id']);
        $this->_setPropertyInternal('AllowedChildObjectTypeIds', array('Document', 'Folder'));
        $this->_setPropertyInternal('Author', $objectProperties['created_by']);
    }

}

?>
