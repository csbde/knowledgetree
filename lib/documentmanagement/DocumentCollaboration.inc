<?php
/**
 * $Id$
 *
 * Static functions dealing with the document approval process.
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
 * @package lib.documentmanagement 
 */
class DocumentCollaboration {
	
	/**
	* Checks if the collaboration process for the document
	* is started yet
	* 
	*/
	function documentCollaborationStarted($iDocumentID) {
		global $default;
		$sql = $default->db;
        $sQuery = "SELECT id FROM $default->folders_user_roles_table WHERE document_id = ? AND (active = 1 OR done = 1)";/*ok*/
        $aParams = array($iDocumentID);
		$sql->query(array($sQuery, $aParams));
		if ($sql->next_record()) {
			return true;
		}
		return false;
	}
    
    function documentCollaborationDone($iDocumentID) {
		global $default;
		$sql = $default->db;
        $sQuery = "SELECT id FROM $default->folders_user_roles_table WHERE document_id = ? AND done = 0";/*ok*/
        $aParams = array($iDocumentID);
		$sql->query(array($sQuery, $aParams));
		if ($sql->num_rows() > 0) {
			return false;
		} else {
		    return true;
        }
    }
	
	/**
	* Checks if the current user has an active role in the document
	* collaboration process
	* 
	*/
	function userIsPerformingCurrentCollaborationStep($iDocumentID) {
		global $default;
		$sql = $default->db;
        $sQuery = "SELECT id FROM $default->folders_user_roles_table WHERE document_id = ? AND active = 1 AND user_id = ?";/*ok*/
        $aParams = array($iDocumentID, $_SESSION["userID"]);
		$sql->query(array($sQuery, $aParams));
		if ($sql->next_record()) {
			return true;
		}
		return false;
	}
	
	
	/**
	* Reset the entire document collaboration process by setting all roles
	* to inactive and undone
	* 
	*/
	function resetDocumentCollaborationSteps($iDocumentID) {
		global $default;
        $aFieldValues = array(
            'active' => 0,
            'done' => 0,
        );
        $aWhereFieldValues = array(
            'document_id' => $iDocumentID,
        );
        $res =& DBUtil::whereUpdate($default->folders_user_roles_table, $aFieldValues, $aWhereFieldValues);

		if (PEAR::isError($res)) {
            return false;
		}
        return true;
	}
	
	/**
	* Checks if a there are any more steps to be be performed in
	* the current document collaboration process
	* 
	*/
	function isLastStepInCollaborationProcess($iDocumentID) {
		global $default;
		$sql = $default->db;
        $sQuery = "SELECT id FROM $default->folders_user_roles_table WHERE document_id = $iDocumentID AND done = 0";/*ok*/
		$sql->query($sQuery);
        $default->log->info("lastCollabStep:$sQuery");
        if ($sql->num_rows() > 1) {
            return false;
        } else {
            return true;
        }
	}
	
    /**
     * Returns the userID of the last user in the collaboration process
     */
    function getLastCollaboratorID($iDocumentID) {
        
		global $default;
		$sql = $default->db;
        // returns all users, the sequence of their collaboration and the time of completion
        $sQuery = "SELECT FURL.user_id, FURL.datetime, GFAL.precedence FROM $default->folders_user_roles_table FURL " ./*ok*/
                  "INNER JOIN $default->groups_folders_approval_table GFAL ON FURL.group_folder_approval_id = GFAL.id " .
                  "WHERE FURL.document_id = ? " .
                  "ORDER BY GFAL.precedence";
        $aParams = array($iDocumentID);
		$sql->query(array($sQuery, $aParams));
        $iPrecedence = -1;
        $iDateTime = 0;
        $iUserID = -1;
        while ($sql->next_record()) {
            if ($sql->f("precedence") >= $iPrecedence) {
                $iPrecedence = $sql->f("precedence");
                $iDateCompleted = strtotime($sql->f("datetime"));
                if ($iDateCompleted > $iDateTime) {
                    $iDateTime = $iDateCompleted;
                    $iUserID = $sql->f("user_id");
                }
            }
        }
        return ($iUserID == -1) ? false : $iUserID;
    }
    
