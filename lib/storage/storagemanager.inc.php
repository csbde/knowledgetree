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
 * -------------------------------------------------------------------------
 *
 * Manages the storage and storage location of a file.
 *
 * The Document Manager may only use setDiskPath on the oDocument
 * object, and should not update the document object.
 */
require_once(KT_DIR . '/search2/indexing/indexerCore.inc.php');

class KTStorageManager {
    /**
     * Puts the given file into storage, and saves the storage details
     * into the document.
     */
    function upload (&$oDocument, $sTmpFilePath) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Upload a temporary file
     *
     * @param unknown_type $sUploadedFile
     * @param unknown_type $sTmpFilePath
     * @return unknown
     */
    function uploadTmpFile($sUploadedFile, $sTmpFilePath, $aOptions = null) {
        return PEAR::raiseError(_kt('Not implemented'));
    }

    function writeToFile($sTmpFilePath, $sDocumentFileSystemPath, $aOptions = null) {
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
		$documentid = $oDocument->getId();
    	$indexer = Indexer::get();
        $indexer->deleteDocument($documentid);
    }

    function deleteVersion(&$oVersion) {
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
    static function &getSingleton() {


    	static $singleton = null;

    	if (is_null($singleton))
    	{
    		$oConfig =& KTConfig::getSingleton();
        	$sDefaultManager = 'KTOnDiskHashedStorageManager';
        	$klass = $oConfig->get('storage/manager', $sDefaultManager);
        	if (!class_exists($klass)) {
            	$klass = $sDefaultManager;
        	}
        	$singleton = new $klass;
    	}

    	return $singleton;
    }
}

?>
