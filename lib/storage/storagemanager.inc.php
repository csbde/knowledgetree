<?php

/**
 * Manages the storage and storage location of a file.
 *
 * The Document Manager may only use setDiskPath on the oDocument
 * object, and should not update the document object.
 */

class KTStorageManager {
    /**
     * Puts the given file into storage, and saves the storage details
     * into the document.
     */
    function upload (&$oDocument, $sTmpFilePath) {
        return PEAR::raiseError("Not implemented");
    }

    /**
     * Gets the latest verison of a document's contents from storage and
     * writes it to the standard content with HTTP headers as an
     * attachment.
     */
    function download (&$oDocument) {
        return PEAR::raiseError("Not implemented");
    }

    /**
     * Gets a specific version of a document's contents from storage and
     * writes it to the standard content with HTTP headers.
     */
    function downloadVersion (&$oDocument) {
        return PEAR::raiseError("Not implemented");
    }

    /**
     * Gets the latest verison of a document's contents from storage and
     * writes it to the standard content with HTTP headers for inline
     * view.
     */
    function inlineView (&$oDocument) {
        return PEAR::raiseError("Not implemented");
    }

    /**
     * Performs any storage changes necessary to account for a changed
     * repository path. 
     *
     * The info arrays must contain the following information:
     *      "names" => an array of the names of the folders in the path
     *          from the root of the repository
     *          ("Root Folder", "foo", "bar", "baz")
     *      "ids" => an array of the ids of the folders in the path from
     *          the root of the repository
     *          (1, 3, 9, 27)
     */
    function move (&$oDocument, $aOldInfo, $aNewInfo) {
        return PEAR::raiseError("Not implemented");
    }

    /**
     * Perform any storage changes necessary to account for moving one
     * tree in the repository to a different location.
     */
    function moveFolder ($oFolder, $oDestFolder) {
        return PEAR::raiseError("Not implemented");
    }

    /**
     * Performs any storage changes necessary to account for the
     * document being marked as deleted.
     */
    function delete (&$oDocument) {
        return PEAR::raiseError("Not implemented");
    }

    /**
     * Remove the documents (already marked as deleted) from the
     * storage.
     */
    function expunge (&$oDocument) {
        return PEAR::raiseError("Not implemented");
    }

    /**
     * Performs any storage changes necessary to account for the
     * document (previously marked as deleted) being restored.
     */
    function restore (&$oDocument) {
        return PEAR::raiseError("Not implemented");
    }

    function getPath(&$oDocument) {
        return PEAR::raiseError("Not implemented");
    }

    function setPath(&$oDocument) {
        return PEAR::raiseError("Not implemented");
    }

    function generatePath(&$oDocument) {
        return PEAR::raiseError("Not implemented");
    }

    function createFolder($sFolderPath) {
        return PEAR::raiseError("Not implemented");
    }
}

class KTStorageManagerUtil {
    function &getSingleton() {
        $oConfig =& KTConfig::getSingleton();
        $sDefaultManager = 'KTOnDiskPathStorageManager';
        $klass = $oConfig->get('storage/manager', $sDefaultManager);
        if (!class_exists($klass)) {
            $klass = $sDefaultManager;
        }
        if (!KTUtil::arrayGet($GLOBALS, 'KTStorageManager')) {
            $GLOBALS['KTStorageManager'] =& new $klass;
        }
        return $GLOBALS['KTStorageManager'];
    }
}

?>
