<?php

/**
* Presentation information for Deleting a Unit 
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getConfirmDeletePage($oUnit, $oOrg) {
    global $default;

    $sToRender .= renderHeading("Remove Unit");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Are you sure you wish to Remove this Unit:</b></td></tr>\n";
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

function getStatusPage($sHeading, $sText = "") {

    global $default;

    $sToRender .= renderHeading("Remove Unit");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>$sHeading</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td>$sText</td></tr>\n";
    $sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=listUnits\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td></tr>\n";
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
    } else {
    	return "<b>* Not Assigned to an Organisation<b>";
    }
}


// show delete button
function getDeleteButton($oUnit) {
    global $default;
    if (isset($oUnit)) {
        return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" border=\"0\"/></td>\n".
               "<td align = right><a href=\"$default->rootUrl/control.php?action=listUnits\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
    }
}


// show cancel button
function getCancelButton($oUnit) {
    global $default;
    if (!isset($oUnit)) {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=unitManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
    } else {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=listUnits\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
    }
}

?>
