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
	
	function uploadDocument($aFileArray, $iFolderID, $sDescription, $sTmpFilePath) {
		global $lang_fileexists, $lang_err_upload, $lang_err_database;
		//create the document object
		$oDocument = & PhysicalDocumentManager::createDocumentFromUploadedFile($aFileArray, $iFolderID);
		$oDocument->setDescription($sDescription);		
		//find the path on the system where the document should be stored
		$sDocumentFileSystemPath = FolderLib::getFolderPath($oDocument->getFolderID()) . "/" . $oDocument->getName();
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
				return false;
			}
		} else {
			$_SESSION["errorMessage"] = $lang_err_upload;
			return false;
		}
		
		//check if the user has folder write permissions
		/*if ($this->hasFolderWritePermissions($iFolderID) || $this->hasWriteRoleForFolder($iFolderID) || $this->isInGroup("System Administrators")) {
			//if the user is within his quota after uploading the file
			if (isWithinQuote($aUserDocument["size"])) {
				$sNewPath = $this->generatePath($iFolderID);
				//if the file already exists, return false and display an error
				if(file_exists($sNewPath . $aUserDocument["name"])) {
					$_SESSION["errorMessage"] = $lang_fileexists
					return false;
				}
				copy($upfile["tmp_name"], $sNewPath);
				unlink($upfile["tmp_name"]);
				if(!file_exists($sNewPath)) { 
					if ($default->debug == true) {
                          $_SESSION["errorMessage"] = $lang_err_upload . "," .$sNewPath;
						  return false;
					} else {
                          $_SESSION["errorMessage"] = $lang_err_upload;
						  return false;					
                	}				
				}
			}
		}*/
		
	}
	
	/**
	* Generate the path for the current folder by recursing up the tree to its parents
	*
	* @return path
	*/
	function generatePath($iFolderID) {
		return "";
		
	}
	
	/**
	* Checks whether the user will be within his/her current quota if a new file is uploaded
	* 
	* @param $iNewFileSize		Size of new file
	*
	* @return boolean true if the user is within quota, false otherwise and sets $_SESSION["errorMessage"]
	*/
	
	function isWithinQuota($iNewFileSize) {
		return true;
	}
	
	/**
	* Generates a path for a folder
	*
	* @param $sFolderName	Name of folder to generate path for
	*
	* @returns generated path
	*/
	
	function generatePath($sFolderName) {
		
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
		/*if ($aFileArray[$varname]) {
				return $aFileArray[$varname];
		}
		if ($HTTP_POSTaFileArray[$varname]) { 
			return $HTTP_POSTaFileArray[$varname];
		}
		$tmp = "$varname_name"; 
		global $$tmp; 
		$retfile['name'] = $$tmp;
		$tmp = "$varname_type"; 
		global $$tmp; 
		$retfile['type'] = $$tmp;
		$tmp = "$varname_size"; 
		global $$tmp; 
		$retfile['size'] = $$tmp;
		$tmp = "$varname_error"; 
		global $$tmp; 
		$retfile['error'] = $$tmp;
		$tmp = "$varname_tmp_name"; 
		global $$tmp; 
		$retfile['tmp_name'] = $$tmp;
		
		return $retfile;*/
	}
	
	/**
	* Get the mime type primary key for a specific mime type
	*
	* @param mime type
	*
	* @return integer mime type primary key if found, else default mime type primary key (text/plain)
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
	
	

	
}

?>
