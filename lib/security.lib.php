<?php

/*

  File: security.lib.php
  Author: Chris
  Date: 2000/12/14

  Owl: Copyright Chris Vincent <cvincent@project802.net>

  You should have received a copy of the GNU Public
  License along with this package; if not, write to the
  Free Software Foundation, Inc., 59 Temple Place - Suite 330,
  Boston, MA 02111-1307, USA.

*/

/**
* Get the security policy for a specified folder
*
* @param id folder id
*
* @return int 1 = permission granted, 0 = permission denied
*/
function getfolderpolicy($id) {
	global $default;
	$sql = new Owl_DB; $sql->query("select security from $default->owl_folders_table where id = '$id'");
	while ($sql->next_record()) return $sql->f("security");
}

/**
* Get the security policy for a specified file
*
* @param id file id
*
* @return int security policy
*/
function getfilepolicy($id) {
	global $default;
	$sql = new Owl_DB; $sql->query("select security from $default->owl_files_table where id = '$id'");
	while ($sql->next_record()) return $sql->f("security");
}

/**
* This function is simple...it returns either a 1 or 0
* If the authentication is good, it returns 1
* If the authentication is bad, it returns 0
*
* Policy key for FILES:
*
* 0 = World read
* 1 = World edit
* 2 = Group read
* 3 = Group edit
* 4 = Creator edit
* 5 = Group edit no delete
* 6 = World edit no delete
* 7 = Group edit, World read 
* 8 = Group edit, World read - no delete 
*
* Policy key for FOLDERS:
*
* 50 = Anyone can read
* 51 = Anyone can upload/create folders
* 56 = Anyone can upload/create folders but not delete
* 52 = Only the group can read
* 53 = Only the group can upload/create folders
* 55 = Only the group can upload/create folders but not delete; except the creator 
* 54 = Only the creator can upload/create folders
* 57 = Only the group can upload/create folders but anyone can read 
* 58 = Only the group can upload/create folders (no delete) but anyone can read 
*/

