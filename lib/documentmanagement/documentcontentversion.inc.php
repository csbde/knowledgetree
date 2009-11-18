<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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
 *
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

    var $md5hash;

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
        'md5hash' => 'md5hash'
    );
    function KTDocumentContentVersion() {
    }

    function getFileName() { return $this->sFileName; }
    function setFileName($sNewValue) { $this->sFileName = $sNewValue; }
    function getDocumentId() { return $this->iDocumentId; }
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
    function getStorageHash() { return $this->md5hash; }
    function setStorageHash($sNewValue) { $this->md5hash = $sNewValue; }

    function getVersion() {
        return sprintf("%s.%s", $this->getMajorVersionNumber(), $this->getMinorVersionNumber());
    }

    function _table() {
        return KTUtil::getTableName('document_content_version');
    }

    function &createFromArray($aOptions) {
        return  KTEntityUtil::createFromArray('KTDocumentContentVersion', $aOptions);
    }




    function create() {
        if (empty($this->iSize)) {
            $this->iSize = 0;
        }
        if (empty($this->iMimeTypeId)) {
            $this->iMimeTypeId = 9;
        }
        if (is_null($this->iMajorVersion)) {
            $this->iMajorVersion = 0;
        }
        if (is_null($this->iMinorVersion)) {
            $this->iMinorVersion = 1;
        }
        return parent::create();
    }

    function &get($iId) {
        return KTEntityUtil::get('KTDocumentContentVersion', $iId);
    }

    function &getByDocument($oDocument, $aOptions = null) {
        $aOptions = KTUtil::meldOptions(array(
            'multi' => true,
        ), $aOptions);
        $iDocumentId = KTUtil::getId($oDocument);
        return KTEntityUtil::getByDict('KTDocumentContentVersion', array(
            'document_id' => $iDocumentId,
        ), $aOptions);
    }
}

?>
