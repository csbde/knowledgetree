<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

require_once(KT_LIB_DIR . '/ktentity.inc');

class KTDocumentMetadataVersion extends KTEntity {
    var $_bUsePearError = true;

    /** Which document we are a version of */
    var $iDocumentId;

    /** Which metadata version of the document we are describing */
    var $iMetadataVersion;

    /** Which content was associated with this metadata version */
    var $iContentVersionId;

    /** The document type of the document during this version */
    var $iDocumentTypeId;

    /** The name of the document during this version */
    var $sName;

    /** The description of the document during this version */
    var $sDescription;

    /** The status of the document during this version */
    var $iStatusId;

    /** When this version was created */
    var $dVersionCreated;

    /** Who created this version */
    var $iVersionCreatorId;

    var $_aFieldToSelect = array(
        "iId" => "id",

        "iDocumentId" => 'document_id',
        "iMetadataVersion" => 'metadata_version',
        "iContentVersionId" => 'content_version_id',

        "iDocumentTypeId" => 'document_type_id',

        "sName" => 'name',
        "sDescription" => 'description',

        "iStatusId" => 'status_id',

        "dVersionCreated" => 'version_created',
        "iVersionCreatorId" => 'version_creator_id',
    );

    function KTDocumentMetadataVersion() {
    }

    // {{{ getters/setters
    function getDocumentId() { return $this->iDocumentId; }
    function setDocumentId($iNewValue) { $this->iDocumentId = $iNewValue; }
    function getMetadataVersion() { return $this->iMetadataVersion; }
    function setMetadataVersion($iNewValue) { $this->iMetadataVersion = $iNewValue; }
    function getContentVersionId() { return $this->iContentVersionId; }
    function setContentVersionId($iNewValue) { $this->iContentVersionId = $iNewValue; }
    function setContentVersion($iNewValue) { $this->iContentVersion = $iNewValue; }
    function getDocumentTypeId() { return $this->iDocumentTypeId; }
    function setDocumentTypeId($iNewValue) { $this->iDocumentTypeId = $iNewValue; }
    function getName() { return $this->sName; }
    function setName($sNewValue) { $this->sName = $sNewValue; }
    function getDescription() { return $this->sDescription; }
    function setDescription($sNewValue) { $this->sDescription = $sNewValue; }
    function getStatusId() { return $this->iStatusId; }
    function setStatusId($iNewValue) { $this->iStatusId = $iNewValue; }
    function getVersionCreated() { return $this->dVersionCreated; }
    function setVersionCreated($dNewValue) { $this->dVersionCreated = $dNewValue; }
    function getVersionCreatorId() { return $this->iVersionCreatorId; }
    function setVersionCreatorId($iNewValue) { $this->iVersionCreatorId = $iNewValue; }
    // }}}

    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTDocumentMetadataVersion', $aOptions);
    }

    function _table() {
        return KTUtil::getTableName('document_metadata_version');
    }

    function create() {
        if (is_null($this->iMetadataVersion)) {
            $this->iMetadataVersion = 0;
        }
        if (is_null($this->dVersionCreated)) {
            $this->dVersionCreated = getCurrentDateTime();
        }
        return parent::create();
    }

    function &get($iId) {
        return KTEntityUtil::get('KTDocumentMetadataVersion', $iId);
    }

    function &getByDocument($oDocument) {
        $iDocumentId = KTUtil::getId($oDocument);
        return KTEntityUtil::getByDict('KTDocumentMetadataVersion', array(
            'document_id' => $iDocumentId,
        ), array(
            'multi' => true,
            'orderby' => 'version_created DESC',
        ));
    }
}

?>
