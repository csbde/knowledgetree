<?php
/**
* Presentation information for adding a User
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

// get page for removal
function getPage($iUserID, $iGroupID) {	
	global $default;
	$oUser = null;
	$oGroup = null;
	
	// change headings if User selected
	if (isset($iUserID)) {
		$oUser = User::get($iUserID);
		$heading = "<tr><td>Are you sure you wish to remove the User from the Group?</td> </tr>\n";
	}else{
		$heading = "<tr>Please select a User: </tr>\n";
	}
	
	// when Groupid is gotten show the name
	if (isset($iGroupID)) {
		$oGroup = Group::get($iGroupID);
		$Groupdisplay = "<td>Group Name: " . getGroupDisplay($oGroup) . "</td>\n";
		
	}
	else{
		$Groupdisplay = "";
	}
		
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Select a User to Remove from its Group:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= $heading;
	$sToRender .= "<br>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td>User Name: " . getUserDisplay($oUser) . "</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= $Groupdisplay;
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td><td>" . getDeleteButton($oUser);	
	$sToRender .= "<td></td><td>" . getCancelButton($oUser);
	$sToRender .= "</td></table>\n";
	
	
	return $sToRender;
}

// show paig if deletion fails
function getPageFail($iUserID) {	
	global $default;
	$oUser = null;
	$oGroup = null;
		
	if (isset($iUserID)) {
		$oUser = User::get($iUserID);
	}
	
	$iGroupID = GroupUserLink::userBelongsToGroup($iUserID);
	
	$oGroup= Group::get($iGroupID);
		
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Sorry, Removal Failed</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeUserFromGroup\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// if no User selected show selection fail page
function getPageNotSelected() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Sorry, Selection Failed</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>Please select a User</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeUserFromGroup\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// show success page if removed from Group successfully
function getPageSuccess() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Success!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The User was successfully removed from the Group</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeUserFromGroup\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// show listbox of Users belonging to Users_Groups_link table
function getUserDisplay($oUser) {
	global $default;
	// added clauses to get those Users belonging to the Users_Groups_link table
	$from = "," . $default->owl_users_groups_table . " AS GU";
	$where = "(ST.id = GU.user_id)";
	
	
	if (!isset($oUser)) {
		$oPatternListBox = & new PatternListBox($default->owl_users_table, "name", "id", "fUserID");
		$oPatternListBox->setWhereClause($where);
		$oPatternListBox->setFromClause($from);
		//$oPatternListBox->setIncludeDefaultValue(true);
		//$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fUserID\" value=\"" . $oUser->iId . "\">\n" .
		 $oUser->getName();
	}
	
}

// show Group details on confirm deletion page
function getGroupDisplay($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		//$oPatternListBox = & new PatternListBox($default->owl_groups_table, "name", "id", "fGroupID");
		//$oPatternLiListBoxstBox->setPostBackOnChange(true);
		//return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $oGroup->iId . "\">\n" .
		$oGroup->getName();
	}
		
}

// show delete button or select button
function getDeleteButton($oUser) {
	global $default;
	if (!isset($oUser)) {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" /></td>\n";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" /></td>\n";
	}
}

// show cancel button
function getCancelButton($oUser) {
	global $default;
	if (!isset($oUser)) {
		//return "<td align = right><a href=\"$default->rootUrl/control.php?action=UserManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=removeUserFromGroup\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}


?>