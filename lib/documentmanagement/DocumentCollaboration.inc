<?php
/** 
 *
 * Static functions dealing with the document approval process
 * 
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
		$sql->query("SELECT id FROM $default->owl_folders_user_roles_table WHERE document_id = $iDocumentID AND (active = 1 OR done = 1)");
		if ($sql->next_record()) {
			return true;
		}
		return false;
	}
    
    function documentCollaborationDone($iDocumentID) {
		global $default;
		$sql = $default->db;
		$sql->query("SELECT id FROM $default->owl_folders_user_roles_table WHERE document_id = $iDocumentID AND done = 0");
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
		$sql->query("SELECT id FROM $default->owl_folders_user_roles_table WHERE document_id = $iDocumentID AND active = 1 AND user_id = " . $_SESSION["userID"]);
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
		$sql = $default->db;		
		if ($sql->query("UPDATE $default->owl_folders_user_roles_table SET active = 0, done = 0 WHERE document_id = $iDocumentID")) {		
			return true;
		}
		return false;
	}
	
	/**
	* Checks if a there are any more steps to be be performed in
	* the current document collaboration process
	* 
	*/
	function isLastStepInCollaborationProcess($iDocumentID) {
		global $default;
		$sql = $default->db;
        $sQuery = "SELECT id FROM $default->owl_folders_user_roles_table WHERE document_id = $iDocumentID AND done = 0";
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
        $sQuery = "SELECT FURL.user_id, FURL.datetime, GFAL.precedence FROM $default->owl_folders_user_roles_table FURL " .
                  "INNER JOIN $default->owl_groups_folders_approval_table GFAL ON FURL.group_folder_approval_id = GFAL.id " .
                  "WHERE FURL.document_id = $iDocumentID " .
                  "ORDER BY GFAL.precedence";
		$sql->query($sQuery);
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
		//if the user is assinged to two or more roles, make sure we get the current
		//one by ordering by precedence
		$sql->query("SELECT FURL.id AS id, GFAT.precedence " .
					"FROM $default->owl_groups_folders_approval_table AS GFAT INNER JOIN $default->owl_folders_user_roles_table AS FURL ON GFAT.id = FURL.group_folder_approval_id " .
					"WHERE document_id = $iDocumentID AND user_id = " . $_SESSION["userID"] . " AND done=0 " . 
					"ORDER BY precedence ASC");
		if ($sql->next_record()) {
			//set it as done
			$oFolderUserRole = FolderUserRole::get($sql->f("id"));
			$oFolderUserRole->setActive(false);
			$oFolderUserRole->setDone(true);
			$oFolderUserRole->setDateTime(getCurrentDateTime());
			$oFolderUserRole->update();
			//get it's sequence number
			$iCurrentSequenceNumber =  $sql->f("precedence");
			$sql->query("SELECT MIN(precedence) AS precedence " . 
						"FROM $default->owl_groups_folders_approval_table AS GFAT INNER JOIN $default->owl_folders_user_roles_table AS FURL ON GFAT.id = FURL.group_folder_approval_id " . 
						"WHERE document_id = $iDocumentID AND done = 0");			
			if ($sql->next_record()) {
				if ($sql->f("precedence") != $iCurrentSequenceNumber) {
					//if there are no concurrent steps outstanding
					$iNextSequenceNumber = $sql->f("precedence");
					$sql->query("SELECT FURL.id " .
								"FROM $default->owl_groups_folders_approval_table AS GFAT INNER JOIN $default->owl_folders_user_roles_table AS FURL ON GFAT.id = FURL.group_folder_approval_id " . 
								"WHERE document_id = $iDocumentID AND precedence = $iNextSequenceNumber");
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
	* Rolls back a collaboration step if it is
	* rejected by the user.  This is a problem function as
	* it really should have transactions, but doesn't
	* 
	*/
	function rollbackCollaborationStep($iDocumentID, $sComment = "") {
		global $default;
		//get the current sequence number		
		$sQuery = "SELECT GFAT.precedence, GFAT.folder_id, FURL.id AS furl_id, FURL.document_id AS document_id " . 
				"FROM $default->owl_folders_user_roles_table AS FURL INNER JOIN $default->owl_groups_folders_approval_table AS GFAT ON FURL.group_folder_approval_id = GFAT.id " .
				"WHERE FURL.document_id = $iDocumentID " .
				"AND user_id = " . $_SESSION["userID"] . " " .
				"AND FURL.active = 1 " .
				"ORDER BY GFAT.precedence ASC";
				
		//echo $sQuery;		
				
		$sql = $default->db;
		$sql->query($sQuery);
		if ($sql->next_record()) {
			$iCurrentSequenceNumber = $sql->f("precedence");
			$iFolderID = $sql->f("folder_id");
			$iCurrentFolderUserRoleID = $sql->f("furl_id");
			$iDocumentID = $sql->f("document_id");
			
			$oDocument = Document::get($iDocumentID);			
			
			//if there are concurrent collaboration steps and one is rejected, then all
			//must be rolled back, whether they were accepted or not			
			$sQuery = "SELECT FURL.id, FURL.user_id  " .
						"FROM $default->owl_folders_user_roles_table AS FURL INNER JOIN $default->owl_groups_folders_approval_table AS GFAT ON FURL.group_folder_approval_id = GFAT.id " .
						"WHERE FURL.document_id = $iDocumentID AND GFAT.precedence = $iCurrentSequenceNumber";			
			$sql->query($sQuery);
			//echo $sQuery;
			//echo $sQuery;
			   
			while ($sql->next_record()) {
				//roll back each user's step and then email them to inform them
				//of the change
				$oFolderUserRole = FolderUserRole::get($sql->f("id"));				
				$oFolderUserRole->setActive(false);
				$oFolderUserRole->setDone(false);
				$oFolderUserRole->update();
				
				
				$oUserToNotify = User::get($sql->f("user_id"));
				$oCurrentUser = User::get($_SESSION["userID"]);					
                $sBody = $oUserToNotify->getUserName() . ", the document, '" . generateLink("/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php", "fDocumentID=" . $this->iId, $oDocument->getName()) . "' " .                    
											"has been rejected by " . $oUserToNotify->getName() . " in the document collaboration process.  The collaboration process has been rolled back to the previous step. " .
											"Your role is no longer active and and any acceptances you may have made have been voided. " . 
											"The comment entered by " . $oUserToNotify->getName() . " was: $sComment";
				$oEmail = & new Email();
				$oEmail->send($oUserToNotify->getEmail(), "Document rejected in collaboration process", $sBody);
			}
			
			//get the previous sequence number
			$sQuery = "SELECT COALESCE(MAX(precedence), -1) AS precedence " .
						"FROM $default->owl_groups_folders_approval_table AS GFAT " .
						"WHERE precedence < $iCurrentSequenceNumber";
						"AND folder_id = $iFolderID";			
			$sql->query($sQuery);			 
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
											"The comment entered by " . $oCurrentUser->getName() . " was: $sComment";
			
					$oEmail = & new Email();
					$oEmail->send($oUser->getEmail(), "Document rejected in collaboration process", $sBody);
				}
			} else {
				//there are steps prior to this step
				$sQuery = "SELECT FURL.id AS furl_id " . 
					"FROM $default->owl_folders_user_roles_table AS FURL INNER JOIN $default->owl_groups_folders_approval_table AS GFAT ON FURL.group_folder_approval_id = GFAT.id " .
					"WHERE FURL.document_id = $iDocumentID " .
					"AND GFAT.precedence = " . $sql->f("precedence");
				
				$sql->query($sQuery);
				while ($sql->next_record()) {
					//reset all the previous steps and email the users
					//to tell them to re-reperform their steps
					$oFolderUserRole = FolderUserRole::get($sql->f("furl_id"));
					$oFolderUserRole->setActive(true);
					$oFolderUserRole->setDone(false);
					$oFolderUserRole->setDateTime("");
					$oFolderUserRole->update();
					
					$oDocument = Document::get($iDocumentID);
                    $oUser = User::get($oDocument->getCreatorID());
					if ($oUser->getEmailNotification()) {
						$oUser = User::get($oFolderUserRole->getUserID());
						$oCurrentUser = User::get($_SESSION["userID"]);
					
                        $sBody = $oUser->getUserName() . ", the document, '" . generateLink("/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php", "fDocumentID=" . $this->iId, $oDocument->getName()) . "' " .                        
											"has been rejected by " . $oCurrentUser->getName() . " in the document collaboration process.  Please re-perform you step in the collaboration process." .
											"The comment entered by " . $oCurrentUser->getName() . " was: $sComment";
			
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
	* 
	* 
	*/
	function documentIsPendingWebPublishing($iDocumentID) {
		global $default;
		$sQuery = "SELECT id FROM $default->owl_web_documents_table WHERE document_id = $iDocumentID AND status_id = 1";
		$sql = $default->db;
		$sql->query($sQuery);
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
		$sQuery = "SELECT WS.web_master_id, WS.web_site_name, WS.web_site_url " .
				  "FROM $default->owl_web_sites_table AS WS " .
                  "INNER JOIN $default->owl_web_documents_table AS WD ON WS.id = WD.web_site_id " .
				  "WHERE WD.document_id = $iDocumentID";
		
		$sql = $default->db;
		$sql->query($sQuery);
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