<?php

/**
*
* Class Folder
*
* Represents as folder as the per the folders table in the database
*
* @author Rob Cherry, Jam Warehouse (Pty), Ltd, South Africa
* @date 16 Janurary 2003
* @package lib.foldermanagement
*/

class Folder {
	
	/** folder primary key */
	var $iId;
	/** folder name */
	var $sName;
	/** folder description */
	var $sDescription;
	/** folder parent primary key */
	var $iParentID;
	/** primary key of user who created folder */
	var $iCreatorID;
	/** primary key of document types this folder holds */
	var $iDocumentTypeID;
	/** primary key of unit to which this folder belongs */
	var $iUnitID;
	/** public status of folder */
	var $bIsPublic;
	
	
	/**
	* Folder class constructor
	*
	* @param $sNewName				Folder name
	* @param $sNewDescription		Folder description
	* @param $iNewParentID			Primary key of parent folder
	* @param $iNewCreatorID			Primary key of user who created folder
	* @param $iNewDocumentTypeID	Primary key of document type folder will hold
	* @param $iNewUnitID			Primary key of unit to which folder belongs
	* @param $bNewIsPublic			Folder public status (is the folder public or not?)
	*/
	function Folder($sNewName, $sNewDescription, $iNewParentID, $iNewCreatorID, $iNewDocumentTypeID, $iNewUnitID, $bNewIsPublic = false) {
		//id of -1 means that the object has not yet been stored in the database
		$this->iId = -1;
		$this->sName = $sNewName;
		$this->sDescription = $sNewDescription;
		$this->iParentID = $iNewParentID;
		$this->iCreatorID = $iNewCreatorID;
		$this->iNewDocumentTypeID = $iNewDocumentTypeID;
		$this->iUnitID = $iNewUnitID;
		$this->bIsPublic = $bNewIsPublic;
	}
	
		/**
	* Get the primary key of the current folder object
	*
	* @return integer primary key of folder
	*
	*/
	function getID() {
		return $this->iId;
	}
	
	/** 
	* Get the folder name 
	*
	* @return String folder name
	*/
	function getName() {
		return $this->sName;
	}
	
	/** 
	* Set the folder name 
	*
	* @param $sNewValue		New folder name
	*/
	function setName($sNewValue) {
		$this->sName = $sNewValue;
	}
	
	/**
	* Get the folder description
	*
	* @return String folder description
	*/
	function getDescription() {
		return $this->sDescription;
	}
	
	/**
	* Set the folder description
	*
	* @param $sNewValue		New folder description
	*
	*/
	function setDescription($sNewValue) {
		$this->sDescription = $sNewValue;
	}
	
	/**
	* Get the parent folder primary key
	*
	* @return integer parent folder primary key
	*
	*/
	function getParentID() {
		return $this->iParentID;
	}
	
	/**
	* Set the parent folder primary key
	*
	* @param $iNewValue		New parent folder primary key
	*
	*/
	function setParentID($iNewValue) {
		$this->iParentID = $iNewValue;
	}
	
	/**
	* Get the document type primary key
	*
	* @return integer document type primary key
	*
	*/
	function getDocumentTypeID() {
		return $this->iDocumentTypeID;
	}
	
	/**
	* Set the document type primary key
	*
	* @param $iNewValue		New document type id
	*
	*/
	function setDocumentTypeID($iNewValue) {
		$this->iDocumentTypeID = $iNewValue;
	}
	
	/**
	* Get the unit primary key
	*
	* @return integer primary key of unit to which the folder belongs
	*
	*/
	function getUnitID() {
		return $this->iUnitID;
	}
	
	/**
	* Set the unit prinary key 
	*
	* @param $iNewValue		New primary key of unit to which folder belongs
	*
	*/
	function setUnitID($iNewValue) {
		$this->iUnitID = $iNewValue;
	}
		
	/**
	* Get the folder public status
	*
	* @return boolean true if folder is public, false if folder is not
	*
	*/
	function getIsPublic() {
		return $this->bIsPublic;
	}
	
	/**
	* Set the folder public status
	*
	* @param $bNewValue 	New folder public status
	*
	*/
	function setIsPublic($bNewValue) {
		$this->bIsPublic = $bNewValue;
	}
	
