<?php

require_once(KT_LIB_DIR . '/ktentity.inc');

class KTDocumentContentVersion extends KTEntity {
    var $_bUsePearError = true;

    /** Which document is this content a version of? */
    var $iDocumentId;

    /** What was the filename of the stored content */
    var $sFilename;

    /** How big was the stored content */
    var $iSize;

    /** Which MIME type was this content */
    var $iMimeId;

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
        "sFilename" => 'filename',
        "iSize" => 'size',
        "iMimeId" => 'mime_id',
        "iMajorVersion" => 'major_version',
        "iMinorVersion" => 'minor_version',
        "sStoragePath" => 'storage_path',
    );

    function KTDocumentContentVersion() {
    }

    
}

?>
