<?php

/**
*
* Class PhysicalDocumentManager.inc
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
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
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
		if (strstr($_SERVER["HTTP_USER_AGENT"], "MSIE")) {
			Header ("Content-type: application/" . PhysicalDocumentManager::getMimeTypeName($oDocument->getMimeTypeID()));
		} else {
			Header ("Content-type: application/octect-stream");
		}
								
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
		if (strstr($_SERVER["HTTP_USER_AGENT"], "MSIE")) {
			header ("Content-type: application/" . PhysicalDocumentManager::getMimeTypeName($oDocument->getMimeTypeID()));
		} else {
			header ("Content-type: application/octect-stream");
		}
								
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
		$total=$url . $files[$i];		
		
		if (strstr($_SERVER["HTTP_USER_AGENT"], "MSIE")) {		
			Header ("Content-type: application/" . PhysicalDocumentManager::getMimeTypeName($oDocument->getMimeTypeID()));
		} else {
			Header ("Content-type: application/octect-stream");
		}					
		Header("Content-Length: " . $oDocument->getFileSize() * 1024);
		Header("Content-Disposition: inline; filename=" . $oDocument->getName());
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
		$oDocument = & new Document($aFileArray['name'], $aFileArray['name'], $aFileArray['size'], $_SESSION["userID"], PhysicalDocumentManager::getMimeTypeID($aFileArray['type']), $iFolderID);
		return $oDocument;	
	}
	
	/**
	* Get the mime type primary key for a specific mime type
	*
	* @param mime type
	*
	* @return int mime type primary key if found, else default mime type primary key (text/plain)
	*/
	function getMimeTypeID($sName) {
		global $default;
		$sql = $default->db;
		//get the mime type		
		if (isset($sName)) {
			$sql->query("SELECT id FROM " . $default->owl_mime_table . " WHERE mimetypes = '$sName'");		
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
		return "text/plain";
	}
	
}

?>
