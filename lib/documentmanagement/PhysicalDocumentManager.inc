<?php
/**
 * $Id$
 *
 * Contains all functions required to upload, alter and
 * delete a physical document.
 *
 * Form variables that will be required:
 *	o When calling uploadDocument:
 *		o $fDescription - description of uploaded file
 *		o $fFolderID - primary key of folder into which file will be placed
 * 	o When calling deleteDocument:
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @version $Revision$
 * @date 13 January 2003
 * @package lib.documentmanagement
 */

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
			return true;		
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
		//set the correct headers
        Header("Content-Type: " . PhysicalDocumentManager::getMimeTypeName($oDocument->getMimeTypeID()));
		Header("Content-Length: ". $oDocument->getFileSize());
		Header("Content-Disposition: attachment; filename=" . $oDocument->getFileName());        
        Header("Pragma: no-store");
        Header("Expires: 0");
		readfile($sDocumentFileSystemPath);
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
		//set the correct headers
        Header("Content-Type: " . PhysicalDocumentManager::getMimeTypeName($oDocument->getMimeTypeID()));
		header("Content-Length: ". $oDocument->getFileSize());
		header("Content-Disposition: attachment; filename=" . "$sVersion-" . $oDocument->getFileName());
        header("Pragma: no-store");
        header("Expires: 0");
		readfile($sDocumentFileSystemPath);
    }
    
	/**
	* Move a document from a source to a destination
	*
	* return boolean true on successful move, false otherwhise
	*
	*/
	function move($sOldDocumentPath, $sNewDocumentPath) {
		global $lang_fileexists, $lang_err_upload, $lang_err_database;		 
		//copy the file	to the new destination
		if (copy($sOldDocumentPath, $sNewDocumentPath)) {
			//delete the old one
			unlink($sOldDocumentPath);
			return true;
		} else {
			$_SESSION["errorMessage"] = $lang_err_upload;
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
		// current document path (includes old versions)
		$sCurrentPath = $oDocument->getPath();
		
		// check if the deleted folder exists and create it if not
		$sDeletedPrefix = $default->documentRoot . "/Deleted";
		if (!file_exists($sDeletedPrefix)) {
            mkdir($sDeletedPrefix, 0755);
        }
		
		// move the file to the deleted folder, prefixed by its document id
		$sDeletedPrefix = $default->documentRoot . "/Deleted/" . $oDocument->getID() . "-" . $oDocument->getFileName();

		// find all the previous versions of this document and rename them
		// ie. interrogate transaction history for all CHECKIN transactions and retrieve the versions		
		$aDocumentTransactions = DocumentTransaction::getList("document_id=" . $oDocument->getID() . " AND transaction_id=" . CHECKOUT);
		for ($i=0; $i<count($aDocumentTransactions); $i++) {
			$sVersionedPath = $sCurrentPath . "-" . $aDocumentTransactions[$i]->getVersion();
			$sDeletedPath = $sDeletedPrefix . "-" . $aDocumentTransactions[$i]->getVersion();
			// move it to the deleted folder
			$default->log->info("delete: moving $sVersionedPath to $sDeletedPath");
			if (!PhysicalDocumentManager::move($sVersionedPath, $sDeletedPath)) {
				$default->log->error("PhysicalDocumentManager::delete error moving $sVersionedPath to $sDeletedPath; documentID=" . $oDocument->getID());
				// FIXME: can't bail now since we don't have transactions- so we doggedly continue deleting and logging errors
			}
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
		// deleted document path (includes previous versions)
		$sDeletedPath = $default->documentRoot . "/Deleted/" . $oDocument->getID() . "-" . $oDocument->getFileName() . "*";
		// zap 'em
		// FIXME: same problem as delete, can't use wildcards, must step through checkin transactions
	}
	
	/**
	 * Completely remove a document from the Deleted/ folder to the specified folder
	 *
	 * return boolean true on successful move, false otherwhise
	 */	
	function restore($oDocument) {
		// deleted document path (includes previous versions)
		$sDeletedPrefix = $default->documentRoot . "/Deleted/" . $oDocument->getID() . "-" . $oDocument->getFileName();		
		// move the document to the folder
		$sNewDocumentFileSystemPath = Folder::getFolderPath($oDocument->getFolderID()) . "/" . $oDocument->getFileName();
		// FIXME: same problem as delete, can't use wildcards, must step through checkin transactions
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
        Header("Content-Type: " . PhysicalDocumentManager::getMimeTypeName($oDocument->getMimeTypeID()));
		Header("Content-Length: " . $oDocument->getFileSize());
		Header("Content-Disposition: inline; filename=" . $oDocument->getFileName());
        header("Pragma: no-store");
		return readfile($sDocumentFileSystemPath);
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
		$oDocument = & new Document(stripslashes($aFileArray['name']), stripslashes($aFileArray['name']), $aFileArray['size'], $_SESSION["userID"], PhysicalDocumentManager::getMimeTypeID($aFileArray['type'], $aFileArray['name']), $iFolderID);
		return $oDocument;	
	}
	
	/**
	* Get the mime type primary key for a specific mime type
	*
	* @param mime type
	*
	* @return int mime type primary key if found, else default mime type primary key (text/plain)
	*/
	function getMimeTypeID($sMimeType, $sFileName) {
		global $default;
		$sql = $default->db;
        
        // check by file extension
        $sExtension = strtolower(substr($sFileName, strpos($sFileName, ".")+1, strlen($sFileName) - strpos($sFileName, ".")));
        $sql->query("SELECT id FROM " . $default->owl_mime_table . " WHERE LOWER(filetypes) = '$sExtension'");
        if ($sql->next_record()) {
            return $sql->f("id");
        }
        
		//get the mime type	
		if (isset($sMimeType)) {
			$sql->query("SELECT id FROM " . $default->owl_mime_table . " WHERE mimetypes = '$sMimeType'");		
			if ($sql->next_record()) {
				//get the mime type id
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
		$sql->query("SELECT id FROM " . $default->owl_mime_table . " WHERE mimetypes = 'text/plain'");		
		$sql->next_record();
		//get the mime type id
		return $sql->f("id");
	}

	function getMimeTypeName($iMimeTypeID) {
		global $default;
		$sql = $default->db;
		$sql->query("SELECT mimetypes FROM " . $default->owl_mime_table . " WHERE id = " . $iMimeTypeID);
		if ($sql->next_record()) {
			return $sql->f("mimetypes");			
		}
		return "application/octet-stream";
	}
	
}

?>
