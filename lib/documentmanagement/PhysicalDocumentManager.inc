<?php

/**
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
*/


class PhysicalDocumentManager {
	
	/**
	* Upload and store a new document
	*	
	* @param 	Array containing file information.  Must match $_FILES exactly
	* @param 	Primary key of folder in which document will be stored
	* @param 	Document description (should be passed through as a form variable)
	* @param	Temporary path of file on server (get from $aFileArray['upfile']['tmp_name'])
	*
	* @return boolean true on successful upload and storage, false otherwise and set $_SESSION["errorMessage"]
	*
	* @todo add URL functionality
	*/
	
	function uploadPhysicalDocument($aFileArray, $iFolderID, $sDescription, $sTmpFilePath) {
		global $lang_fileexists, $lang_err_upload, $lang_err_database;
		//create the document object
		$oDocument = & PhysicalDocumentManager::createDocumentFromUploadedFile($aFileArray, $iFolderID);
		$oDocument->setDescription($sDescription);		
		//find the path on the system where the document should be stored
		$sDocumentFileSystemPath = Folder::getFolderPath($oDocument->getFolderID()) . "/" . $oDocument->getName();
		var_dump($sDocumentFileSystemPath);
		//copy the file accross
		if (copy($sTmpFilePath, $sDocumentFileSystemPath)) {
			//create the object in the database			
			if ($oDocument->create()) {
				//remove the temporary file
				unlink($sTmpFilePath);
				return true;
			} else {
				//if object creation failed, delete the file on the system
				$_SESSION["errorMessage"] = $lang_err_database;
				unlink($sDocumentFileSystemPath);
				//remove the temporary file as well
				unlink($sTmpFilePath);
				return false;
			}
		} else {
			$_SESSION["errorMessage"] = $lang_err_upload;
			return false;
		}
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
		$sDocumentFileSystemPath = Folder::getFolderPath($oDocument->getFolderID()) . "/" . $oDocument->getName();		
		//set the correct headers
		if (strstr($_SERVER["HTTP_USER_AGENT"], "MSIE")) {		
			Header ("Content-type: application/" . PhysicalDocumentManager::getMimeTypeName($oDocument->getMimeTypeID()));
		} else {
			Header ("Content-type: application/octect-stream");
		}
								
		Header("Content-Length: ". $oDocument->getFileSize());
		Header("Content-Disposition: attachment; filename=" . $oDocument->getName());        
        Header("Pragma: no-cache");
        Header("Expires: 0");
		readfile($sDocumentFileSystemPath);

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
		$sDocumentFileSystemPath = Folder::getFolderPath($oDocument->getFolderID()) . "/" . $oDocument->getName();
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
		$oDocument = & new Document($aFileArray['upfile']['name'], $aFileArray['upfiler']['name'], $aFileArray['upfile']['size'], $_SESSION["userID"], PhysicalDocumentManager::getMimeTypeID($aFileArray['upfile']['type']), $iFolderID);
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
		$sql = new Owl_DB();
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
		$sql = new Owl_DB();		
		$sql->query("SELECT id FROM " . $default->owl_mime_table . " WHERE mimetypes = 'text/plain'");		
		$sql->next_record();
		//get the mime type id
		return $sql->f("id");
	}

	function getMimeTypeName($iMimeTypeID) {
		global $default;
		$sql = new Owl_DB();
		$sql->query("SELECT mimetypes FROM " . $default->owl_mime_table . " WHERE id = " . $iMimeTypeID);
		if ($sql->next_record()) {
			return $sql->f("mimetypes");			
		}
		return "text/plain";
	}
	
}

?>
