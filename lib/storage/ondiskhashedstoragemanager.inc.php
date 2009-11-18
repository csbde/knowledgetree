<?php
/**
 * $Id$
 *
 * Provides storage for contents of documents on disk, using a hashed
 * folder path and the content version as the file name.
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
 */

require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
require_once(KT_LIB_DIR . '/mime.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/documentcontentversion.inc.php');
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');

class KTOnDiskHashedStorageManager extends KTStorageManager {
    function upload(&$oDocument, $sTmpFilePath, $aOptions = null) {

    	if (!file_exists($sTmpFilePath)) {

            	return new PEAR_Error("$sTmpFilePath does not exist so we can't copy it into the repository! Options: "  . print_r($aOptions,true) );
            }


        $oConfig =& KTConfig::getSingleton();
        $sStoragePath = $this->generateStoragePath($oDocument);
        if (PEAR::isError($sStoragePath)) {
            return $sStoragePath;
        }
        $this->setPath($oDocument, $sStoragePath);
        $oDocument->setFileSize(filesize($sTmpFilePath));
        $sDocumentFileSystemPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oDocument));

        //copy the file accross
        $start_time = KTUtil::getBenchmarkTime();
        $file_size = $oDocument->getFileSize();
        if (OS_WINDOWS) {
            $sDocumentFileSystemPath = str_replace('\\','/',$sDocumentFileSystemPath);
        }
        if ($this->writeToFile($sTmpFilePath, $sDocumentFileSystemPath, $aOptions)) {
            $end_time = KTUtil::getBenchmarkTime();
            global $default;
            $default->log->info(sprintf("Uploaded %d byte file in %.3f seconds", $file_size, $end_time - $start_time));

            //remove the temporary file
            //@unlink($sTmpFilePath);
            if (file_exists($sDocumentFileSystemPath)) {
                return true;
            } else {
            	return new PEAR_Error("$sDocumentFileSystemPath does not exist after write to storage path. Options: " . print_r($aOptions,true));
            }
        } else {
            return new PEAR_Error("Could not write $sTmpFilePath to $sDocumentFileSystemPath with options: " . print_r($aOptions,true));
        }
    }

    /**
     * Upload a temporary file
     *
     * @param unknown_type $sUploadedFile
     * @param unknown_type $sTmpFilePath
     * @return unknown
     */
    function uploadTmpFile($sUploadedFile, $sTmpFilePath, $aOptions = null) {

        //copy the file accross
        if (OS_WINDOWS) {
            $sTmpFilePath = str_replace('\\','/',$sTmpFilePath);
        }
        if ($this->writeToFile($sUploadedFile, $sTmpFilePath, $aOptions)) {
            if (file_exists($sTmpFilePath)) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    function writeToFile($sTmpFilePath, $sDocumentFileSystemPath, $aOptions = null) {
        // Make it easy to write compressed/encrypted storage
        if(isset($aOptions['copy_upload']) && ($aOptions['copy_upload'] == 'true')) {
            return copy($sTmpFilePath, $sDocumentFileSystemPath);
        }

        if (is_uploaded_file($sTmpFilePath))
            $res = @move_uploaded_file($sTmpFilePath, $sDocumentFileSystemPath);
        else
            $res = @rename($sTmpFilePath, $sDocumentFileSystemPath);

        if ($res === false)
        {
        	$res = @copy($sTmpFilePath, $sDocumentFileSystemPath);
        }

        return $res;
    }

    function getPath(&$oDocument) {
        return $oDocument->getStoragePath();
    }

    function setPath(&$oDocument, $sNewPath) {
        $oDocument->setStoragePath($sNewPath);
    }

    function generateStoragePath(&$oDocument) {
        return $this->generateStoragePathForVersion($oDocument->getContentVersionId());
    }

    function generateStoragePathForVersion($oContentVersion) {
        $iId = KTUtil::getId($oContentVersion);
        $str = (string)$iId;
        if (strlen($str) < 4) {
            $str = sprintf('%s%s', str_repeat('0', 4 - strlen($str)), $str);
        }
        if (strlen($str) % 2 == 1) {
            $str = sprintf('0%s', $str);
        }

        $str = substr($str, 0, -2);

        $dir = preg_replace('#(\d\d)(\d\d)#', '\1/\2', $str);

        // Now, create the directory (including intermediaries)
        $oConfig =& KTConfig::getSingleton();
        $sDocumentRoot = $oConfig->get('urls/documentRoot');

        $path = "";
        foreach(split('/', $dir) as $sDirPart) {
            $path = sprintf('%s/%s', $path, $sDirPart);
            $createPath = sprintf('%s%s', $sDocumentRoot, $path);
            if (!file_exists($createPath)) {
                $res = @mkdir($createPath, 0777, true);
                if ($res === false) {
                    return PEAR::raiseError(sprintf(_kt("Could not create directory for storage" .': ' . '%s') , $createPath));
                }
            }
        }
        return sprintf("%s/%d", $dir, $iId);
    }

    function temporaryFile(&$oDocument) {
        $oConfig =& KTConfig::getSingleton();
        return sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oDocument));
    }

    function freeTemporaryFile($sPath) {
        // Storage uses file-on-filesystem for temporaryFile
        return;
    }

    function download($oDocument, $bIsCheckout = false) {
        global $default;

        //get the path to the document on the server
        //$docRoot = $default->documentRoot;
        $oConfig =& KTConfig::getSingleton();
        $docRoot  = $oConfig->get('urls/documentRoot');

        $path = $docRoot .'/'. $oDocument->getStoragePath();

        // Ensure the file exists
        if (file_exists($path)) {
            // Get the mime type
            $mimeId = $oDocument->getMimeTypeID();
            $mimetype = KTMime::getMimeTypeName($mimeId);

            if ($bIsCheckout && $default->fakeMimetype) {
                // note this does not work for "image" types in some browsers
                $mimetype = 'application/x-download';
            }

            $sFileName = $oDocument->getFileName( );
            $iFileSize = $oDocument->getFileSize();

            KTUtil::download($path, $mimetype, $iFileSize, $sFileName);
        } else {
            return false;
        }
    }

    function createFolder($oFolder) {
        // Storage doesn't deal with folders
        return true;
    }

    function removeFolder($oFolder) {
        // Storage doesn't deal with folders
        return true;
    }

    function removeFolderTree($oFolder) {
        // Storage doesn't deal with folders
        return true;
    }

    function downloadVersion($oDocument, $iVersionId) {
        //get the document
        $oContentVersion = KTDocumentContentVersion::get($iVersionId);
        $oConfig =& KTConfig::getSingleton();
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oContentVersion));
        $sVersion = sprintf("%d.%d", $oContentVersion->getMajorVersionNumber(), $oContentVersion->getMinorVersionNumber());
        if (file_exists($sPath)) {
            // Get the mime type
            $mimeId = $oContentVersion->getMimeTypeID();
            $mimetype = KTMime::getMimeTypeName($mimeId);

            $sFileName = $sVersion.'-'.$oContentVersion->getFileName( );
            $iFileSize = $oContentVersion->getFileSize();

            KTUtil::download($sPath, $mimetype, $iFileSize, $sFileName);
        } else {
            return false;
        }
    }

    function moveDocument(&$oDocument, $oSourceFolder, $oDestinationFolder) {
        // Storage path isn't based on location folder hierarchy
        return true;
    }

    /**
     * Move a file
     *
     * @param string source path
     * @param string destination path
     */
    function move($sOldDocumentPath, $sNewDocumentPath) {
        global $default;
        if (file_exists($sOldDocumentPath)) {
            //copy the file    to the new destination
            if (rename($sOldDocumentPath, $sNewDocumentPath)) {
                //delete the old one
                //@unlink($sOldDocumentPath);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function moveFolder($oFolder, $oDestFolder) {
        // Storage path isn't based on folder hierarchy
        return true;
    }

    function renameFolder($oFolder, $sNewName) {
        // Storage path isn't based on folder hierarchy
        return true;
    }

    /**
     * Perform any storage changes necessary to account for a copied
     * document object.
     */
    function copy($oSrcDocument, &$oNewDocument) {
        // we get the Folder object
        $oVersion = $oNewDocument->_oDocumentContentVersion;
        $oConfig =& KTConfig::getSingleton();
        $sDocumentRoot = $oConfig->get('urls/documentRoot');

        $sNewPath = $this->generateStoragePath($oNewDocument);
        $sFullOldPath = sprintf("%s/%s", $sDocumentRoot, $this->getPath($oSrcDocument));
        $sFullNewPath = sprintf("%s/%s", $sDocumentRoot, $sNewPath);

        $res = KTUtil::copyFile($sFullOldPath, $sFullNewPath);
        if (PEAR::isError($res)) { return $res; }
        $oVersion->setStoragePath($sNewPath);
        $oVersion->update();
    }

    function renameDocument(&$oDocument, $oOldContentVersion, $sNewFilename) {
        // Storage isn't based on document name
        return true;
     }

    function delete($oDocument) {
        // Storage doesn't care if the document is deleted
        return true;
    }

    /**
     * Completely remove a document from the Deleted/ folder
     *
     * return boolean true on successful expunge
     */
    function expunge($oDocument) {
    	parent::expunge($oDocument);
    	$oConfig =& KTConfig::getSingleton();
        $sCurrentPath = $this->getPath($oDocument);

        $sDocumentRoot = $oConfig->get('urls/documentRoot');

        $aVersions = KTDocumentContentVersion::getByDocument($oDocument);
        foreach ($aVersions as $oVersion) {
            $sPath = sprintf('%s/%s', $sDocumentRoot, $oVersion->getStoragePath());
            @unlink($sPath);
        }
        return true;
    }

	/**
	 * Completely remove a document version
	 *
	 * return boolean true on successful delete
	 */
	function deleteVersion($oVersion) {
	    $oConfig =& KTConfig::getSingleton();
	    $sDocumentRoot = $oConfig->get('urls/documentRoot');
	    $iContentId = $oVersion->getContentVersionId();
        $oContentVersion = KTDocumentContentVersion::get($iContentId);

	    $sPath = $oContentVersion->getStoragePath();
	    $sFullPath = sprintf("%s/%s", $sDocumentRoot, $sPath);
	    if (file_exists($sFullPath)) {
            unlink($sFullPath);
	    }
	    return true;
	}

    function restore($oDocument) {
        // Storage doesn't care if the document is deleted or restored
        return true;
    }
}

?>
