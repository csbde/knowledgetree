<?php

/**
* Presentation information for Deleting a Unit 
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// gets the delete stuff
function getDeletePage($iUnitID) {
    global $default;
    $oUnit = null;
    if (isset($iUnitID)) {
        $oUnit = Unit::get($iUnitID);
        $sHeading .= "<b>Are you sure you wish to Remove this Unit?:</b>\n";
    } else {
        $sHeading .= "Please select a Unit: ";
    }

    $sToRender .= "<table width=\"600\">" . renderHeading("Remove Unit") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td>$sHeading</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Unit Name: " . getUnitDisplay($oUnit) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";

    $sToRender .= getDeleteButton($oUnit);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {

    global $default;

    $sToRender .= "<table width=\"600\">" . renderHeading("Remove Unit") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>Unit SuccessFully Removed!</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=removeUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get deletion failed page
function getDeleteFailPage() {

    global $default;

    $sToRender .= "<table width=\"600\">" . renderHeading("Remove Unit") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>Deletion Failed!</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>Please Ensure that the unit has no groups belonging to it</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=removeUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td></tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}


function getDeleteConfirmedPage($iUnitID,$iOrgID) {
    global $default;
    $oUnit = null;
    if (isset($iUnitID)) {
        $oUnit = Unit::get($iUnitID);
    }

    if (isset($iOrgID)) {
        $oOrg = Organisation::get($iOrgID);
    }

    $sToRender .= "<table width=\"600\">" . renderHeading("Remove Unit") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Are you sure you wish to Remove this Unit?:</b></td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Unit Name: " . getUnitDisplay($oUnit) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Organisation Name: " . getOrgDisplay($oOrg) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= getDeleteButton($oUnit);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}


// show listbox...or the text name
function getUnitDisplay($oUnit) {
    global $default;
    if (!isset($oUnit)) {
        $oPatternListBox = & new PatternListBox($default->owl_units_table, "name", "id", "fUnitID");
        $oPatternListBox->setIncludeDefaultValue(true);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fUnitID\" value=\"" . $oUnit->iId . "\">\n" .
               "<b>" . $oUnit->getName() . "</b>";
    }

}

// show listbox...or the text name
function getOrgDisplay($oOrg) {
    global $default;
    if (isset($oOrg)) {
        return "<input type=\"hidden\" name=\"fOrgID\" value=\"" . $oOrg->iId . "\">\n" .
               "<b>" . $oOrg->getName() . "</b>";
    }
}


// show delete button
function getDeleteButton($oUnit) {
    global $default;
    if (isset($oUnit)) {
        return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" border=\"0\"/></td>\n".
               "<td align = right><a href=\"$default->rootUrl/control.php?action=removeUnit\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
    }
}


// show cancel button
function getCancelButton($oUnit) {
    global $default;
    if (!isset($oUnit)) {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=unitManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
    } else {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=removeUnit\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
    }
}

?>
