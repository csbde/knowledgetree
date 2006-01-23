<?php

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
