<?php

/**
* Presentation information for Deleting a User 
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// gets the delete stuff
function getDeletePage($iUserID) {
    global $default;
    $oUser = null;
    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
        $sHeading .= "<tr><td><b>Are you sure you wish to Remove this User From the System?:</b></td></tr>\n";
    } else {
        $sHeading .= "<tr><td><b>Please select a User:</b></td></tr>\n";
    }

    $sToRender .= renderHeading("Remove User");
    $sToRender .= "<table>\n";
    $sToRender .= $sHeading;
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>User Name: " . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {

    global $default;

    $sToRender .= renderHeading("Remove User");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>User SuccessFully Removed!</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=listUsers\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get deletion failed page
function getDeleteFailPage() {

    global $default;

    $sToRender .= renderHeading("Remove User");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>Deletion Failed!</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=listUsers\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td></tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}


function getDeleteConfirmedPage($iUserID) {
    global $default;
    $oUser = null;
    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
    }

    $sToRender .= renderHeading("Remove User");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Are you sure you wish to Remove this User?:</b></td></tr>";
    $sToRender .= "<tr><td>Note: This User will be Removed from ALL Groups as well\n</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>User Name: " . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .=  getUserDetails($oUser). "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td>" . getDeleteButton($oUser);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}


// show listbox...or the text name
function getUserDisplay($oUser) {
    global $default;
    if (!isset($oUser)) {
        $oPatternListBox = & new PatternListBox($default->owl_users_table, "username", "id", "fUserID");
        $oPatternListBox->setIncludeDefaultValue(true);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fUserID\" value=\"" . $oUser->iId . "\">\n" .
               "<td><b>" . $oUser->getUserName() . "</b>";
    }
}

// show listbox...or the text name
function getUserDetails($oUser) {
    global $default;
    if (!isset($oUser)) {
        return "";
    } else {
        return
            "<tr>\n".
            "<td>Name: </td><td>". $oUser->getName() . "</td>\n" .
            "</tr>\n".
            "<tr>\n".
            "<td>Email Address: </td><td>". $oUser->getEmail() . "</td>\n" .
            "</tr>\n".
            "<tr>\n".
            "<td>Mobile Number: </td><td>". $oUser->getMobile() . "</td>\n" .
            "</tr>\n";
    }
}


// show delete button
function getDeleteButton($oUser) {
    global $default;
    return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" border=\"0\"/></td>\n".
			"<td><a href=\"$default->rootUrl/control.php?action=listUsers\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a></td>\n";
}


// show cancel button
function getCancelButton($oUser) {
    global $default;
    if (!isset($oUser)) {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=userAdministration\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
    }
    else {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=removeUser\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";    
    }
}

?>
