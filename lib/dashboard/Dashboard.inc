<?php

require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");
require_once("$default->fileSystemRoot/lib/web/WebDocument.inc");
require_once("$default->fileSystemRoot/lib/links/link.inc");

/**
 * $Id$
 * 
 * Contains dashboard helper functions
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
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
	 * Retrieves the collaboration documents that the current user has pending
	 *
	 * @param integer the user to retrieve pending collaboration documents for
	 */
	function getPendingCollaborationDocuments(){
	    global $default;
	    
	    $sQuery = "SELECT document_id FROM $default->owl_folders_user_roles_table WHERE active=1 AND user_id=" . $this->iUserID;
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
	 * Retrieves the web documents that the current user has pending
	 *
	 * @param integer the user to retrieve pending web documents for
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
}