<?php
/**
* Presentation information for adding a User
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

// gets main page
function getPage($iUserID, $iGroupID) {	
	global $default;
	$oUser = null;
	$oGroup = null;
	
	if (isset($iUserID)) {
		$oUser = User::get($iUserID);
		$heading = "<tr>Are you Sure you wish to assign the User to the Group? </tr>\n";
	}else{
		$heading = "<tr>Please Assign a User to a Group: </tr>\n";
	}
	
	
	if (isset($iGroupID)) {
		$oGroup = Group::get($iGroupID);
		
	}
		
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Assign User to Group:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= $heading;
	$sToRender .= "<br>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td>User Name: </td><td>" . getUserDisplay($oUser) . "</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Group Name: </td><td>" . getGroupDisplay($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td><td>" . getAssignButton($oUser);	
	$sToRender .= getCancelButton($oUser);	
	$sToRender .= "</td></table>\n";
	
	
	return $sToRender;
}


// page that comes up when there is nothing selected
function getPageNotSelected() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Sorry, Selection Failed</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>Please select BOTH a User AND a Group</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUserToGroup\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

//if edition successful
function getPageSuccess() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Success!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The User was successfully assigned to the specified Group</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUserToGroup\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

//if edition successful
function getPageFail() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Failed!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The User already belongs to the specified Group</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUserToGroup\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// display the listbox initially ..then just display the text
function getUserDisplay($oUser) {
	global $default;
	if (!isset($oUser)) {
        $oPatternListBox = & new PatternListBox($default->owl_users_table, "username", "id", "fUserID");
        if (Permission::userIsUnitAdministrator()) {
            $oPatternListBox->setFromClause("INNER JOIN $default->owl_users_groups_table UGL ON ST.id=UGL.user_id " .
                                            "INNER JOIN $default->owl_groups_units_table GUL ON UGL.group_id=GUL.group_id");
            $oPatternListBox->setWhereClause("GUL.unit_id=" . User::getUnitID($_SESSION["userID"]));
            
            $oUnassignedUsersLB = & new PatternListBox($default->owl_users_table, "username", "id", "fUserID");
            $oUnassignedUsersLB->setFromClause("LEFT OUTER JOIN $default->owl_users_groups_table UGL ON ST.id=UGL.user_id");
            $oUnassignedUsersLB->setWhereClause("ISNULL(UGL.group_id)");
            $default->log->info(arrayToString($oUnassignedUsersLB->getEntries()));
            $oPatternListBox->setAdditionalEntries($oUnassignedUsersLB->getEntries());
        }
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fUserID\" value=\"" . $oUser->iId . "\">\n" .
		 $oUser->getUserName();
	}
	
}

// display the listbox initially ..then just display the text
function getGroupDisplay($oGroup) {
	global $default;
	if (!isset($oGroup)) {
        if (Permission::userIsSystemAdministrator()) {
            // if this is the system administrator, prepend group names with unit name
            $oPatternListBox = & new PatternListBox($default->owl_groups_table, "name", "id", "fGroupID");
            //$oPatternListBox->setFromClause("INNER JOIN $default->");
        } else if (Permission::userIsUnitAdministrator()) {
            // else if this is a unit administrator, only display the groups in your unit
            $oPatternListBox = & new PatternListBox($default->owl_groups_table, "name", "id", "fGroupID");
            $oPatternListBox->setFromClause("INNER JOIN $default->owl_groups_units_table GUL on ST.id=GUL.group_id");
            $oPatternListBox->setWhereClause("GUL.unit_id=" . User::getUnitID($_SESSION["userID"]));
        }
        return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $oGroup->iId . "\">\n" .
		$oGroup->getName();
	}
		
}

// show button after User selected
function getAssignButton($oUser) {
	global $default;
	if (!isset($oUser)) {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" /></td>\n";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/assign.gif\" value=\"submit\" /></td>\n";
	}
}
// show button after User selected
function getCancelButton($oUser) {
	global $default;
	if (!isset($oUser)) {
		//return "<td align = right><a href=\"$default->rootUrl/control.php?action=UserManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=addUserToGroup\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}


?>