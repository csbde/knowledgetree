<?php
/**
 * $Id$
 *
 * Contains all functions required to upload, alter and
 * delete a physical document.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.documentmanagement 
 */

// used for well-known MIME deterministic techniques
if (!extension_loaded('fileinfo')) {
    @dl('fileinfo.' . PHP_SHLIB_SUFFIX);
}

class PhysicalDocumentManager {
	
	/**
	* Upload and store a new document
	*	
	* @param 	The document object being uploaded
	* @param 	Primary key of folder in which document will be stored
	* @param 	Document description (should be passed through as a form variable)
	* @param	Temporary path of file on server (get from $aFileArray['fFile']['tmp_name'])
	*
	* @return boolean true on successful upload and storage, false otherwise and set $_SESSION["errorMessage"]
	*
	* @todo add URL functionality
	*/
	function uploadPhysicalDocument($oDocument, $iFolderID, $sDescription, $sTmpFilePath) {
		global $lang_fileexists, $lang_err_upload, $lang_err_database;				
		//find the path on the system where the document should be stored
        $sDocumentFileSystemPath = $oDocument->getPath();		
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
			$_SESSION["errorMessage"] = $lang_err_upload;
			return false;
		}
	}
    
    /**
     * Renames a document on the filesystem
     *
     * @param object the document to rename
     * @param string the new document filename
     * @return true on success, false on failure
     */
    function renamePhysicalDocument($oDocument, $sNewFileName) {
        global $default;
      
        // create new and old paths
        $sDocumentFileSystemPath = $oDocument->getPath();
        $sNewDocumentFileSystemPath = Folder::getFolderPath($oDocument->getFolderID()) . "/" . $sNewFileName;
        $default->log->debug("renaming $sDocumentFileSystemPath to $sNewDocumentFileSystemPath");
        // move it
        return rename($sDocumentFileSystemPath, $sNewDocumentFileSystemPath);
    }
    
	/**
	* Stream a document to a client over http
	*
	* @param 	Primary key of document to stream
	*
	* @return int number of bytes read from file on success or false otherwise;
	*
	* @todo investigate possible problem in MSIE 5.5 concerning Content-Disposition header
	*/
	function downloadPhysicalDocument($iDocumentID) {
		//get the document
		$oDocument = & Document::get($iDocumentID);
		//get the path to the document on the server
        $sDocumentFileSystemPath = $oDocument->getPath();
        if (file_exists($sDocumentFileSystemPath)) {
			//set the correct headers
	        Header("Content-Type: " . PhysicalDocumentManager::getMimeTypeName($oDocument->getMimeTypeID()));
			Header("Content-Length: ". $oDocument->getFileSize());
			Header("Content-Disposition: attachment; filename=\"" . $oDocument->getFileName() . "\"");        
	        Header("Pragma: no-store");
	        Header("Expires: 0");
			readfile($sDocumentFileSystemPath);
        } else {
        	return false;
        }
	}
    
    /**
	 * Stream a particular version of a document to a client over http
	 *
	 * @param 	Primary key of document to stream
	 * @param 	Primary key of document to stream
	 * @return int number of bytes read from file on success or false otherwise;
 	 */
	function downloadVersionedPhysicalDocument($iDocumentID, $sVersion) {
		//get the document
		$oDocument = & Document::get($iDocumentID);
		//get the path to the document on the server
        $sDocumentFileSystemPath = $oDocument->getPath() . "-$sVersion";
        if (file_exists($sDocumentFileSystemPath)) {
			//set the correct headers
	        Header("Content-Type: " . PhysicalDocumentManager::getMimeTypeName($oDocument->getMimeTypeID()));
			header("Content-Length: ". $oDocument->getFileSize());
			// prefix the filename presented to the browser to preserve the document extension
			header("Content-Disposition: attachment; filename=" . "$sVersion-" . $oDocument->getFileName());
	        header("Pragma: no-store");
	        header("Expires: 0");
			readfile($sDocumentFileSystemPath);
        } else {
        	return false;
        }
    }
    
	/**
 	 * Move a document to a new folder
	 *
	 * return boolean true on successful move, false otherwhise
	 */
	function moveDocument($sOldDocumentPath, $oDocument, $oFolder) {
		global $default;
		
		// current document path
		$sCurrentPath = $sOldDocumentPath;
		
		// the destination path
		$sDestinationFolderPath = Folder::getFolderPath($oFolder->getID()) . $oDocument->getFileName();

		// find all the previous versions of this document and move them
		// ie. interrogate transaction history for all CHECKIN transactions and retrieve the versions
		// FIXME: refactor array getOldVersionPaths($iDocumentID)??
		
		$sql = $default->db;
		$result = $sql->query("SELECT DISTINCT version FROM $default->document_transactions_table WHERE document_id=" . $oDocument->getID() . " AND transaction_id=" . CHECKOUT);
        if ($result) {
            while ($sql->next_record()) {
            	$sVersion = $sql->f("version");
            	if ($sVersion <> $oDocument->getVersion()) {
					$sSourcePath = $sCurrentPath . "-" . $sVersion;
					$sDestinationPath = $sDestinationFolderPath . "-" . $sVersion;
					// move it to the new folder
					$default->log->info("PhysicalDocumentManager::moveDocument moving $sSourcePath to $sDestinationPath");
					if (!PhysicalDocumentManager::move($sSourcePath, $sDestinationPath)) {
						// FIXME: can't bail now since we don't have transactions- so we doggedly continue deleting and logging errors					
						$default->log->error("PhysicalDocumentManager::moveDocument error moving $sSourcePath to $sDestinationPath; documentID=" . $oDocument->getID() . "; folderID=" . $oFolder->getID());
					}
            	}
            }
        } else {
        	$default->log->error("PhysicalDocumentManager::moveDocument error looking up document versions, id=" . $oDocument->getID());
        }	

		// now move the current version		
		if (PhysicalDocumentManager::move($sCurrentPath, $sDestinationFolderPath)) {
			return true;
		} else {
			$default->log->error("PhysicalDocumentManager::moveDocument couldn't move $sCurrentPath to $sDestinationFolderPath, documentID=" . $oDocument->getID());
			return false;
		}
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
	
	
	/**
	 * Deletes a document- moves it to the Deleted/ folder
	 *
	 * return boolean true on successful move, false otherwhise
	 */
	function delete($oDocument) {
		global $default;
		// current document path
		$sCurrentPath = $oDocument->getPath();
		
		// check if the deleted folder exists and create it if not
		$sDeletedPrefix = $default->documentRoot . "/Deleted";
		if (!file_exists($sDeletedPrefix)) {
            mkdir($sDeletedPrefix, 0755);
        }
		
		// move the file to the deleted folder, prefixed by its document id
		$sDeletedPrefix = $default->documentRoot . "/Deleted/" . $oDocument->getID() . "-" . $oDocument->getFileName();

		// find all the previous versions of this document and move them
		// ie. interrogate transaction history for all CHECKIN transactions and retrieve the versions
		// FIXME: refactor
		$sql = $default->db;
		$result = $sql->query("SELECT DISTINCT version FROM $default->document_transactions_table WHERE document_id=" . $oDocument->getID() . " AND transaction_id=" . CHECKOUT);
        if ($result) {
            while ($sql->next_record()) {
            	$sVersion = $sql->f("version");
            	if ($sVersion <> $oDocument->getVersion()) {
					$sVersionedPath = $sCurrentPath . "-" . $sVersion;
					$sDeletedPath = $sDeletedPrefix . "-" . $sVersion;
					// move it to the deleted folder
					$default->log->info("PhysicalDocumentManager::delete moving $sVersionedPath to $sDeletedPath");
					if (!PhysicalDocumentManager::move($sVersionedPath, $sDeletedPath)) {
						$default->log->error("PhysicalDocumentManager::delete error moving $sVersionedPath to $sDeletedPath; documentID=" . $oDocument->getID());
						// FIXME: can't bail now since we don't have transactions- so we doggedly continue deleting and logging errors
					}
            	}
            }
        } else {
        	$default->log->error("PhysicalDocumentManager::delete error looking up document versions, id=" . $oDocument->getID());
        }	

		// now move the current version		
		if (PhysicalDocumentManager::move($sCurrentPath, $sDeletedPrefix)) {
			return true;
		} else {
			$default->log->error("PhysicalDocumentManager::delete couldn't move $sCurrentPath to $sDeletedPath, documentID=" . $oDocument->getID());
			return false;
		}
	}

	/**
	 * Completely remove a document from the Deleted/ folder
	 *
	 * return boolean true on successful move, false otherwhise
	 */	
	function expunge($oDocument) {
		global $default;
		// deleted document path
		$sDeletedPrefix = $default->documentRoot . "/Deleted/" . $oDocument->getID() . "-" . $oDocument->getFileName();
		
		// find all the previous versions of this document and delete them
		// ie. interrogate transaction history for all CHECKIN transactions and retrieve the versions
		// FIXME: refactor
		$sql = $default->db;
		$result = $sql->query("SELECT DISTINCT version FROM $default->document_transactions_table WHERE document_id=" . $oDocument->getID() . " AND transaction_id=" . CHECKOUT);
        if ($result) {
            while ($sql->next_record()) {
            	$sVersion = $sql->f("version");
            	if ($sVersion <> $oDocument->getVersion()) {
					$sExpungePath = $sDeletedPrefix . "-" . $sVersion;
					// zap it
					$default->log->info("PhysicalDocumentManager::expunge rm'ing $sExpungePath");
					if (file_exists($sExpungePath)) {
						if (!unlink($sExpungePath)) {
							$default->log->error("PhysicalDocumentManager::expunge error deleting $sExpungePath; documentID=" . $oDocument->getID());
							// FIXME: can't bail now since we don't have transactions- so we doggedly continue deleting and logging errors
						}
					} else {
						$default->log->error("PhysicalDocumentManager::expunge can't rm $sExpungePath because it doesn't exist");
					}
            	}
            }
        } else {
        	$default->log->error("PhysicalDocumentManager::expunge error looking up document versions, id=" . $oDocument->getID());
        }	

		if (file_exists($sDeletedPrefix)) {
			// now delete the current version
			if (unlink($sDeletedPrefix)) {
				$default->log->info("PhysicalDocumentManager::expunge  unlinkied $sDeletedPrefix");			
				return true;
			} else {
				$default->log->info("PhysicalDocumentManager::expunge couldn't unlink $sDeletedPrefix");
				if (file_exists($sDeletedPrefix)) {
					return false;
				} else {
					return true;
				}
			}
		} else {
			$default->log->info("PhysicalDocumentManager::expunge can't rm $sDeletedPrefix because it doesn't exist");
			return true;
		}
	}
	
	/**
	 * Restore a document from the Deleted/ folder to the specified folder
	 *
	 * return boolean true on successful move, false otherwhise
	 */	
	function restore($oDocument) {
		global $default;
		
		// deleted document path (includes previous versions)
		$sDeletedPath = $default->documentRoot . "/Deleted/" . $oDocument->getID() . "-" . $oDocument->getFileName();
				
		// build the path to the new folder
		$sRestorePath = Folder::getFolderPath($oDocument->getFolderID()) . "/" . $oDocument->getFileName();
				
		// find all the previous versions of this document and move them
		// ie. interrogate transaction history for all CHECKIN transactions and retrieve the versions
		// FIXME: refactor
		$sql = $default->db;
		$result = $sql->query("SELECT DISTINCT version FROM $default->document_transactions_table WHERE document_id=" . $oDocument->getID() . " AND transaction_id=" . CHECKOUT);
        if ($result) {
            while ($sql->next_record()) {
            	$sVersion = $sql->f("version");
            	if ($sVersion <> $oDocument->getVersion()) {
					$sVersionedDeletedPath = $sDeletedPath . "-" . $sVersion;
					$sVersionedRestorePath = $sRestorePath . "-" . $sVersion;
					// move it to the new folder
					$default->log->info("PhysicalDocumentManager::restore moving $sVersionedDeletedPath to $sVersionedRestorePath");
					if (!PhysicalDocumentManager::move($sVersionedDeletedPath, $sVersionedRestorePath)) {
						$default->log->error("PhysicalDocumentManager::restore error moving $sVersionedDeletedPath to $sVersionedRestorePath; documentID=" . $oDocument->getID());
						// FIXME: can't bail now since we don't have transactions- so we doggedly continue restoring and logging errors
					}
            	}
            }
        } else {
        	$default->log->error("PhysicalDocumentManager::expunge error looking up document versions, id=" . $oDocument->getID());
        }
		
		// now move the current version		
		if (PhysicalDocumentManager::move($sDeletedPath, $sRestorePath)) {
			return true;
		} else {
			$default->log->error("PhysicalDocumentManager::restore couldn't move $sDeletedPath to $sRestorePath, documentID=" . $oDocument->getID());
			return false;
		}		
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
	        Header("Content-Type: " . PhysicalDocumentManager::getMimeTypeName($oDocument->getMimeTypeID()));
			Header("Content-Length: " . $oDocument->getFileSize());
			Header("Content-Disposition: inline; filename=" . $oDocument->getFileName());
	        header("Pragma: no-store");
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

    /**
    * Strip all but the extension from a file. For instance, input of
    * 'foo.tif' would return 'tif'.
    *
    * @param string filename
    * @return string extension for given file, without filename itself
    */
    function stripAllButExtension($sFileName) {
        return strtolower(substr($sFileName, strpos($sFileName, ".")+1, strlen($sFileName) - strpos($sFileName, ".")));
    }

    /**
    * Get the mime type primary key for a specific mime type
    *
    * @param string detected mime type
    * @param string filename
    * @return int mime type primary key if found, else default mime type primary key (text/plain)
    */
    function getMimeTypeID($sMimeType, $sFileName) {
        global $default;
        $sql = $default->db;
        
        if (!$sMimeType) {
          // check by file extension
          $sExtension = PhysicalDocumentManager::stripAllButExtension($sFileName);
          $sql->query("SELECT id FROM " . $default->mimetypes_table . " WHERE LOWER(filetypes) = '$sExtension'");
          if ($sql->next_record()) {
              return $sql->f("id");
          }
        }
        
        // get the mime type id
        if (isset($sMimeType)) {
            $sql->query("SELECT id FROM " . $default->mimetypes_table . " WHERE mimetypes = '$sMimeType'");     
            if ($sql->next_record()) {
                return $sql->f("id");
            }
        }

        //otherwise return the default mime type
        return PhysicalDocumentManager::getDefaultMimeTypeID();
    }

	/**
	* Get the default mime type, which is text/plain
	*
	* @return int default mime type
	*
	*/
	function getDefaultMimeTypeID() {
		global $default;
		$sql = $default->db;		
		$sql->query("SELECT id FROM " . $default->mimetypes_table . " WHERE mimetypes = 'text/plain'");		
		$sql->next_record();
		//get the mime type id
		return $sql->f("id");
	}

	function getMimeTypeName($iMimeTypeID) {
		global $default;
		$sql = $default->db;
		$sql->query("SELECT mimetypes FROM " . $default->mimetypes_table . " WHERE id = " . $iMimeTypeID);
		if ($sql->next_record()) {
			return $sql->f("mimetypes");			
		}
		return "application/octet-stream";
	}

    /**
    * Try well-defined methods for getting the MIME type for a file on disk.
    * First try PECL's Fileinfo library, then try mime_content_type() builtin.
    * If neither are available, returns NULL.
    *
    * @param string file on disk
    * @return string mime time for given filename, or NULL
    */
    function getMimeTypeFromFile($sFileName) {
        if (extension_loaded('fileinfo')) {
            $res = finfo_open(FILEINFO_MIME);
            $sType = finfo_file($res, $sFileName);
        }

        if (!$sType && function_exists('mime_content_type')) {
            $sType = @mime_content_type($sFileName);
        }

        if ($sType) {
            return preg_replace('/;.*$/', '', $sType);
        }

        return NULL;
    }
}

?>
