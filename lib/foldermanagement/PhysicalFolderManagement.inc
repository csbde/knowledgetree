<?php

/**
* 
* Contains static functions for doing physical folder managements
* such as creating/deleting folders
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
*
* @todo finished createFolder() function - find out correct mode
* @package lib.foldermanagement
*/

class PhysicalFolderManagement {
	
	/**
	* Create a physical directory on the file system
	*
	* @param 	Folder path
	* @param 	Folder mode
	*
	*/
	function createFolder($sPath) {
        global $default;
        
		return mkdir($sPath, $default->uMask);
	}
	
	/**
	* Delete a physical folder on the file system
	*
	* @param 	Folder path
	*
	* @return boolean true on successful delete, false otherwise
	*/
	function deleteFolder($sPath) {
		return rmdir($sPath);
	}
	
	function renameFolder($sOldPath, $sNewPath) {		
		return rename($sOldPath, $sNewPath);
	}
	
}

?>
