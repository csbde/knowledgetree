<?php

/**
* Presentation information for Deleting a Link 
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// gets the delete stuff
function getDeletePage($iLinkID) {
    global $default;
    $oLink = null;
    if (isset($iLinkID)) {
        $oLink = Link::get($iLinkID);
        $sHeading .= "<b>Are you sure you wish to Remove this Link?:</b>\n";
    } else {
        $sHeading .= "Please select a Link:";
    }

    $sToRender .= renderHeading("Remove QuickLink");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td colspan=\"2\">$sHeading</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Link Name: </td><td>" . getLinkDisplay($oLink) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . getUrl($oLink) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . getRank($oLink) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td/>" . getDeleteButton($oLink) . "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {

    global $default;

    $sToRender .= renderHeading("Remove QuickLink");
    $sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>Link SuccessFully Removed!</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=listLinks\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get deletion failed page
function getDeleteFailPage() {

    global $default;

    $sToRender .= renderHeading("Remove QuickLink");
    $sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>Deletion Failed!</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>Please Ensure that The Link has been Removed from any Unit it belongs to</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=listLinks\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td></tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// show listbox...or the text name
function getLinkDisplay($oLink) {
    global $default;
    if (!isset($oLink)) {
        $oPatternListBox = & new PatternListBox($default->owl_links_table, "name", "id", "fLinkID");
        $oPatternListBox->setIncludeDefaultValue(true);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fLinkID\" value=\"" . $oLink->iId . "\">\n" .
               "<b>" . $oLink->getName() . "</b>";
    }
}

// show check box
function getUrl($oLink) {
    if (!isset($oLink)) {
        return "";
    } else {
        return  "URL: </td><td>" . $oLink->getUrl();
    }
}

// show text box
function getRank($oLink) {
    if (!isset($oLink)) {
        return "";
    } else {
        return  "Rank: </td><td>" .$oLink->getRank();
    }
}

// show delete button
function getDeleteButton($oLink) {
    global $default;
    if (isset($oLink)) {
        return "<td colspan=\"2\" align=\"right\"><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" border=\"0\"/>\n".
               "<a href=\"$default->rootUrl/control.php?action=listLinks\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
    }
}
?>
