<?php
/**
* Presentation information for renaming a User
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
function renderHeading() {
    global $default;
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">Preferences</font></th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    return $sToRender;
}

function getUserDetailsPage($iUserID) {
	
	global $default;
	$oUser = null;

	$sQuery =  "SELECT * FROM $default->owl_users_table WHERE id = $iUserID";
	$aDisplayColumns = array("name","email", "mobile", "email_notification", "sms_notification"); 
	$aStoreColumns = array("name","email", "mobile", "email_notification", "sms_notification") ;
	$aColumnNames = array("Name: ", "Email Address: ", "Mobile Number: ", "Email Notification: ", "Sms Notification: ");
	$aDisplayColumnTypes = array(1,1,1,2,2);
	$aDatabaseColumnTypes = array(1,1,1,2,2);
	
	// get list of User properties...name...text field...is_Sys_admin and is_unit_admin..checkboxes
	$oPattern = & new PatternEditableListFromQuery($sQuery, $default->owl_users_table, $aDisplayColumns, $aStoreColumns, $aColumnNames, $aDisplayColumnTypes, $aDatabaseColumnTypes) ;
	$oPattern->setUniqueName("userEdit");
	$oPattern->setColumnsRequired(array(1,0,0,0,0));
	
	$sToRender .= "<table width=\"600\">\n";
    $sToRender .= renderHeading();
	$sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr/><tr/><tr><td><b>Edit User Properties:</b></td></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPattern->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	
	$sToRender .= "<td><input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"Update\" border=\"0\"/></td>\n";	
	$sToRender .= "</table>\n";
	return $sToRender;
}

// if edition is successful print otu success page
function getEditPageSuccess() {	
	global $default;
			
	$sToRender .= "<table width=\"600\">\n";
    $sToRender .= renderHeading();
	$sToRender .= "<tr/><tr/><tr><td><b>Success!</b></td></tr>\n";
	$sToRender .= "<tr><td>The User was successfully modified</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=preferences\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}


// gets the page if edition fails
function getEditPageFail() {	
	global $default;
			
	$sToRender .= "<table>\n";
    $sToRender .= renderHeading();
	$sToRender .= "<tr/><tr/>\n";
	$sToRender .= "<tr><td>User modification Failed!</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align=\"right\"><a href=\"$default->rootUrl/control.php?action=preferences\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
