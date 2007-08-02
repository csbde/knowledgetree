<?php
/**
 * $Id$
 *
 * Provides storage for contents of documents on disk, using a hashed
 * folder path and the content version as the file name.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 *
 * The Original Code is: KnowledgeTree Open Source
 *
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 */

require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
require_once(KT_LIB_DIR . '/mime.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/documentcontentversion.inc.php');
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');

class KTOnDiskHashedStorageManager extends KTStorageManager {
    function upload(&$oDocument, $sTmpFilePath) {
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
        if ($this->writeToFile($sTmpFilePath, $sDocumentFileSystemPath)) {
            $end_time = KTUtil::getBenchmarkTime();
            global $default;
            $default->log->info(sprintf("Uploaded %d byte file in %.3f seconds", $file_size, $end_time - $start_time));

            //remove the temporary file
            @unlink($sTmpFilePath);
            if (file_exists($sDocumentFileSystemPath)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function writeToFile($sTmpFilePath, $sDocumentFileSystemPath) {
        // Make it easy to write compressed/encrypted storage

        return copy($sTmpFilePath, $sDocumentFileSystemPath);
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
                $res = @mkdir(sprintf('%s%s', $sDocumentRoot, $path));
                if ($res === false) {
                    return PEAR::raiseError(_kt("Could not create directory for storage"));
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
        //get the path to the document on the server
        $oConfig =& KTConfig::getSingleton();
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oDocument));
        $mimetype = KTMime::getMimeTypeName($oDocument->getMimeTypeID());

        if ($bIsCheckout && $oConfig->get('ui/fakeMimetype' ,false)) {
            $mimetype = 'application/x-download';
            // note this does not work for "image" types in some browsers
            // go web.
        }

        if (file_exists($sPath)) {

            $oUrlEncodedFileName = $oDocument->getFileName( );
            $browser = $_SERVER['HTTP_USER_AGENT'];
            if ( strpos( strtoupper( $browser), 'MSIE') !== false) {
                $oUrlEncodedFileName = htmlentities($oUrlEncodedFileName, ENT_QUOTES, 'UTF-8');
            }
            //set the correct headers
            header("Content-Type: " . $mimetype);
            header("Content-Length: ". $oDocument->getFileSize());
            header("Content-Disposition: attachment; filename=\"" . $oUrlEncodedFileName . "\"");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            // HTTP/1.1
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);

            // HTTP/1.0
            // header("Pragma: no-cache"); // Don't send this header! It breaks IE.

            $oFile = new KTFSFileLike($sPath);
            KTFileLikeUtil::send_contents($oFile);
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
            //set the correct headers
            header("Content-Type: " .
                    KTMime::getMimeTypeName($oContentVersion->getMimeTypeID()));
            header("Content-Length: ".  filesize($sPath));
            // prefix the filename presented to the browser to preserve the document extension
            header('Content-Disposition: attachment; filename="' . "$sVersion-" . $oContentVersion->getFileName() . '"');
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate");
            $oFile = new KTFSFileLike($sPath);
            KTFileLikeUtil::send_contents($oFile);
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
            if (copy($sOldDocumentPath, $sNewDocumentPath)) {
                //delete the old one
                @unlink($sOldDocumentPath);
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

    function restore($oDocument) {
        // Storage doesn't care if the document is deleted or restored
        return true;
    }
}

?>
