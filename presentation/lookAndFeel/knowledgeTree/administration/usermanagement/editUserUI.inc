<?php
/**
* Presentation information for renaming a User
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

// if its the manual edit page..then display normally
function getEditPage($iUserID) {	
	global $default;
	$oUser = null;
	if (isset($iUserID)) {
		$oUser = User::get($iUserID);
		
	}
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Edit User Properties:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Username: </td><td>" . getUserDisplay($oUser) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td>" . getUpdateButton($oUser);	
	$sToRender .= getCancelButton($oUser);	
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

function getUserDetailsPage($iUserID) {
	
	global $default;
	$oUser = null;
	//if (isset($iUserID)) {
	//	$oUser = User::get($iUserID);
	//}
	
	$sQuery =  "SELECT * FROM $default->owl_users_table WHERE id = $iUserID";
	$aDisplayColumns = array( "username","name","email", "mobile", "email_notification", "sms_notification", "ldap_dn", "quota_max", "quota_current","max_sessions"); 
	$aStoreColumns = array( "username","name","email", "mobile", "email_notification", "sms_notification", "ldap_dn", "quota_max", "quota_current","max_sessions") ;
	$aColumnNames = array("Username: ","Name: ", "Email Address: ", "Mobile Number: ", "Email Notification: ", "Sms Notification: ", "Ldap Dn: ", "Quota Max: ", "Quota Current: ", "Max Sessions: ");
	$aDisplayColumnTypes = array(1,1,1,1,2,2,1,1,1,1);
	$aDatabaseColumnTypes = array(1,1,1,1,2,2,1,1,1,1);
	
	// get list of User properties...name...text field...is_Sys_admin and is_unit_admin..checkboxes
	$oPattern = & new PatternEditableListFromQuery($sQuery, $default->owl_users_table, $aDisplayColumns, $aStoreColumns, $aColumnNames, $aDisplayColumnTypes, $aDatabaseColumnTypes) ;
	$oPattern->setUniqueName("userEdit");
	$oPattern->setColumnsRequired(array(1,1,0,0,0,0,0,0,0,0));
	
	$sToRender .= "<br></br>\n";	
	$sToRender .= "<b>Edit User Properties:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPattern->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	
	$sToRender .= "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"Update\" /></td>\n";	
	$sToRender .= "</table>\n";
		
	
		
	return $sToRender;
	
	
	
}

// if edition is successful print otu success page
function getEditPageSuccess() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Success!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The User was successfully modified</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editUser\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// gets the page if edition fails
function getEditPageFail() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>FAILED!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>User modification Failed!</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editUser\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// show the list box when no User selected
// then shows a text box with the User's name
function getUserDisplay($oUser) {
	global $default;
	if (!isset($oUser)) {
		$oPatternListBox = & new PatternListBox($default->owl_users_table, "username", "id", "fUserID");
		//$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fUserID\" value=\"" . $oUser->iId . "\">\n" .
		"<input size = \"40\" type=\"text\" name=\"fUserName\" value=\"" . $oUser->getUserName() . "\">";
	}
	
}


// gets the updatebutton when a User is selected
function getUpdateButton($oUser) {
	global $default;
	if (!isset($oUser)) {
		return "Please select a User: ";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"submit\" /></td>\n";
	}
}

// gets the cancel button when a User is selected
function getCancelButton($oUser) {
	global $default;
	if (!isset($oUser)) {
		//return "<td align = right><a href=\"$default->rootUrl/control.php?action=UserManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=editUser\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}
?>