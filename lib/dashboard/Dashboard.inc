<?php
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");
require_once("$default->fileSystemRoot/lib/web/WebDocument.inc");
require_once("$default->fileSystemRoot/lib/links/link.inc");
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
	    $sQuery = "SELECT wd.id FROM web_documents wd " . 
	              "INNER JOIN web_sites ws ON wd.web_site_id = ws.id " .
	              "WHERE ws.web_master_id=" . $this->iUserID . " AND wd.status_id=1";
	    $aDocumentList = array();
	    $sql = $default->db;
	    if ($sql->query($sQuery)) {
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
		return ArchiveRestorationRequest::getList("admin_user_id=" . $this->iUserID);
	}

	/**
	 * Retrieves the collaboration documents that the current user has pending
	 *
	 * @param integer the user to retrieve pending collaboration documents for
	 */
	function getPendingCollaborationDocuments(){
	    global $default;
	    
	    $sQuery = "SELECT document_id FROM $default->folders_user_roles_table WHERE active=1 AND user_id=" . $this->iUserID;
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
		return Document::getList("checked_out_user_id=" . $this->iUserID);
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
		return Link::getList("ORDER BY rank");
	}
		
	/**
	 * Retrieves details of supporting documents that the current user must create
	 */
	function getDependantDocuments() {
		global $default;
		$sQuery = "SELECT id FROM $default->dependant_document_instance_table WHERE user_id = " . $this->iUserID;
		$aDocumentList = array();
		$sql = $default->db;
		$sql->query($sQuery);
		while ($sql->next_record()) {
			$aDocumentList[] = & DependantDocumentInstance::get($sql->f("id"));
		}
		return $aDocumentList;	
	}	
}