	/**
	* Create the current folder in the database
	*
	* @return boolean true and set $this->iId with new primary key, false otherwise and set $_SESSION["errorMessage"]
	*/
	function create() {
		global $default, $lang_err_database; $lang_err_object_exists;
		//if the object has not already been stored		
		if ($this->iId < 0) {
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO " . $default->owl_folders_table . " (name, description, parent_id, creator_id, document_type_id, unit_id, is_public) " .
						"VALUES ('" . addslashes($this->sName) . "', '" . addslashes($this->sDescription) . "', $this->iParentID, $this->iCreatorID, $this->iDocumentTypeID, $this->iUnitID, " . ($this->bIsPublic ? 1 : 0) . ")");
			if ($result) {
				$this->iId = $sql->insert_id();
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = folders";
		return false;
	}
	
	/**
	* Update the current folder values in the database
	*
	* @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
	*/
	function update() {
		global $default, $lang_err_database, $lang_err_object_key;
		//can only update the object if it has already been stored
		if ($this->iId >= 0) {
			$sql = new Owl_DB();
			$result = $sql->query("UPDATE " . $default->owl_folders_table . " SET " .
							"name = '" . addslashes($this->sName) . "', " .
							"description = '" . addslashes($this->sDescription) . "', " .
							"parent_id = $this->iParentID, " .
							"creator_id = $this->iCreatorID, " .
							"document_type_id = $this->iDocumentTypeID, " .
							"unit_id = $this->iUnitID, " . 
							"is_public = " . ($this->bIsPublic ? 1 : 0) . " " .
						"WHERE id = " . $this->iId);
			if ($result) {
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;			
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;		
	}
	
	
	/**
	* Delete the current object from the database
	*
	* @return boolean true and reset id to -1 on successful delete, false otherwise and set $_SESSION["errorMessage"]
	*/
	function delete() {
		global $default, $lang_err_database, $lang_err_object_key;
		if ($this->iId >= 0) {
			$sql = new Owl_DB();
			$result = $sql->query("DELETE FROM " . $default->owl_folders_table . " WHERE id = " . $this->iId);
			if ($result) {
				$this->iId = -1;
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;
		return false;
		
	}
	
	/**
	* Static function.  
	* Given a folder primary key will create
	* a folder object and populate it with the corresponding
	* database values
	*
	* @param $iFolderID		Primary key of folder to get
	*
	* @return Folder folder object on successful retrieval, false otherwise and set $_SESSION["errorMessage"]
	*/
	function get($iFolderID) {
		global $default,  $lang_err_object_not_exist;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_folders_table . " WHERE id = " . $iFolderID);
		if ($sql->next_record()) {
			$oFolder = & new Folder(stripslashes($sql->f("name")), stripslashes($sql->f("description")), $sql->f("parent_id"), $sql->f("creator_id"), $sql->f("document_type_id"), $sql->f("unit_id"), $sql->f("is_public"));
			return $oFolder;
		} 
 		$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iFolderID . " table = folders";
		return false;
	}
	
	/**
	* Static function.
	* Get the full path for a folder
	* 
	* @param 	Primary key of folder to generate path for	
	*
	* @return String full path of folder
	*/
	function getFolderPath($iFolderID) {
		global $default;		
		//if the folder has a parent
		if (Folder::getParentFolderID($iFolderID) != 0) {			
			$sCurrentPath = Folder::getFolderPath(Folder::getParentFolderID($iFolderID), Folder::getFolderName($iFolderID)) . "/" . $sCurremtPath;
		}
		return $default->owl_FileDir . "/" . Folder::getFolderName($iFolderID);
		
	}
	
	/**
	* Static function
	* Get the primary key of the parent folder
	*
	* @param $iFolderID		Primary key of folder to get parent for
	*
	* @return integer primary key of parent folder
	*/
	function getParentFolderID($iFolderID) {
		global $default;
		$sql = new Owl_DB();
		$sql->query("SELECT parent_id FROM " . $default->owl_folders_table . " WHERE id = " . $iFolderID);
		$sql->next_record();
		return $sql->f("parent_id");
	}
	
	/**
	* Static function
	* Get the document type for a folder
	*
	* @param $iFolderID
	*
	* @return integer document type primary key, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getFolderDocumentType($iFolderID) {
		global $default, $lang_err_database;
		if (Folder::folderExistsID($iFolderID)) {
			$sql = new Owl_DB();
			$sql->query("SELECT document_type_id FROM " . $default->owl_folders_table . " WHERE id = " . $iFolderID);
			if ($sql->next_record()) {
				return $sql->f("document_type_id");
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			
		}
		//error message set by Folder::folderExists
		return false;
	}
	
	/**
	* Static function
	* Checks if a given folder already exists using the folder name
	*
	* @param $sName			Name of folder
	* @param $iParentID		Primary key of parent folder
	*
	* @return	true if the folder exists, false otherwise and set $_SESSION["errorMessage"]
	*/
	function folderExistsName($sName, $iParentID) {
		$sName = addslashes($sName);
		global $default, $lang_err_folder_exist;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_folders_table . " WHERE name = '" . $sName . "' AND parent_id = " . $iParentID);
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_folder_exist . $sName . " parent_id " . $iParentID;
		return false;
	}
	
	/**
	* Checks if a given folder already exists using the folder name
	*
	* @param $iFolderID			Primary key of folder
	*
	* @return	true if the folder exists, false otherwise and set $_SESSION["errorMessage"]
	*/
	function folderExistsID($iFolderID) {
		global $default, $lang_err_folder_exist;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_folders_table . " WHERE id = " . $iFolderID);
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] =$lang_err_folder_exist . "id " . $iFolderID;
		return false;
	}
	
	/**
	* Get the folder name using the primary key
	*
	* @param $iFolderID		Primary key of folder to get name for
	*
	* @return String name on success, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getFolderName($iFolderID) {
		global $default, $lang_err_database;
		$sql = new Owl_DB();
		$sql->query("SELECT name FROM " . $default->owl_folders_table . " WHERE id = " . $iFolderID);
		if ($sql->next_record()) {
			return stripslashes($sql->f("name"));
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
}

?>