	/**
	* Begins the next step(s) in the document
	* collaboration process
	* 
	*/
	function beginNextStepInCollaborationProcess($iDocumentID, $iUserID) {
		global $default;
		$sql = $default->db;
		//get the current step
		//if the user is assigned to two or more roles, make sure we get the current
		//one by ordering by precedence
        $sQuery = "SELECT FURL.id AS id, GFAT.precedence " ./*ok*/
					"FROM $default->groups_folders_approval_table AS GFAT " . 
					"INNER JOIN $default->folders_user_roles_table AS FURL ON GFAT.id = FURL.group_folder_approval_id " .
					"WHERE document_id = ? AND FURL.user_id = ? AND done=0 " . 
					"ORDER BY precedence ASC";
        $aParams = array($iDocumentID, $_SESSION["userID"]);
		$sql->query(array($sQuery, $aParams));
		if ($sql->next_record()) {
			//set it as done
			$oFolderUserRole = FolderUserRole::get($sql->f("id"));
			$oFolderUserRole->setActive(false);
			$oFolderUserRole->setDone(true);
			$oFolderUserRole->setDateTime(getCurrentDateTime());
			$oFolderUserRole->update();
			//get it's sequence number
			$iCurrentSequenceNumber =  $sql->f("precedence");
            $sQuery = "SELECT MIN(precedence) AS precedence " . /*ok*/
						"FROM $default->groups_folders_approval_table AS GFAT " . 
						"INNER JOIN $default->folders_user_roles_table AS FURL ON GFAT.id = FURL.group_folder_approval_id " . 
						"WHERE document_id = ? AND done = 0";
            $aParams = array($iDocumentID);
            $sql->query(array($sQuery, $aParams));
			if ($sql->next_record()) {
				if ($sql->f("precedence") != $iCurrentSequenceNumber) {
					//if there are no concurrent steps outstanding
					$iNextSequenceNumber = $sql->f("precedence");
					$sQuery = "SELECT FURL.id " ./*ok*/
								"FROM $default->groups_folders_approval_table AS GFAT " . 
								"INNER JOIN $default->folders_user_roles_table AS FURL ON GFAT.id = FURL.group_folder_approval_id " . 
								"WHERE document_id = ? AND precedence = ?";
                    $aParams = array($iDocumentID, $iNextSequenceNumber);
                    $sql->query(array($sQuery, $aParams));
					while ($sql->next_record()) {
						$oFolderUserRole = FolderUserRole::get($sql->f("id"));
						$oFolderUserRole->setActive(true);
						$oFolderUserRole->update();
						$oFolderCollaboration = FolderCollaboration::get($oFolderUserRole->getGroupFolderApprovalID());
						//get the role the user must perform
						$oRole = Role::get($oFolderCollaboration->getRoleID());
						//get the user to email
						$oUser = User::get($oFolderUserRole->getUserID());
						if ($oUser->getEmailNotification()) {
                            $oDocument = & Document::get($iDocumentID);
							$sBody = $oUser->getName() . ", your role of '" . $oRole->getName() . "' in the document, '" . $oDocument->getName() . "' collaboration process is now active. " .
                                                    "Click " . generateLink("/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php", "fDocumentID=$iDocumentID", "here") . " to access " .
													"the document";
					
							$oEmail = & new Email();
							$oEmail->send($oUser->getEmail(), "Document collaboration role active", $sBody);
						}
						DocumentCollaboration::createDependantDocuments($oFolderUserRole);
					}
					return true;
				}
				return false;
			}
			return false;
		}
		return false;
	}
	
