<?php

function getEditWebSitePage($oWebSite) {
    global $default;
    
    $oUser = User::get($oWebSite->getWebMasterID());

    $sToRender .= renderHeading("Edit Website");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td>Update WebSite Properties</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Webmaster:</td><td>" . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>WebSite Name: </td><td>" . getWebSiteName($oWebSite) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>WebSite URL: </td><td>" . getWebSiteUrl($oWebSite,$iUserID) . "</td>\n";
    $sToRender .= "</tr>\n";
	$sToRender .= "<input type=\"hidden\" name=\"fWebSiteID\" value=\"" . $oWebSite->getID() . "\">\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td>" . getUpdateButton($oUser);
    $sToRender .= getCancelButton($oUser);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    $sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";
	$sToRender .= "\tif (!(validRequired(theForm.fWebSiteName, 'WebSite Name'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";        
	$sToRender .= "\tif (!(validRequired(theForm.fWebSiteURL, 'WebSite URL'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";
    
    return $sToRender;
}

// gets the cancel button when a User is selected
function getCancelButton($oUser) {
    global $default;
    return "<td align = right><a href=\"$default->rootUrl/control.php?action=listWebsites\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
}

// show the list box when no User selected
// then shows a text box with the User's name
function getUserDisplay($oUser) {
    global $default;
    $oPatternListBox = & new PatternListBox($default->owl_users_table, "username", "id", "fWebMasterID");
    if ($oUser) {
		$oPatternListBox->setSelectedValue($oUser->getID());
    }
    return $oPatternListBox->render();
}


function getWebSiteName($oWebSite) {
    return "<input type=\"text\" name=\"fWebSiteName\" value=\"" . $oWebSite->getWebSiteName() . "\">\n";
}

// show the list box when no User selected
// then shows a text box with the User's name
function getWebSiteUrl($oWebSite) {
	return "<input type=\"text\" name=\"fWebSiteURL\" value=\"" . $oWebSite->getWebSiteURL() . "\">\n";
}

function getSuccessPage() {
	return statusPage("Edit Website", "Website update success!", "The website has been updated.", "listWebsites");
}

function getFailPage() {
	return statusPage("Edit Website", "Website update failure!", "There was an error updating the website, please try again later.", "listWebsites");
    return $sToRender;
}

function getUpdateButton($oUser) {
    global $default;
    if (isset($oUser)) {
        return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"submit\" border=\"0\"/></td>\n";
    }
}
?>