function check_auth($id, $action, $userid) {
	global $default;
	$usergroup = owlusergroup($userid);
	$filecreator = owlfilecreator($id);
	$foldercreator = owlfoldercreator($id);
	$filegroup = owlfilegroup($id);
	$foldergroup = owlfoldergroup($id);

	if (($action == "folder_modify") || 
            ($action == "folder_view")   || 
            ($action == "folder_delete") ||
            ($action == "folder_property")) {
		$policy = getfolderpolicy($id);
	} else {
		$policy = getfilepolicy($id);
	}

	//if policy is: world read
	if ($policy == "0") {
		//if the user want to delete/modify
		if (($action == "file_delete") || ($action == "file_modify")) {
			//if the user is not the file create
			if ($userid != $filecreator) {
				$authorization = "0";
			} else {
				$authorization = "1";
			}
		} else {
			$authorization = "1";
		}
	}
	//if the policy is: world edit
	if ($policy == "1") {
		$authorization = "1";
	}
	//if the policy is: group read
	if ($policy == "2") {
		//if the user wants to delete/modify the file
		if (($action == "file_delete") || ($action == "file_modify")) {
			if ($userid != $filecreator) {
                                $authorization = "0";
                        } else {
                                $authorization = "1";
			}
		} else {
                        // Bozz Change Begin
                 	$sql = new Owl_DB;
                 	$sql->query("SELECT * FROM $default->owl_users_grpmem_table WHERE userid = '$userid' and groupid = '$filegroup'");
			//if the user is in the group
			if ($filegroup == $usergroup || $sql->num_rows($sql) > 0) {
                        // Bozz Change End
				$authorization = "1";
			} else {
				$authorization = "0";
			}
		}

	}
	//if the policy is: group edit
	if ($policy == "3") {
		if (($action == "file_delete") || ($action == "file_modify") || ($action == "file_download")) {
                // Bozz Change Begin
                $sql = new Owl_DB;
                $sql->query("SELECT * FROM $default->owl_users_grpmem_table WHERE userid = '$userid' and groupid = '$filegroup'");
                // Bozz Change End
			//if the user is not in the group
			if ($usergroup != $filegroup && $sql->num_rows($sql) == 0) {
                                $authorization = "0";
                        } else {
                                $authorization = "1";
			}
		}
	}
	//if the policy is: creator edit
	if ($policy == "4") {
		//if the user is the creator
		if ($filecreator == $userid) {
			$authorization = "1";
		} else {
			$authorization = "0";
		}
	}
	//if the policy is: group edit no delete
	if ($policy == "5") {
		if (($action == "file_modify") || ($action == "file_download")) {
                // Bozz Change Begin
                $sql = new Owl_DB;
                $sql->query("SELECT * FROM $default->owl_users_grpmem_table WHERE userid = '$userid' and groupid = '$filegroup'");
                // Bozz Change End
			//if the user is in the group
			if ($usergroup != $filegroup && $sql->num_rows($sql) == 0) {
                                $authorization = "0";
                        } else {
                                $authorization = "1";
			}
                 }
                 if ($action == "file_delete") {
	              //if the user is the file creator
                      if ($filecreator == $userid) {
                           $authorization = "1";
                      } else {
                           $authorization = "0";
                      }
                   }
	}
	//if the policy is: world edit no delete
	if ($policy == "6") {
		$authorization = "1";
                 if ($action == "file_delete")  {
			 //if the user is the creator
                      if ($filecreator == $userid) {
                           $authorization = "1";
                      } else {
                           $authorization = "0";
                      }
                 }
	}
	//if the policy is: group edit world read
	if ($policy == "7") { 
		if (($action == "file_delete") || ($action == "file_modify")) { 
			$sql = new Owl_DB; 
			$sql->query("SELECT * FROM $default->owl_users_grpmem_table WHERE userid = '$userid' and groupid = '$filegroup'");
			//if the user is not in the group 
			if ($usergroup != $filegroup && $sql->num_rows($sql) == 0) { 
				$authorization = "0"; 
			} else { 
				$authorization = "1"; 
			} 
		} 
		if ($action == "file_download") { 
			$authorization = "1"; 
		} 
	} 
	//if the policy is: group edit, world read, no delete
	if ($policy == "8") { 
		if ($action == "file_modify") { 
			$sql = new Owl_DB; 
			$sql->query("SELECT * FROM $default->owl_users_grpmem_table WHERE userid = '$userid' and groupid = '$filegroup'");
			//if the user is not in the group
			if ($usergroup != $filegroup && $sql->num_rows($sql) == 0) { 
				$authorization = "0"; 
			} else { 
				$authorization = "1"; 
			} 
		} 
		if ($action == "file_download") { 
			$authorization = "1"; 
		} 
		if ($action == "file_delete") { 
			//if the user is the creator
			if ($filecreator == $userid) { 
				$authorization = "1"; 
			} else { 
				$authorization = "0"; 
			} 
		} 
	}
	//if the policy is: anyone can read
	if ($policy == "50") {
		if (($action == "folder_delete")   || 
                    ($action == "folder_property") ||
                    ($action == "folder_modify")) {
		        //if the user is not the creator
			if ($userid != $foldercreator) {
				$authorization = "0";
			} else {
				$authorization = "1";
			}
		} else {
			$authorization = "1";
		}
	}
	
	//if the policy is: anyone can upload/create folders
	if ($policy == "51") {
		$authorization = "1";
	}
	
	//if the policy is: only the group can read folders
	if ($policy == "52") {
		if (($action == "folder_delete")   || 
                    ($action == "folder_property") ||
                    ($action == "folder_modify")) {
			if ($userid != $foldercreator) {
                                $authorization = "0";
                        } else {
                                $authorization = "1";
			}
		} else {
                // Bozz Change Begin
                $sql = new Owl_DB;
                $sql->query("SELECT * FROM $default->owl_users_grpmem_table WHERE userid = '$userid' and groupid = '$foldergroup'");
			if ($foldergroup == $usergroup || $sql->num_rows($sql) > 0) {
                // Bozz Change End
				$authorization = "1";
			} else {
				$authorization = "0";
			}
		}

	}
	
	//if the policy is: only the group can upload/create folders
	if ($policy == "53") {
		if (($action == "folder_delete") || 
                    ($action == "folder_modify") || 
                    ($action == "folder_property") || 
                    ($action == "folder_view")) {
                // Bozz Change Begin
                $sql = new Owl_DB;
                $sql->query("SELECT * FROM $default->owl_users_grpmem_table WHERE userid = '$userid' and groupid = '$foldergroup'");
			//if the user is not in the group
			if ($usergroup != $foldergroup && $sql->num_rows($sql) == 0) {
                // Bozz Change End
                                $authorization = "0";
                        } else {
                                $authorization = "1";
			}
		}
	}
	
	//if the policy is: only the creator can upload/change files
	if ($policy == "54") {
		//if the user is the creator
		if ($foldercreator == $userid) {
			$authorization = "1";
		} else {
			$authorization = "0";
		}
	}
	
	//if the policy is: only the group can upload/create folders but not delete; except the creator
	if ($policy == "55") {
		if (($action == "folder_modify") || ($action == "folder_view")) {
                // Bozz Change Begin
                $sql = new Owl_DB;
                $sql->query("SELECT * FROM $default->owl_users_grpmem_table WHERE userid = '$userid' and groupid = '$foldergroup'");
			if ($usergroup != $foldergroup && $sql->num_rows($sql) == 0) {
                // Bozz Change End
                                $authorization = "0";
                        } else {
                                $authorization = "1";
			}
		}
                if (($action == "folder_delete")  ||
                    ($action == "folder_property")) {
                   if ($foldercreator == $userid) {
                           $authorization = "1";
                   } else {
                           $authorization = "0";
                   }
               }
        }
	//if the policy is: anyone can upload/create folders but not delete
	if ($policy == "56") {
		$authorization = "1";
                if (($action == "folder_delete")  ||
                    ($action == "folder_property")) {
		   //if the user is the creator
                   if ($foldercreator == $userid) {
                           $authorization = "1";
                   } else {
                           $authorization = "0";
                   }
               }
	}

	//if the policy is: only the group can upload/create folders but anyone can read
	if ($policy == "57") { 
		if (($action == "folder_modify") || ($action == "folder_delete")) { 
				$sql = new Owl_DB; 
				$sql->query("SELECT * FROM $default->owl_users_grpmem_table WHERE userid = '$userid' and groupid = '$foldergroup'");
				//if the user is not in the group
				if (($usergroup != $foldergroup) && ($sql->num_rows($sql) == 0)) { 
						$authorization = "0"; 
				} else { 
						$authorization = "1"; 
				} 
		} 
		if ($action == "folder_property") { 
				//if the user is the creator
				if ($foldercreator == $userid) { 
						$authorization = "1"; 
				} else { 
						$authorization = "0"; 
				} 
		} 
		if ($action == "folder_view") { 
				$authorization = "1"; 
		} 
	} 
	//if the policy is: only the group can upload/create folders (no delete) but anyone can read
	if ($policy == "58") { 
		if ($action == "folder_modify") { 
			$sql = new Owl_DB; 
			$sql->query("SELECT * FROM $default->owl_users_grpmem_table WHERE userid = '$userid' and groupid = '$foldergroup'");
			//if the user is not in the group
			if ($usergroup != $foldergroup && $sql->num_rows($sql) == 0) { 
				$authorization = "0"; 
			} else { 
				$authorization = "1"; 
			} 
		} 
		if ($action == "folder_property") { 
			//if the user is the creator
			if ($foldercreator == $userid) { 
				$authorization = "1"; 
			} else { 
				$authorization = "0"; 
			} 
		} 
		if ($action == "folder_delete") { 
			//if the user is the creator
			if ($foldercreator == $userid) { 
				$authorization = "1"; 
			} else { 
				$authorization = "0"; 
			} 
		} 
		if ($action == "folder_view") { 
			$authorization = "1"; 
		} 
	} 

// Bozz Change Begin
// I Think that the Admin Group should 
// have the same rights as the admin user
	if ($userid == 1 || $usergroup == 0) {
// Bozz Change End
		$authorization = "1";
	}
// cv change bug #504298
// this call must be recursive through the parent directories

	// continue recursion?
	if( $authorization == 1 ) {
		if( ($policy > 49) && ($id == 1) ) {
			// stop if we are at the doc root
			return $authorization;
		} else {
			// continue;
			if($policy < 50) {
				$parent = owlfileparent($id);
			} else {
				$parent = owlfolderparent($id);
			}
			return check_auth($parent, "folder_view", $userid);
		}
	} else {
		// dont continue because authorization is 0
		return $authorization;
	}
}
