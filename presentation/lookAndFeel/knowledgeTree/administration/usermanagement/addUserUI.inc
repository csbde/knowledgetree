<?php
/**
* Presentation information for adding a unit
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

// display multiple users
function getSelectUserPage($aResults) {
	global $default;
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Select the User to add to the System:</b>\n";
	$sToRender .= "<table>\n";
    $sToRender .= "<br></br>\n";
	$sToRender .= "<tr><td><select name=\"fName\">\n";
    foreach ($aResults as $sName => $aAttributes) {
        $sToRender .= "<option value=\"$sName\">$sName</option>";
    }
    $sToRender .= "</select></td></tr>\n";
    $sToRender .= "<tr><td></td><td align=\"right\"><input type=\"image\" src=\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\"/></td></tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

// get add pages
function getAddPage($unitID) {

	global $default;
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Add a New User to the System:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr>Please enter a VALID Username to search for in the text box below</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Username: </td><td><input size = \"40\" type=\"text\" name=\"fName\"> </td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td><td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"Submit\" /></td>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
	
}

function getDetailsDBPage($suserName, $aAttributes) {

 	global $default;
    foreach ($aAttributes as $sUserName => $aUserAttributes) {
        $sToRender .= "<br></br>\n";
        $sToRender .= "<b>User Details: </b>\n";
        $sToRender .= "<table>\n";
        $sToRender .= "<br></br>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Username: </td><td><input disabled size = \"40\" type=\"text\" name=\"fUsername\" value = \"". $aUserAttributes["username"]. "\"> </td>\n";
        $sToRender .= "<input type=\"hidden\" name=\"fUsername\" value = \"" . $aUserAttributes["username"] ."\">\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Name: </td><td><input size = \"40\" type=\"text\" name=\"fName\" value = \"" . $aUserAttributes["name"] ."\"> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Email: </td><td><input size = \"40\" type=\"text\" name=\"fEmail\" value = \"" . $aUserAttributes["email"] ."\"> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Mobile: </td><td><input size = \"40\" type=\"text\" name=\"fMobile\" value = \"" . $aUserAttributes["mobile"] ."\"> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Email Notification: </td><td><input size = \"40\" type=\"text\" name=\"fEmailNotificaition\" value = \"" . $aUserAttributes["email_notification"] ."\"> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Sms Notification: </td><td><input size = \"40\" type=\"text\" name=\"fSmsNotification\" value = \"" . $aUserAttributes["sms_notification"] ."\"> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td></td><td>" . getAddButton();	
        $sToRender .= "<td></td><td>" . getCancelButton();
        $sToRender .= "</tr>\n";
        $sToRender .= "</table>\n";
	}
	return $sToRender;
	
}

function getDetailsLDAPPage($suserName, $aAttributes) {

 	global $default;
	foreach ($aAttributes as $sUserName => $aUserAttributes) {
        $sToRender .= "<br></br>\n";
        $sToRender .= "<b>User Details: </b>\n";
        $sToRender .= "<table>\n";
        $sToRender .= "<br></br>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>LDAP DN: </td><td><input disabled size = \"40\" type=\"text\" name=\"fLdap\" value = \"". $aUserAttributes["dn"]. "\"> </td>\n";
        $sToRender .= "<input type=\"hidden\" name=\"fLdap\" value = \"". $aUserAttributes["dn"]. "\">\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Username: </td><td><input disabled size = \"40\" type=\"text\" name=\"fUsername\" value = \"" . $aUserAttributes["uid"] ."\"> </td>\n";
        $sToRender .= "<input type=\"hidden\" name=\"fUsername\" value = \"" . $aUserAttributes["uid"] ."\">\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Name: </td><td><input size = \"40\" type=\"text\" name=\"fName\" value = \"" . $aUserAttributes["givenname"] ." " . $aUserAttributes["sn"] ."\"> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Email: </td><td><input disabled size = \"40\" type=\"text\" name=\"fEmail\" value = \"" . $aUserAttributes["mail"] ."\"> </td>\n";
        $sToRender .= "<input type=\"hidden\" name=\"fEmail\" value = \"" . $aUserAttributes["mail"] ."\">\n";    
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Mobile Number: </td><td><input size = \"40\" type=\"text\" name=\"fMobile\" value = \"" . $aUserAttributes["mobile"] ."\"> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td></td><td>" . getAddButton();	
        $sToRender .= "<td></td><td>" . getCancelButton();
        $sToRender .= "</tr>\n";
        $sToRender .= "</table>\n";
    }
	
	return $sToRender;
	
}


// if edition is successful print otu success page
function getPageSuccess() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Addition Successful!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The User was successfully added to the System</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUser\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

// if edition is successful print otu success page
function getPageFail() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Addition Unsuccessful</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The User was not added. Username Already exists!</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUser\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// if edition is successful print otu success page
function getPageUsernameNotFound() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Username not found!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>Please ensure that the username that you entered Exists!</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUser\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// show delete button or select button
function getAddButton() {
	global $default;
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" value=\"submit\" /></td>\n";
}

// show cancel button
function getCancelButton() {
	global $default;
	return "<td align = right><a href=\"$default->rootUrl/control.php?action=addUser\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	
}


?>