<?php
/**
 * $Id$
 *
 * Contains static functions for doing physical folder managements
 * such as creating/deleting folders
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
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
        // check if a folder with this name exists before creating it        
        if (!file_exists($sPath)) {
			return mkdir(rtrim($sPath), 0755);
        } else {
        	return true;
        }
	}
	
	/**
	* Delete a physical folder on the file system
	*
	* @param 	Folder path
	*
	* @return boolean true on successful delete, false otherwise
	*/
	function deleteFolder($sPath) {
		rmdir(rtrim($sPath));
		if (file_exists($sPath)) {
			return false;
		} else {
			return true;
		}
	}
	
	function renameFolder($sOldPath, $sNewPath) {		
		return rename($sOldPath, rtrim($sNewPath));
	}
	
}

?>