	/**
	* Creates any new documents that must be created for the
	* folder collaboration step with primary key $iGroupFolderApprovalID.  Users
	* responsible for their creation are notified via email 
	*/
	function createDependantDocuments(& $oFolderUserRole) {
		global $default;
		//only create the documents if they haven't been created		
		if ($oFolderUserRole->getDependantDocumentsCreated() == false) {
			$sQuery = "SELECT * FROM $default->dependant_document_template_table WHERE group_folder_approval_link_id = ?";/*ok*/
            $aParams = array($oFolderUserRole->getGroupFolderApprovalID());
			$sql = $default->db;
			$sql->query(array($sQuery, $aParams));
			while ($sql->next_record()) {				
				$oDependantDocumentInstance = & new DependantDocumentInstance($sql->f("document_title"), $sql->f("default_user_id"), $sql->f("template_document_id"), $oFolderUserRole->getDocumentID());				
				if ($oDependantDocumentInstance->create()) {
					//get the user who is responsible for creating the new document
					$oUser = User::get($oDependantDocumentInstance->getUserID());
					//get the document that can be used as a template, if there is one
					$oDocument = Document::get($oDependantDocumentInstance->getTemplateDocumentID());
					//send the user notification via email
					$sBody = $oUser->getName() . ", a step in the document collaboration process requires you to create a new document.  " .
								generateLink("/control.php","action=dashboard","Log onto KnowledgeTree") . " and select the relevant link under the 'Dependant Documents' heading on your dashboard when you are ready to upload it.  ";
								//if we have a template document
								if (!($oDocument === false)) {
									$sBody .= "The document entitled " . generateLink("/control.php", "action=viewDocument&fDocumentID=" . $oDocument->getID(), $oDocument->getName()) . " " .
									"can be used as a template";									
								}						
					$oEmail = & new Email();
					$oEmail->send($oUser->getEmail(), "Dependant document creation required", $sBody);
				} else {
					$_SESSION["errorMessage"] = "Error creating dependant documents when moving to next collaboration step. FolderUserRoleID: " + $oFolderUserRole->getID();
					return false;
				}				
			} 
			$oFolderUserRole->setDependantDocumentsCreated(true);
			$oFolderUserRole->update();
		}
	}
	
