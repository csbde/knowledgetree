<?php
/**
* Presentation information for adding a group
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

// gets main page
function getPage($iGroupID, $iUnitID) {
    global $default;
    $oGroup = null;
    $oUnit = null;

    if (isset($iGroupID)) {
        $oGroup = Group::get($iGroupID);
        $heading = "<tr><td>Are you Sure you wish to assign the Group to the Unit?</td></tr>\n";
    } else {
        if (Permission::userIsUnitAdministrator()) {
            $heading = "<tr><td>Please Assign a Group to your Unit:</td></tr>\n";
        } else {
            $heading = "<tr><td>Please Assign a Group to a Unit:</td></tr>\n";
        }
    }
    
    if (isset($iUnitID)) {
        $oUnit = Unit::get($iUnitID);
    }

    $sToRender .= "<table width=\"600\">" . renderHeading("Assign Group to Unit") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= $heading;
    $sToRender .= "<br>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td>Group Name: </td><td>" . getGroupDisplay($oGroup) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Unit Name: </td><td>" . getUnitDisplay($oUnit) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td></td><td>" . getAssignButton($oGroup);
    $sToRender .= getCancelButton($oGroup);
    $sToRender .= "</td></table>\n";

    $sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {;\n";
	$sToRender .= "\tif (!(validRequired(theForm.fGroupID, 'Group'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";	
	$sToRender .= "return true;\n}\n";		
	$sToRender .= "-->\n</script>\n\n";

    return $sToRender;
}

// page when failure occurred
function getPageFail($iGroupID) {
    global $default;
    $oGroup = null;
    $oUnit = null;

    if (isset($iGroupID)) {
        $oGroup = Group::get($iGroupID);
    }
    // find otu which unit the group belogns to
    $iUnitID = GroupUnitLink::groupBelongsToUnit($iGroupID);
    // get the id of that unit
    $oUnit= Unit::get($iUnitID);

    $sToRender .= "<table width=\"600\">" . renderHeading("Assign Group to Unit") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Sorry, Assignment Failed</b></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>Group Already Belongs to A Unit\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Group Name: </td><td><b>" .  $oGroup->getName() . "</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Unit Name: </td><td><b>" . $oUnit->getName($iUnitID) . "</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=assignGroupToUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";

    $sToRender .= "</table>\n";


    return $sToRender;
}

// page that comes up when there is nothing selected
function getPageNotSelected() {
    global $default;

    $sToRender .= "<table width=\"600\">" . renderHeading("Assign Group to Unit") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Sorry, Selection Failed</b></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>Please select BOTH a Group AND a Unit</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=assignGroupToUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}

//if edition successful
function getPageSuccess() {
    global $default;

    $sToRender .= "<table width=\"600\">" . renderHeading("Assign Group to Unit") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Success!</b></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>The Group was successfully assigned to the specified Unit</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=assignGroupToUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}

// display the listbox initially ..then just display the text
function getGroupDisplay($oGroup) {
    global $default;

    if (!isset($oGroup)) {
        // only display unassigned groups
        $oPatternListBox = & new PatternListBox($default->owl_groups_table, "name", "id", "fGroupID");
        $oPatternListBox->setFromClause("LEFT OUTER JOIN $default->owl_groups_units_table GUL on ST.id=GUL.group_id");
        $oPatternListBox->setWhereClause("ISNULL(GUL.group_id) AND ST.name <> 'Anonymous' AND ST.name <> 'System Administrators'");
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $oGroup->iId . "\">\n" .
               $oGroup->getName();
    }

}

// display the listbox initially ..then just display the text
function getUnitDisplay($oUnit) {
    global $default;
    if (Permission::userIsUnitAdministrator()) {
        $oUnit = Unit::get(User::getUnitID($_SESSION["userID"]));
    }
    if (!isset($oUnit)) {
        $oPatternListBox = & new PatternListBox($default->owl_units_table, "name", "id", "fUnitID");
        return $oPatternListBox->render();            
    } else {
        return "<input type=\"hidden\" name=\"fUnitID\" value=\"" . $oUnit->iId . "\">\n" .
               $oUnit->getName();
    }

}

// show button after group selected
function getAssignButton($oGroup) {
    global $default;
    if (!isset($oGroup)) {
        return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" border=\"0\"/></td>\n";
    } else {
        return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/assign.gif\" value=\"submit\" border=\"0\"/></td>\n";
    }
}
// show button after group selected
function getCancelButton($oGroup) {
    global $default;
    return "<td align = right><a href=\"$default->rootUrl/control.php?action=groupManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";    
}


?>