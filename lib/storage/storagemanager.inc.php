<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 * -------------------------------------------------------------------------
 *
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
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Gets the latest verison of a document's contents from storage and
     * writes it to the standard content with HTTP headers as an
     * attachment.
     */
    function download (&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Gets a specific version of a document's contents from storage and
     * writes it to the standard content with HTTP headers.
     */
    function downloadVersion (&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Gets the latest verison of a document's contents from storage and
     * writes it to the standard content with HTTP headers for inline
     * view.
     */
    function inlineView (&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
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
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Perform any storage changes necessary to account for moving one
     * tree in the repository to a different location.
     */
    function moveFolder ($oFolder, $oDestFolder) {
        return PEAR::raiseError(_kt("Not implemented"));
    }
    
    function renameFolder($oFolder, $sNewName) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Perform any storage changes necessary to account for a copied
     * document object.
     */
     function copy ($oSrcDocument, &$oNewDocument) {
        return PEAR::raiseError(_kt("Not implemented"));   
     }

    /**
     * Performs any storage changes necessary to account for the
     * document being marked as deleted.
     */
    function delete (&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Remove the documents (already marked as deleted) from the
     * storage.
     */
    function expunge (&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Performs any storage changes necessary to account for the
     * document (previously marked as deleted) being restored.
     */
    function restore (&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    function getPath(&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    function setPath(&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    function generatePath(&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    function createFolder($sFolderPath) {
        return PEAR::raiseError(_kt("Not implemented"));
    }
    
    function renameDocument(&$oDocument, $oOldContentVersion, $sNewFilename) {
        return PEAR::raiseError(_kt("Not implemented"));
    }
}

class KTStorageManagerUtil {
    function &getSingleton() {
        $oConfig =& KTConfig::getSingleton();
        $sDefaultManager = 'KTOnDiskHashedStorageManager';
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
