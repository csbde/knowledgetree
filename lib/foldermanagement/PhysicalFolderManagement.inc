<?php

/**
* @package lib.foldermanagement
* 
* Contains static functions for doing physical folder managements
* such as creating/deleting folders
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
*
* @todo finished createFolder() function - find out correct mode
*/

class PhysicalFolderManagement {
	
	/**
	* Create a physical directory on the file system
	*
	* @param 	Folder path
	* @param 	Folder mode
	*
	*/
	function createFolder($sPath, $iMode) {
		
		
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
	
}

?>
