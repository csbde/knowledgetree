<?php
/**
 * $Id$
 *
 * Provides storage for contents of documents on disk, using the same
 * path on-disk as in the repository.
 *
 * WARNING:
 * 
 * This storage manager is _not_ transaction-safe, as on-disk paths need
 * to update when the repository position changes, and this operation
 * and the repository change in combination can't be atomic, even if
 * they individually are.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
require_once(KT_LIB_DIR . '/documentmanagement/PhysicalDocumentManager.inc');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/documentcontentversion.inc.php');

// used for well-known MIME deterministic techniques
if (!extension_loaded('fileinfo')) {
    @dl('fileinfo.' . PHP_SHLIB_SUFFIX);
}

class KTOnDiskPathStorageManager extends KTStorageManager {
    function upload(&$oDocument, $sTmpFilePath) {
        $oConfig =& KTConfig::getSingleton();
        $sStoragePath = $this->generateStoragePath($oDocument);
        $this->setPath($oDocument, $sStoragePath);
        $oDocument->setFileSize(filesize($sTmpFilePath));
        $sDocumentFileSystemPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oDocument));
        //copy the file accross
        if (copy($sTmpFilePath, $sDocumentFileSystemPath)) {
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

    function getPath(&$oDocument) {
        return $oDocument->getStoragePath();
    }

    function setPath(&$oDocument, $sNewPath) {
        $oDocument->setStoragePath($sNewPath);
    }

    function generateStoragePath(&$oDocument) {
        $sStoragePath = sprintf("%s/%s-%s", KTDocumentCore::_generateFolderPath($oDocument->getFolderID()), $oDocument->getContentVersionId(), $oDocument->getFileName());
        return $sStoragePath;
    }

    function temporaryFile(&$oDocument) {
        $oConfig =& KTConfig::getSingleton();
        return sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oDocument));
    }

    function freeTemporaryFile($sPath) {
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

            readfile($sPath);
        } else {
            return false;
        }
    }

    function createFolder($oFolder) {
        $oConfig =& KTConfig::getSingleton();
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $oFolder->generateFolderPath($oFolder->getID()));
        if (file_exists($sPath)) {
            return PEAR::raiseError("Path already exists");
        }
        $res = @mkdir($sPath, 0755);
        if ($res === false) {
            return PEAR::raiseError("Couldn't create folder");
        }
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
            readfile($sPath);
        } else {
            return false;
        }
    }
    
	/**
 	 * Move a document to a new folder
     *
     * By the time we are called, the document believes it is in the new
     * location in terms of its folder_id and paths.  Just in case, we
     * avoid using generateStoragePath and rely on the folder objects
     * for our paths.
     *
     * We have to use the folders for our source and destination paths,
     * and then set storage_path.
	 *
	 * return boolean true on successful move, false otherwhise
	 */
	function moveDocument(&$oDocument, $oSourceFolder, $oDestinationFolder) {
        $oConfig =& KTConfig::getSingleton();
        $aContentVersions = KTDocumentContentVersion::getByDocument($oDocument);
        $sDocumentRoot = $oConfig->get('urls/documentRoot');

        foreach ($aContentVersions as $oVersion) {
            $sOldPath = sprintf("%s/%s-%s", KTDocumentCore::_generateFolderPath($oSourceFolder->getID()), $oVersion->getId(), $oVersion->getFileName());
            $sNewPath = sprintf("%s/%s-%s", KTDocumentCore::_generateFolderPath($oDestinationFolder->getID()), $oVersion->getId(), $oVersion->getFileName());
            $sFullOldPath = sprintf("%s/%s", $sDocumentRoot, $sOldPath);
            $sFullNewPath = sprintf("%s/%s", $sDocumentRoot, $sNewPath);
            $res = KTUtil::moveFile($sFullOldPath, $sFullNewPath);
            $oVersion->setStoragePath($sNewPath);
            $oVersion->update();
        }
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
			//copy the file	to the new destination
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
        $table = "document_content_version";
        $sQuery = "UPDATE $table SET storage_path = CONCAT(?, SUBSTRING(storage_path FROM ?)) WHERE storage_path LIKE ?";
        $aParams = array(
            sprintf("%s/%s", $oDestFolder->getFullPath(), $oDestFolder->getName()),
            strlen($oFolder->getFullPath()) + 1,
            sprintf("%s/%s%%", $oFolder->getFullPath(), $oFolder->getName()),
        );
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }
        
        $oConfig =& KTConfig::getSingleton();
        $sSrc = sprintf("%s/%s/%s",
            $oConfig->get('urls/documentRoot'),
            $oFolder->getFullPath(),
            $oFolder->getName()
        );
        $sDst = sprintf("%s/%s/%s/%s",
            $oConfig->get('urls/documentRoot'),
            $oDestFolder->getFullPath(),
            $oDestFolder->getName(),
            $oFolder->getName()
        );
        return KTUtil::moveDirectory($sSrc, $sDst);
    }
	
	
	/**
	 * Deletes a document- moves it to the Deleted/ folder
	 *
	 * return boolean true on successful move, false otherwhise
	 */
	function delete($oDocument) {
        $oConfig =& KTConfig::getSingleton();
		$sCurrentPath = $this->getPath($oDocument);
		
		// check if the deleted folder exists and create it if not
        $sDeletedPrefix = sprintf("%s/Deleted", $oConfig->get('urls/documentRoot'));
		if (!file_exists($sDeletedPrefix)) {
            mkdir($sDeletedPrefix, 0755);
        }

        $sDocumentRoot = $oConfig->get('urls/documentRoot');

        $aVersions = KTDocumentContentVersion::getByDocument($oDocument);
        foreach ($aVersions as $oVersion) {
            $sOldPath = $oVersion->getStoragePath();
            $sNewPath = sprintf("Deleted/%s-%s", $oVersion->getId(), $oVersion->getFileName());
            $sFullOldPath = sprintf("%s/%s", $sDocumentRoot, $sOldPath);
            $sFullNewPath = sprintf("%s/%s", $sDocumentRoot, $sNewPath);
            KTUtil::moveFile($sFullOldPath, $sFullNewPath);
        }
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
	
	/**
	 * Restore a document from the Deleted/ folder to the specified folder
	 *
	 * return boolean true on successful move, false otherwhise
	 */	
	function restore($oDocument) {
        $oConfig =& KTConfig::getSingleton();
		$sCurrentPath = $this->getPath($oDocument);
		
		// check if the deleted folder exists and create it if not
        $sDeletedPrefix = sprintf("%s/Deleted", $oConfig->get('urls/documentRoot'));
        $sDocumentRoot = $oConfig->get('urls/documentRoot');

        $aVersions = KTDocumentContentVersion::getByDocument($oDocument);
        foreach ($aVersions as $oVersion) {
            $sNewPath = $oVersion->getStoragePath();
            $sOldPath = sprintf("Deleted/%s-%s", $oVersion->getId(), $oVersion->getFileName());
            $sFullNewPath = sprintf("%s/%s", $sDocumentRoot, $sNewPath);
            $sFullOldPath = sprintf("%s/%s", $sDocumentRoot, $sOldPath);
            KTUtil::moveFile($sFullOldPath, $sFullNewPath);
        }
        return true;
	}
	
	
	/**
	* View a document using an inline viewer
	*
	* @param 	Primary key of document to view
	*
	* @return int number of bytes read from file on success or false otherwise;
	*
	* @todo investigate possible problem in MSIE 5.5 concerning Content-Disposition header
	*/
	function inlineViewPhysicalDocument($iDocumentID) {
            //get the document
            $oDocument = & Document::get($iDocumentID);		
            //get the path to the document on the server
            $sDocumentFileSystemPath = $oDocument->getPath();
            if (file_exists($sDocumentFileSystemPath)) {
                header("Content-Type: application/octet-stream");
                header("Content-Length: ". $oDocument->getFileSize());
                // prefix the filename presented to the browser to preserve the document extension
                header('Content-Disposition: inline; filename="' . $oDocument->getFileName() . '"');
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Cache-Control: must-revalidate");
                header("Content-Location: ".$oDocument->getFileName());
                return readfile($sDocumentFileSystemPath);
            } else {
                return false;
            }
	}
	
	/**
	* Get the uploaded file information and place it into a document object
	*
	* @param	Array containing uploaded file information (use $aFileArray)
	* par		Primary key of folder into which document will be placed
	*
	* @return Document Document object containing uploaded file information
	*/
	function & createDocumentFromUploadedFile($aFileArray, $iFolderID) {
		//get the uploaded document information and put it into a document object		
		$oDocument = & new Document($aFileArray['name'], $aFileArray['name'], $aFileArray['size'], $_SESSION["userID"], PhysicalDocumentManager::getMimeTypeID($aFileArray['type'], $aFileArray['name']), $iFolderID);
		return $oDocument;	
	}
}

?>
