<?php
/**
 * $Id$
 *
 * Contains static functions for doing physical folder managements
 * such as creating/deleting folders
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
		return rmdir(rtrim($sPath));
	}
	
	function renameFolder($sOldPath, $sNewPath) {		
		return rename($sOldPath, rtrim($sNewPath));
	}
	
}

?>
