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
	
    $sToRender .= renderHeading("Add User To System");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td>Select the User to add to the System:</td></tr>\n";
	$sToRender .= "<tr><td><select name=\"fName\">\n";
    foreach ($aResults as $sName => $aAttributes) {
        $sToRender .= "<option value=\"$sName\">$sName</option>";
    }
    $sToRender .= "</select></td></tr>\n";
    $sToRender .= "<tr><td></td><td align=\"right\"><input type=\"image\" src=\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" border=\"0\"/></td></tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

// get add pages
function getSearchPage($unitID) {

	global $default;
	
    $sToRender .= renderHeading("Search For A User To Add");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td colspan=\"2\">Please enter a VALID Username to search for in the text box below</td></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Username: </td><td><input size = \"40\" type=\"text\" name=\"fName\"> </td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td><td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"Submit\" border=\"0\"/></td>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
	
}

function getDetailsDBPage($suserName, $aAttributes) {

 	global $default;
    $sToRender .= renderHeading("Add User To System");
    foreach ($aAttributes as $sUserName => $aUserAttributes) {
        $sToRender .= "<table>\n";
        $sToRender .= "<tr><td><b>User Details: </b></td></tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Username: </td><td><input size = \"40\" type=\"text\" name=\"fUsername\" value = \"". $aUserAttributes["username"]. "\"> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Password: </td><td><input size = \"40\" type=\"password\" name=\"fPassword\"></td>\n";
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
        $sToRender .= "<td>Email Notification: </td><td><input type=\"checkbox\" name=\"fEmailNotification\" value=\"" . $aUserAttributes["email_notification"] . "\" " . ($aUserAttributes["email_notification"] == "1" ? "checked" : "") . "\"> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Sms Notification: </td><td><input type=\"checkbox\" name=\"fSmsNotification\" value=\"" . $aUserAttributes["sms_notification"] . "\" " . ($aUserAttributes["sms_notification"] == "1" ? "checked" : "") . "> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td colspan=\"2\" align=\"right\">" . getAddButton() . getCancelButton() . "</td>";
        $sToRender .= "</tr>\n";
        $sToRender .= "</table>\n";
	}
	return $sToRender;
	
}

function getDetailsLDAPPage($suserName, $aAttributes, $sUserIdentifier) {
 	global $default;
 	
    $sToRender .= renderHeading("Add User To System");

	foreach ($aAttributes as $sUserName => $aUserAttributes) {
        $sToRender .= "<table>\n";
        $sToRender .= "<tr><td><b>User Details: </b></td></tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>LDAP DN: </td><td><input " . ($default->bNN4 ? "onblur=\"disable(document.MainForm.fLdap)" : "DISABLED") . " size = \"40\" type=\"text\" name=\"fLdap\" value = \"". $aUserAttributes["dn"]. "\"> </td>\n";
        $sToRender .= "<input type=\"hidden\" name=\"fLdap\" value = \"". $aUserAttributes["dn"]. "\">\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Username: </td><td><input disabled size = \"40\" type=\"text\" name=\"fUsername\" value = \"" . $aUserAttributes[$sUserIdentifier] ."\"> </td>\n";
        $sToRender .= "<input type=\"hidden\" name=\"fUsername\" value = \"" . $aUserAttributes[$sUserIdentifier] ."\">\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Name: </td><td><input size = \"40\" type=\"text\" name=\"fName\" value = \"" . $aUserAttributes["givenname"] ." " . $aUserAttributes["sn"] ."\"> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Email: </td><td><input " . ((strlen($aUserAttributes["mail"]) > 0) ? "disabled " : "") . "size = \"40\" type=\"text\" name=\"fEmail\" value = \"" . $aUserAttributes["mail"] ."\"> </td>\n";
        $sToRender .= "<input type=\"hidden\" name=\"fEmail\" value = \"" . $aUserAttributes["mail"] ."\">\n";    
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Mobile Number: </td><td><input size = \"40\" type=\"text\" name=\"fMobile\" value = \"" . $aUserAttributes["mobile"] ."\"> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";        
        $sToRender .= "<td>Email Notification: </td><td><input type=\"checkbox\" name=\"fEmailNotification\"/ CHECKED> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Sms Notification: </td><td><input type=\"checkbox\" name=\"fSmsNotification\"/> </td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td colspan=\"2\" align=\"right\">" . getAddButton() . getCancelButton() . "</td>";
        $sToRender .= "</tr>\n";
        $sToRender .= "</table>\n";
    }

	return $sToRender;
}


// if edition is successful print otu success page
function getPageSuccess() {	
	global $default;
			
	$sToRender .= renderHeading("Add User To System");
	$sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Addition Successful!</b></td></tr>\n";
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
			
	$sToRender .= renderHeading("Add User To System");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>Addition Successful!</b></td></tr>\n";
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
			
	$sToRender .= renderHeading("Add User To System");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>Username not found!</b></td></tr>\n";
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
		return "<input type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" value=\"submit\" border=\"0\"/>\n";
}

// show cancel button
function getCancelButton() {
	global $default;
	return "<a href=\"$default->rootUrl/control.php?action=addUser\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a>\n";
	
}


?>
