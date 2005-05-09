<?php
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");
require_once("$default->fileSystemRoot/lib/web/WebDocument.inc");
require_once("$default->fileSystemRoot/lib/links/Link.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DependantDocumentInstance.inc");
require_once("$default->fileSystemRoot/lib/archiving/ArchiveRestorationRequest.inc");
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
	 * Retrieves the web documents that the current user has pending
	 */
	function getPendingWebDocuments(){
	    global $default;
	    $sQuery = "SELECT wd.id FROM web_documents wd " . /*ok*/
	              "INNER JOIN web_sites ws ON wd.web_site_id = ws.id " .
	              "WHERE ws.web_master_id = ? AND wd.status_id = 1";
        $aParams = array($this->iUserID);
	    $aDocumentList = array();
	    $sql = $default->db;
	    if ($sql->query(array($sQuery, $aParams))) {
	        while ($sql->next_record()) {
	            $aDocumentList[] = & WebDocument::get($sql->f("id"));
	        }
	    }
	    return $aDocumentList;
	}
	
	/**
	 * Retrieves details of supporting documents that the current user must create
	 */
	function getArchiveRestorationRequestDocuments() {
		return ArchiveRestorationRequest::getList(array("admin_user_id = ?", $this->iUserID));/*ok*/
	}

	/**
	 * Retrieves the collaboration documents that the current user has pending
	 *
	 * @param integer the user to retrieve pending collaboration documents for
	 */
	function getPendingCollaborationDocuments(){
	    global $default;
	    
	    $sQuery = array("SELECT document_id FROM $default->folders_user_roles_table WHERE active=1 AND user_id = ?", $this->iUserID);/*ok*/
	    $aDocumentList = array();
	    $sql = $default->db;
	    if ($sql->query($sQuery)) {
	        while ($sql->next_record()) {
	            $aDocumentList[] = & Document::get($sql->f("document_id"));
	        }
	    }
	    return $aDocumentList;
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
	 * Retrieves details of supporting documents that the current user must create
	 */
	function getDependantDocuments() {
		global $default;
		$sQuery = array("SELECT id FROM $default->dependant_document_instance_table WHERE user_id = ?", $this->iUserID);/*ok*/
		$aDocumentList = array();
		$sql = $default->db;
		$sql->query($sQuery);
		while ($sql->next_record()) {
			$aDocumentList[] = & DependantDocumentInstance::get($sql->f("id"));
		}
		return $aDocumentList;	
	}	
	

	/**
	 * Retrieves Public Folders
	 */
	function getPublicFolders() {
		global $default;
		$sQuery = array("SELECT id FROM $default->folders_table WHERE is_public = 1");
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
	}

}
