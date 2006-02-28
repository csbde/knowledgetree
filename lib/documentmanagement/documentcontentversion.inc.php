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

class KTDocumentContentVersion extends KTEntity {
    var $_bUsePearError = true;

    /** Which document is this content a version of? */
    var $iDocumentId;

    /** What was the filename of the stored content */
    var $sFileName;

    /** How big was the stored content */
    var $iSize;

    /** Which MIME type was this content */
    var $iMimeTypeId;

    /** User-specified major version for this content */
    var $iMajorVersion;

    /** User-specified minor version for this content */
    var $iMinorVersion;

    /** Where in the storage this file can be found */
    var $sStoragePath;

    var $_aFieldToSelect = array(
        "iId" => "id",

        // transaction-related
        "iDocumentId" => 'document_id',
        "sFileName" => 'filename',
        "iSize" => 'size',
        "iMimeTypeId" => 'mime_id',
        "iMajorVersion" => 'major_version',
        "iMinorVersion" => 'minor_version',
        "sStoragePath" => 'storage_path',
    );

    function KTDocumentContentVersion() {
    }

    function getFileName() { return $this->sFileName; }
    function setFileName($sNewValue) { $this->sFileName = $sNewValue; }
    function getFileSize() { return $this->iSize; }
    function setFileSize($iNewValue) { $this->iSize = $iNewValue; }
    function getSize() { return $this->iSize; }
    function setSize($iNewValue) { $this->iSize = $iNewValue; }
    function getMimeTypeId() { return $this->iMimeTypeId; }
    function setMimeTypeId($iNewValue) { $this->iMimeTypeId = $iNewValue; }
    function getMajorVersionNumber() { return $this->iMajorVersion; }
    function setMajorVersionNumber($iNewValue) { $this->iMajorVersion = $iNewValue; }
    function getMinorVersionNumber() { return $this->iMinorVersion; }
    function setMinorVersionNumber($iNewValue) { $this->iMinorVersion = $iNewValue; }
    function getStoragePath() { return $this->sStoragePath; }
    function setStoragePath($sNewValue) { $this->sStoragePath = $sNewValue; }

    function _table() {
        return KTUtil::getTableName('document_content_version');
    }

    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTDocumentContentVersion', $aOptions);
    }

    function create() {
        if (empty($this->iSize)) {
            $this->iSize = 0;
        }
        if (empty($this->iMimeTypeId)) {
            $this->iMimeTypeId = 0;
        }
        if (empty($this->iMajorVersion)) {
            $this->iMajorVersion = 0;
        }
        if (empty($this->iMinorVersion)) {
            $this->iMinorVersion = 1;
        }
        return parent::create();
    }

    function &get($iId) {
        return KTEntityUtil::get('KTDocumentContentVersion', $iId);
    }

    function &getByDocument($oDocument) {
        $iDocumentId = KTUtil::getId($oDocument);
        return KTEntityUtil::getByDict('KTDocumentContentVersion', array(
            'document_id' => $iDocumentId,
        ), array(
            'multi' => true,
        ));
    }
}

?>