	/**
	* Rolls back a collaboration step if it is
	* rejected by the user.  This is a problem function as
	* it really should have transactions, but doesn't
	*
	* Note that new documents created for a folder collaboration step
	* are NOT rolled back - they are simply allowed to continue.  When
	* this step is next reached, these documents won't be created again
	* 
	*/
	function rollbackCollaborationStep($iDocumentID, $sComment = "") {
		global $default;
		//get the current sequence number		
		$sQuery = "SELECT GFAT.precedence, GFAT.folder_id, FURL.id AS furl_id, FURL.document_id AS document_id " . /*ok*/
				"FROM $default->folders_user_roles_table AS FURL " .
				"INNER JOIN $default->groups_folders_approval_table AS GFAT ON FURL.group_folder_approval_id = GFAT.id " .
				"WHERE FURL.document_id = ? " .
				"AND FURL.user_id = ? " .
				"AND FURL.active = 1 " .
				"ORDER BY GFAT.precedence ASC";
        $aParams = array($iDocumentID, $_SESSION["userID"]);
				
		$sql = $default->db;
		$sql->query(array($sQuery, $aParams));
		if ($sql->next_record()) {
			$iCurrentSequenceNumber = $sql->f("precedence");
			$iFolderID = $sql->f("folder_id");
			$iCurrentFolderUserRoleID = $sql->f("furl_id");
			$iDocumentID = $sql->f("document_id");
			
			$oDocument = Document::get($iDocumentID);			
			
			//if there are concurrent collaboration steps and one is rejected, then all
			//must be rolled back, whether they were accepted or not			
			$sQuery = "SELECT FURL.id, FURL.user_id  " ./*ok*/
					  "FROM $default->folders_user_roles_table AS FURL " . 
					  "INNER JOIN $default->groups_folders_approval_table AS GFAT ON FURL.group_folder_approval_id = GFAT.id " .
					  "WHERE FURL.document_id = ? AND GFAT.precedence = ?";			
            $aParams = array($iDocumentID, $iCurrentSequenceNumber);
			$sql->query(array($sQuery, $aParams));
			   
			while ($sql->next_record()) {
				//roll back each user's step and then email them to inform them
				//of the change
				$oFolderUserRole = FolderUserRole::get($sql->f("id"));				
				$oFolderUserRole->setActive(false);
				$oFolderUserRole->setDone(false);
				if ($oFolderUserRole->update()) {
					$default->log->debug("DocumentCollaboration::rollbackCollaborationStep successfully updated folderUserRole=" . arrayToString($oFolderUserRole));
				} else {
					$default->log->error("DocumentCollaboration::rollbackCollaborationStep couldn't update folderUserRole=" . arrayToString($oFolderUserRole));
					// FIXME: shouldn't attempt to email the user- how to die gracefully is a problem....
				}

				$oUserToNotify = User::get($sql->f("user_id"));				
				// only send a mail to users other than yourself
				if ($oUserToNotify->getID() <> $_SESSION["userID"]) {
					//send an email only if the user is to be notified via email
					if ($oUserToNotify->getEmailNotification()) {
						$oCurrentUser = User::get($_SESSION["userID"]);					
		                $sBody = $oUserToNotify->getUserName() . ", the document, '" . generateLink("/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php", "fDocumentID=" . $oDocument->getID(), $oDocument->getName()) . "' " .                    
													"has been rejected by " . $oCurrentUser->getName() . " in the document collaboration process.  The collaboration process has been rolled back to the previous step. " .
													"Your role is no longer active and and any acceptances you may have made have been voided. " . 
													(strlen($sComment) > 0 ? "The comment entered by " . $oUserToNotify->getName() . " was: $sComment" : "");
						$oEmail = & new Email();
						$oEmail->send($oUserToNotify->getEmail(), "Document rejected in collaboration process", $sBody);
					}
				}
			}
			
			//get the previous sequence number
			$sQuery = "SELECT COALESCE(MAX(precedence), -1) AS precedence " ./*ok*/
						"FROM $default->groups_folders_approval_table AS GFAT " .
						"WHERE precedence < ?";
						"AND folder_id = ?";			
            $aParams = array($iCurrentSequenceNumber, $iFolderID);
			$sql->query(array($sQuery, $aParams));
			//there will always be a result in the result set
			$sql->next_record();			
			if ($sql->f("precedence") == -1) {
				//the current step is the first step
				//reset all steps and email the creator				
				DocumentCollaboration::resetDocumentCollaborationSteps($iDocumentID);
				//$oDocument = Document::get($iDocumentID);				
				$oUser = User::get($oDocument->getCreatorID());
				if ($oUser->getEmailNotification()) {
					$oCurrentUser = User::get($_SESSION["userID"]);
					
                    $sBody = $oUser->getUserName() . ", the document, '" . generateLink("/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php", "fDocumentID=" . $this->iId, $oDocument->getName()) . "' " .                    
											"has been rejected by " . $oCurrentUser->getName() . " in the document collaboration process.  The collaboration process has been stopped. " .
											(strlen($sComment) > 0 ? "The comment entered by " . $oCurrentUser->getName() . " was: $sComment" : "");
			
					$oEmail = & new Email();
					$oEmail->send($oUser->getEmail(), "Document rejected in collaboration process", $sBody);
				}
			} else {
				//there are steps prior to this step
				$sQuery = "SELECT FURL.id AS furl_id " . /*ok*/
					"FROM $default->folders_user_roles_table AS FURL INNER JOIN $default->groups_folders_approval_table AS GFAT ON FURL.group_folder_approval_id = GFAT.id " .
					"WHERE FURL.document_id = ? " .
					"AND GFAT.precedence = ?";
                $aParams = array($iDocumentID, $sql->f("precedence"));
				
				$sql->query(array($sQuery, $aParams));
				while ($sql->next_record()) {
					//reset all the previous steps and email the users
					//to tell them to re-reperform their steps
					$oFolderUserRole = FolderUserRole::get($sql->f("furl_id"));
					$oFolderUserRole->setActive(true);
					$oFolderUserRole->setDone(false);
					$oFolderUserRole->setDateTime("");
					$oFolderUserRole->update();
					
					$oDocument = Document::get($iDocumentID);
                    $oUser = User::get($oFolderUserRole->getUserID());
					if ($oUser->getEmailNotification()) {
						$oCurrentUser = User::get($_SESSION["userID"]);
					
                        $sBody = $oUser->getUserName() . ", the document, '" . generateLink("/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php", "fDocumentID=$iDocumentID", $oDocument->getName()) . "' " .                        
											"has been rejected by " . $oCurrentUser->getName() . " in the document collaboration process.  Please re-perform your step in the collaboration process." .
											(strlen($sComment) > 0 ? "The comment entered by " . $oCurrentUser->getName() . " was: $sComment" : "");
			
						$oEmail = & new Email();
						$oEmail->send($oUser->getEmail(), "Document rejected in collaboration process", $sBody);
					}
					
				}
				$oFolderUserRole = FolderUserRole::get($iCurrentFolderUserRoleID);
				$oFolderUserRole->setActive(false);
				$oFolderUserRole->setDone(false);
				$oFolderUserRole->update();				
			}
			
		}
	}
	
