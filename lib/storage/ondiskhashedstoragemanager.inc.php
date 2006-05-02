<?php
/**
 * $Id$
 *
 * Provides storage for contents of documents on disk, using a hashed
 * folder path and the content version as the file name.
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
 * @version $Revision$
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
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
            unlink($sTmpFilePath);
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
                    return PEAR::raiseError("Could not create directory for storage");
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
    
    function download($oDocument) {
        //get the path to the document on the server
        $oConfig =& KTConfig::getSingleton();
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oDocument));
        if (file_exists($sPath)) {
            //set the correct headers
            header("Content-Type: " .
                    KTMime::getMimeTypeName($oDocument->getMimeTypeID()));
            header("Content-Length: ". $oDocument->getFileSize());
            header("Content-Disposition: attachment; filename=\"" . $oDocument->getFileName() . "\"");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate");

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
                    KTMime::getMimeTypeName($oDocument->getMimeTypeID()));
            header("Content-Length: ".  filesize($sPath));
            // prefix the filename presented to the browser to preserve the document extension
            header('Content-Disposition: attachment; filename="' . "$sVersion-" . $oDocument->getFileName() . '"');
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
                unlink($sOldDocumentPath);
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
        
        // check if the deleted folder exists and create it if not
        $sDeletedPrefix = sprintf("%s/Deleted", $oConfig->get('urls/documentRoot'));
        $sDocumentRoot = $oConfig->get('urls/documentRoot');

        $aVersions = KTDocumentContentVersion::getByDocument($oDocument);
        foreach ($aVersions as $oVersion) {
            $sPath = sprintf("Deleted/%s-%s", $oVersion->getId(), $oVersion->getFileName());
            $sFullPath = sprintf("%s/%s", $sDocumentRoot, $sPath);
            if (file_exists($sFullPath)) {
                unlink($sFullPath);
            }
        }
        return true;
    }
    
    function restore($oDocument) {
        // Storage doesn't care if the document is deleted or restored
        return true;
    }
}

?>
