<?php
/**
* Document collaboration business logic - contains business logic to set up
* document approval process
*
* Required form variables:
*	o fFolderCollaborationID - primary key of folder collaboration entry we are viewing
*	o fDocumentID - primary key of document this folder collaboration entry is for
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 28 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->owl_fs_root/lib/foldermanagement/FolderCollaboration.inc");
	require_once("$default->owl_fs_root/lib/foldermanagement/FolderUserRole.inc");
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
	require_once("$default->owl_fs_root/lib/groups/Group.inc");	
	require_once("$default->owl_fs_root/presentation/Html.inc");
	require_once("$default->owl_fs_root/lib/security/permission.inc");	
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
	require_once("collaborationUI.inc");
	
	//if the required form variabled are set
	if (isset($fFolderCollaborationID) && isset($fDocumentID)) {
		//if the user has write permission for the document
		if (Permission::userHasDocumentWritePermission($fDocumentID)) {
			if (isset($fForStore)) {
				//if we are storing, get the folder collaboration entry from the database
				$oFolderCollaboration = & FolderCollaboration::get($fFolderCollaborationID);			
				if (isset($fUserID) & ($fUserID != -1)) {
					//if a user has been selected, then set up the folders_users_roles_link database entry
					$oFolderUserRole = & FolderUserRole::getFromFolderCollaboration($fFolderCollaborationID);
					if (!($oFolderUserRole === false)) {
						//if we have an entry, just update it
						$oFolderUserRole->setUserID($fUserID);
						$oFolderUserRole->update();
					} else {
						//otherwise, create a new one
						$oFolderUserRole = & new FolderUserRole($fUserID, $fDocumentID, $fFolderCollaborationID, 0);
						$oFolderUserRole->create();
					}
				}
				if (isset($fRoleID) & ($fRoleID != -1)) {
					//if a role was chosen then update the folder collaboration entry in the db
					$oFolderCollaboration->setRoleID($fRoleID);
					$oFolderCollaboration->update();
				}
				//go back to the document view page
				redirect("$default->owl_root_url/control.php?action=viewDocument&fDocumentID=$fDocumentID");
			} else {
				//we're still browsing, so just display the document routing details
				require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");			
				$oPatternCustom = & new PatternCustom();
				
				$aFolderCollaborationArray = getFolderCollaborationArray($fFolderCollaborationID);			
				$oPatternCustom->setHtml(getDocumentRoutingPage($aFolderCollaborationArray["group_id"],$aFolderCollaborationArray["user_id"], $aFolderCollaborationArray["role_id"], $aFolderCollaborationArray["sequence"], $fDocumentID));
				$main->setCentralPayload($oPatternCustom);
				$main->setFormAction($_SEVER["PHP_SELF"] . "?fFolderCollaborationID=$fFolderCollaborationID&fDocumentID=$fDocumentID&fForStore=1");
				$main->render();
			}
		} else {
			//user does not have permission to edit these details
			require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");			
			$oPatternCustom = & new PatternCustom();							
			$oPatternCustom->setHtml("<a href=\"$default->owl_root_url/control.php?action=viewDocument&fDocumentID=" . $fDocumentID . "\">Return to document view page</a>");
			$main->setCentralPayload($oPatternCustom);
			$main->setErrorMessage("You do not have permission to edit document routing details");
			$main->render();
		}
	} else {
		//user does not have permission to edit these details
			require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");			
			$oPatternCustom = & new PatternCustom();							
			$oPatternCustom->setHtml("<a href=\"$default->owl_root_url/control.php?action=dashboard\">Return to document dashboard</a>");
			$main->setCentralPayload($oPatternCustom);
			$main->setErrorMessage("No document/document routing details are currently selected");
			$main->render();
	}
}

function getFolderCollaborationArray($fFolderCollaborationID) {
	global $default;
	$sQuery = "SELECT GFL.group_id AS group_id, GFL.folder_id AS folder_id, GFL.precedence AS precedence, GFL.role_id, U.id AS user_id " .
			"FROM $default->owl_groups_folders_approval_table AS GFL LEFT OUTER JOIN folders_users_roles_link AS FURL ON FURL.group_folder_approval_id = GFL.id " .
			"LEFT OUTER JOIN users AS U ON FURL.user_id = U.id " .
			"WHERE GFL.id = $fFolderCollaborationID";
			
	$sql = $default->db;
	$sql->query($sQuery);
	if ($sql->next_record()) {
		return array("group_id" => $sql->f("group_id"), "user_id" => $sql->f("user_id"), "role_id" => $sql->f("role_id"), "sequence" => $sql->f("precedence"));
	} 
}

?>