	/**
 	 * Checks if a document is pending web publishing
	 */
	function documentIsPendingWebPublishing($iDocumentID) {
		global $default;
		$sQuery = "SELECT id FROM $default->web_documents_table WHERE document_id = ? AND status_id = 1";/*ok*/
        $aParams = array($iDocumentID);
		$sql = $default->db;
		$sql->query(array($sQuery, $aParams));
		if ($sql->next_record()) {
			return true;;
		}
		return false;
	}
	
	/**
 	 * Checks if a document is published
	 */
	function documentIsPublished($iDocumentID) {
		global $default;
		$sQuery = "SELECT id FROM $default->web_documents_table WHERE document_id = ? AND status_id = 2";/*ok*/
        $aParams = array($iDocumentID);
		$sql = $default->db;
		$sql->query(array($sQuery, $aParams));
		if ($sql->next_record()) {
			return true;;
		}
		return false;
	}
		
	/**
	 * Notify the web master when a document is awaiting publishing 
	 *
     * @param integer the docoument to publish
     * @param string comment to the web master from the publisher
	 */
	function notifyWebMaster($iDocumentID, $sComment) {
		global $default;
		$sQuery = "SELECT WS.web_master_id, WS.web_site_name, WS.web_site_url " ./*ok*/
				  "FROM $default->web_sites_table AS WS " .
                  "INNER JOIN $default->web_documents_table AS WD ON WS.id = WD.web_site_id " .
				  "WHERE WD.document_id = ?";
        $aParams = array($iDocumentID);
		$sql = $default->db;
		$sql->query(array($sQuery, $aParams));
		if ($sql->next_record()) {
			$oUser = User::get($sql->f("web_master_id"));
			if (!($oUser === false)) {
				if ($oUser->getEmailNotification()) {
					$oDocument = Document::get($iDocumentID);
                    $sBody = $oUser->getUserName() . ", the document, '" . generateLink("/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php", "fDocumentID=" . $oDocument->iId, $oDocument->getName()) . "' " .                    
							 "is pending web publishing to " . $sql->f("web_site_name") . " (" . $sql->f("web_site_url") . ").\n\n  The publisher had these additional comments to make:\n$sComment" ;
						
					$oEmail = & new Email();
					$oEmail->send($oUser->getEmail(), "Document for Web Publishing", $sBody);
				}
			}			
		}		
	}
	

}

?>
