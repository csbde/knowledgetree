<?php
/**
 * $Id$
 *
 * Remove a unit UI functions.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Mukhtar Dharsey, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.unitmanagement
 */

function getConfirmDeletePage($oUnit, $oOrg) {
    global $default;

    $sToRender .= renderHeading("Remove Unit");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>" . _("Are you sure you wish to Remove this Unit:") . "</b></td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Unit Name") . ": " . getUnitDisplay($oUnit) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Organisation Name") . ": " . getOrgDisplay($oOrg) . "</td>\n";
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
        $oPatternListBox = & new PatternListBox($default->units_table, "name", "id", "fUnitID");
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
    	return "<b>* " . _("Not Assigned to an Organisation") . "<b>";
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
