<?php
require_once(KT_LIB_DIR . "/subscriptions/SubscriptionManager.inc");
require_once(KT_LIB_DIR . "/links/Link.inc");
require_once(KT_LIB_DIR . "/archiving/ArchiveRestorationRequest.inc");
/**
 * $Id$
 *
 * Contains dashboard helper functions.
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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.dashboard 
 */
class Dashboard {

	/**
	 * The user id of the user viewing the dashboard
	 */
	var $iUserID;
	
	/**
	 * Constructs a new instance of the Dashboard
	 * @param integer the user id of the current user
	 */
	function Dashboard($iUserID){
		$this->iUserID = $iUserID;
	}
	
	/**
	 * Retrieves details of supporting documents that the current user must create
	 */
	function getArchiveRestorationRequestDocuments() {
		return ArchiveRestorationRequest::getList(array("admin_user_id = ?", $this->iUserID));/*ok*/
	}

	/**
	 * Retrieve checked out documents for this user
	 *
	 * @return array of documents
	 */                         
	function getCheckedOutDocuments(){
		return Document::getList(array("checked_out_user_id = ?", $this->iUserID));/*ok*/
	}

	/**
	 * Retrieve subscription alerts for this user.
	 *
	 * @return array of subscription alerts
	 */
	function getSubscriptionAlerts(){
		return SubscriptionManager::listSubscriptionAlerts($this->iUserID);
	}

	/**
	 * Retrieve quicklinks
	 *
	 * @return array of link objects
	 */
	function getQuickLinks(){
		return Link::getList("ORDER BY rank");/*ok*/
	}
		
	/**
	 * Retrieves Public Folders
	 */
	function getPublicFolders() {
		global $default;
		$sQuery = array("SELECT id FROM $default->folders_table WHERE is_public = ?", array(true));
		$aFolderList = array();
		$sql = $default->db;
		$sql->query($sQuery);
		while ($sql->next_record()) {
			$aFolderList[] = & Folder::get($sql->f("id"));
		}
		return $aFolderList;
	}
	
	/**
	 * Retrieves Browseable Folders for this user
	 */
	function getBrowseableFolders() {
        return;

        /*
		global $default;
		$sQuery = array("SELECT DISTINCT F.id as folderid, F.parent_id as parentfolderid " .
				"FROM $default->folders_table F, $default->groups_folders_table GFL, $default->users_groups_table UGL " .
				"WHERE UGL.user_id=? AND UGL.group_id=GFL.group_id AND GFL.folder_id = F.id AND " .
				"F.id=F.permission_folder_id AND F.id \!= 1 ORDER BY F.id", $this->iUserID);
		$aBrowseableList = array();
		$aShowedFolderList = array();
		$sql = $default->db;
		$sql->query($sQuery);
		while ($sql->next_record()) {
			
			if (!$aShowedFolderList[$sql->f("parentfolderid")]) {	
				$aBrowseableList[] = & Folder::get($sql->f("folderid"));
			}
			$aShowedFolderList[$sql->f("folderid")] = 1;	// check the showed folder
		}
		return $aBrowseableList;		
        */
	}

}
