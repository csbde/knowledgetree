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
function getPage($iUserID) {	
	global $default;
	$oUser = null;
	$oGroup = null;
	
	// change headings if User selected
	if (isset($iUserID)) {
		$oUser = User::get($iUserID);
	}else{
		$heading = "<tr>Please select a User: </tr>\n";
	}
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Select a User to Remove from a Group:</b>\n";
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
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	//$sToRender .= "<td></td><td>" . getDeleteButton($oUser);	
	//$sToRender .= "<td></td><td>" . getCancelButton($oUser);
	$sToRender .= "</td></table>\n";
	
	
	return $sToRender;
}



// get page for removal
function getGroupPage($iUserID, $aGroup) {	
	global $default;
	$oUser = null;
	$oGroup = null;
	
	// change headings if User selected
	if (isset($iUserID)) {
		$oUser = User::get($iUserID);
	}
	
	// change headings if User selected
	if (isset($fGroupID)) {
		$oGroup = Group::get($fGroupID);
		echo "what: " . $oGroup->getName();
	}
	
	// when Groupid is gotten show the name
	if (!isset($oGroup)) {
		
		$Groupdisplay = "<td>Group Name: " . getGroupDisplay($aGroup) . "</td>\n";
	}
	else{
		$Groupdisplay = "<td>Group Name: " . $oGroup->getName() . "</td>\n";
	}
		
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Select a Group to Remove User from:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
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
	$sToRender .= "<td></td><td>" . getSelectButton($oUser);	
	$sToRender .= "<td></td><td>" . getCancelButton($oUser);
	$sToRender .= "</td></table>\n";
	
	
	return $sToRender;
}

// get page for removal
function getDeleteConfirmedPage($iUserID,$fGroupID) {	
	global $default;
	$oUser = null;
	$oGroup = null;
	
	// change headings if User selected
	if (isset($iUserID)) {
		$oUser = User::get($iUserID);
		$heading = "<tr>Are you sure you wish to remove this user from the Group?</tr>"; 
	}
	
	// change headings if User selected
	if (isset($fGroupID)) {
		$oGroup = Group::get($fGroupID);
		$Groupdisplay = "<td>Group Name: " . $oGroup->getName() . "</td>\n";
	}
		
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Select a Group to Remove User from:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= $heading;
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
function getPageFail() {	
	global $default;
		
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
		$oPatternListBox = & new PatternListBox($default->owl_users_table, "username", "id", "fUserID");
		$oPatternListBox->setWhereClause($where);
		$oPatternListBox->setFromClause($from);
		//$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fUserID\" value=\"" . $oUser->iId . "\">\n" .
		 $oUser->getUserName();
	}
	
}

	


// show Group details on confirm deletion page
function getGroupDisplay($aGroup) {
	global $default;
		/*"select UGL.group_id, GL.name from users_groups_link as UGL
		 inner join groups_lookup AS GL on UGL.group_id = GL.id
		 where UGL.user_id=1"
		 */
		
		 $oGroup= null;
		$sGroup = "<Select Name=\"fGroupID\">";
		 
		 for ($i=0;$i < count($aGroup);$i++){
		 	
		 	   $oGroup= Group::get($aGroup[$i]);
		 	    $Options .=  "<OPTION value= \"". $oGroup->iId . "\">". $oGroup->getName() ."</OPTION>\n";
		 	
		 }
		
		$sGroup .= $Options . "</SELECT>";
		
		return $sGroup;
	
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


// show delete button or select button
function getSelectButton($oUser) {
	global $default;
	if (!isset($oUser)) {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" /></td>\n";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" /></td>\n";
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