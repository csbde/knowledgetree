<?php

// if its the manual edit page..then display normally
function getSelectWebMasterPage($iUserID) {
    global $default;
    $oUser = null;
    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
    }

    $sToRender .= renderHeading("Edit Website");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td>Please Select a WebMaster</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>User: </td><td>" . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// if its the manual edit page..then display normally
function getSelectWebSitePage($iUserID, $iWebSiteID) {
    global $default;
    $oUser = null;
    $oWebSite = null;

    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
    }

    if (isset($iWebSiteID)) {
        $oWebSite = WebSite::get($iWebSiteID);
    }

    $sToRender .= renderHeading("Edit Website");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td>Please Select the WebSite you wish to Edit:</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>User: " . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>WebSite: " . getWebSiteDisplay($oWebSite,$iUserID) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>" . getCancelButton($oUser);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// if its the manual edit page..then display normally
function getEditWebSitePage($iUserID, $iWebSiteID) {
    global $default;
    $oUser = null;
    $oWebSite = null;

    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
    }

    if (isset($iWebSiteID)) {
        $oWebSite = WebSite::get($iWebSiteID);
    }

    $sToRender .= renderHeading("Edit Website");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td>Enter WebSite Properties</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Webmaster:</td><td>" . getUserChangedDisplay($oUser) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>WebSite Name: </td><td>" . getWebSiteDisplay($oWebSite,$iUserID) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>WebSite URL: </td><td>" . getWebSiteUrl($oWebSite,$iUserID) . "</td>\n";
    $sToRender .= "</tr>\n";
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



// if edition is successful print otu success page
function getSuccessPage() {
    global $default;

    $sToRender .= renderHeading("Edit Website");
    $sToRender .= "<table>\n";
    $sToRender .= "<b>Success!</b>\n";
    $sToRender .= "<table>\n";
    $sToRender .= "<br></br>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>The WebSite was successfully edited</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editWebSite\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}

// if edition is successful print otu success page
function getFailPage() {
    global $default;

    $sToRender .= renderHeading("Edit Website");
    $sToRender .= "<table>\n";
    $sToRender .= "<b>FAILURE!</b>\n";
    $sToRender .= "<table>\n";
    $sToRender .= "<br></br>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>The WebSite was not updated</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editWebSite\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}


// if edition is successful print otu success page
function getTextPage() {
    global $default;

    $sToRender .= renderHeading("Edit Website");
    $sToRender .= "<table>\n";
    $sToRender .= "<b>FAILURE!</b>\n";
    $sToRender .= "<table>\n";
    $sToRender .= "<br></br>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>Please Enter a Value in the Text Box</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editWebSite\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}

// gets the updatebutton when a User is selected
function getUpdateButton($oUser) {
    global $default;
    if (isset($oUser)) {
        return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"submit\" border=\"0\"/></td>\n";
    }
}

// gets the cancel button when a User is selected
function getCancelButton($oUser) {
    global $default;
    if (isset($oUser)) {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=editWebSite\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
    }
}

// show the list box when no User selected
// then shows a text box with the User's name
function getUserDisplay($oUser) {
    global $default;
    if (!isset($oUser)) {

        //$sWhereClause = " ST.has_lookup = 1" ;
        $oPatternListBox = & new PatternListBox($default->owl_users_table, "username", "id", "fUserID");

        $sFromClause = "," . $default->owl_web_sites_table ." aS WB ";
        $sWhereClause = "ST.id = WB.web_master_id" ;
        $oPatternListBox->setFromClause($sFromClause);
        $oPatternListBox->setWhereClause($sWhereClause);
        //$oPatternListBox->setIncludeDefaultValue(true);
        //$oPatternListBox->setWhereClause($sWhereClause);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fUserID\" value=\"" . $oUser->iId . "\">\n" .
               $oUser->getUserName();
    }
}

// show the list box when no User selected
// then shows a text box with the User's name
function getUserChangedDisplay($oUser) {
    global $default;

    $oPatternListBox = & new PatternListBox($default->owl_users_table, "username", "id", "fUserID");
    $oPatternListBox->setSelectedValue($oUser->getID());
    $oPatternListBox->setIncludeDefaultValue(false) ;

    return $oPatternListBox->render();
}

// show the list box when no User selected
// then shows a text box with the User's name
function getWebSiteDisplay($oWebSite,$iUserID) {
    global $default;
    if (!isset($oWebSite)) {
        $sWhereClause = " ST.web_master_id = " . $iUserID;
        $oPatternListBox = & new PatternListBox($default->owl_web_sites_table, "web_site_name", "id", "fWebSiteID");
        $oPatternListBox->setWhereClause($sWhereClause);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fWebSiteID\" value=\"" . $oWebSite->iId . "\">\n" .
               "<input type=\"text\" name=\"fWebSiteName\" value=\"" . $oWebSite->getWebSiteName() . "\">\n" .
               "<input type=\"hidden\" name=\"fOldWebSiteName\" value=\"" . $oWebSite->getWebSiteName() . "\">\n" ;
    }
}



// show the list box when no User selected
// then shows a text box with the User's name
function getWebSiteUrl($oWebSite,$iUserID) {
    global $default;
    if (!isset($oWebSite)) {
        return "";
    } else {
        return "<input type=\"text\" name=\"fWebSiteURL\" value=\"" . $oWebSite->getWebSiteURL() . "\">\n" .
               "<input type=\"hidden\" name=\"fOldWebSiteURL\" value=\"" . $oWebSite->getWebSiteURL() . "\">\n" ;
    }
}
?>