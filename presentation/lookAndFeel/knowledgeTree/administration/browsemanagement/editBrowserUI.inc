<?php
/**
 * $Id$
 *
 * Copyright (c) 2004 Jam Warehouse http://www.jamwarehouse.com
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
 * @package administration.groupmanagement
 */

// Create two listbox
function renderBrowsePicker() {

	$sToRender .= renderHeading(_("Browse Management"));

	$sToRender .= "<table width=\"600\" border=0>";

	// Headings
	$sToRender .= "<tr>";
	$sToRender .= "<td><strong>Allocated in Browser</strong></td>";
	$sToRender .= "<td>&nbsp;</td>";
	$sToRender .= "<td>&nbsp;</td>";
 	$sToRender .= "<td><strong>Available Criteria</strong></td>";
	$sToRender .= "</tr>";

	// Initialise structure
	$aInfo = InitCriteria();

	// Select Boxes	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td valign=\"top\" width=1%>\n";
	$sToRender .= "<select name=\"browseSelect\" size=\"10\" multiple>\n";
	// retrieve users' groups
	$sToRender .= getSelected($aInfo);
	$sToRender .= "</select></td>\n";
    $sToRender .= "<td>";
	$sToRender .= "<input TYPE=\"button\" NAME=\"up\" style=\"width:10px\" VALUE=\"^\" ONCLICK=\"moveOptionUp(optBrowse.left); optBrowse.update()\"> <br />";
	$sToRender .= "<input TYPE=\"button\" NAME=\"down\" style=\"width:10px\" VALUE=\"v\" ONCLICK=\"moveOptionDown(optBrowse.left); optBrowse.update()\">";
    $sToRender .= "</td>\n";

	// Move buttons
	$sToRender .= "<td align=\"center\">";	
	$sToRender .= "<input TYPE=\"button\" NAME=\"right\" style=\"width:60px\" VALUE=\"- &gt;&gt;\" ONCLICK=\"optBrowse.transferRight()\">";
	$sToRender .= "<BR>";
	$sToRender .= "<input TYPE=\"button\" NAME=\"left\" style=\"width:60px\" VALUE=\"&lt;&lt; +\" ONCLICK=\"optBrowse.transferLeft()\">";	
	$sToRender .= "</td>";
	
	$sToRender .= "<td valign=\"top\" width=1%>\n";	
	$sToRender .= "<select name=\"chosenCriteria\" size=\"10\" multiple>\n";
	// retrieve other groups
	$sToRender .= getAvailable($aInfo);
	$sToRender .= "</select></td>";
	$sToRender .= "</tr>";

	// Assign/Cancel button
	$sToRender .= "<tr><td align=\"right\" colspan=\"4\">";	
    $sToRender .= "<input type=\"image\" src=\"". KTHtml::getAssignButton() . "\" border=\"0\" />";
	$sToRender .= "<a href=\"" . $_SERVER["PHP_SELF"] . "?fGroupID=" . $iGroupID . "\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"/></a>\n";
	$sToRender .= "</td></tr>";

	
	// Show current settings
	$sBgColor = "#F5F6EE";
	$sToRender .= "<tr>";
	$sToRender .= "<td colspan=\"3\"><strong>" . _("Current Users") . "</strong><BR>";
	$sToRender .= "<table cellspacing=1 border=0>";
	for ($i=0; $i < count($aInfo[0]); $i++) {
		$sName = criterionGet($aInfo[0][$i]);
		$sToRender .=  "<tr><td>" . ($i+1) . "</td><td bgcolor=$sBgColor>". $sName ."</td></tr>\n";
	}
	$sToRender .= "</table></td></tr>";

	$sToRender .= "</table>";
	
	$sToRender .= "<input type=\"hidden\" name=\"browseNewLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"browseNewRight\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"browseRemovedLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"browseRemovedRight\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"browseAddedLeft\"/><br>";
	$sToRender .= "<input type=\"hidden\" name=\"browseAddedRight\"/><br>";	
	return $sToRender;
}

$aBaseCriteria = array(
    -1 => _('Title'),
    -2 => _('Description'),
    -3 => _('Creator'),
    -4 => _('Date Created'),
    -5 => _('Document Type'),
    -6 => _('ID'),
);

function criterionGet($iCriteriaID) {
    global $default;
    global $aBaseCriteria;
    if ($iCriteriaID < 0) {
        $sName = $aBaseCriteria[$iCriteriaID];
    } else {
        $aQuery = array("SELECT name FROM $default->document_fields_table WHERE id = ?", array($iCriteriaID)); /*ok*/
        $sName = DBUtil::getOneResultKey($aQuery, 'name');
    }
    return $sName;
}

/* Initialise some structures to build ui
 * Return an array of:
 *		Current Users assigned to Group
 *		UnitAdmin Manageable User Ids
*/
function InitCriteria() {
    global $default;
    global $aBaseCriteria;

    $aAllCriteriaIDs = array_keys($aBaseCriteria);
    $aQuery = array("SELECT id FROM $default->document_fields_table", array()); /*ok*/
    $aIDs = DBUtil::getResultArrayKey($aQuery, 'id');
    $aAllCriteriaIDs = array_merge($aAllCriteriaIDs, $aIDs);

    $aQuery = array("SELECT criteria_id FROM browse_criteria ORDER BY precedence", array()); /*ok*/
    $aSelectedCriteriaIDs = DBUtil::getResultArrayKey($aQuery, 'criteria_id');
    
	return array($aSelectedCriteriaIDs, $aAllCriteriaIDs);
}


/* Get all Users in a Group */
function getSelected($aInfo) {
    global $default;
    global $aBaseCriteria;

	foreach ($aInfo[0] as $iCriteriaID) {
        if ($iCriteriaID < 0) {
            $sName = $aBaseCriteria[$iCriteriaID];
        } else {
            $aQuery = array("SELECT name FROM $default->document_fields_table WHERE id = ?", array($iCriteriaID)); /*ok*/
            $sName = DBUtil::getOneResultKey($aQuery, 'name');
        }
		$sToRender .= "<OPTION value=\"" . $iCriteriaID . "\" onDblClick=\"optBrowse.transferRight()\" >\n";	
		$sToRender .= $sName;
		$sToRender .= "</OPTION>\n";
	}

	return $sToRender;
}


// Get other Users
function getAvailable($aInfo) {
    global $default;
    global $aBaseCriteria;

	$aOtherUserIDs = array();

	$aOtherUserIDs = array_diff($aInfo[1], $aInfo[0]);

	foreach ($aOtherUserIDs as $iCriteriaID) {
        if ($iCriteriaID < 0) {
            $sName = $aBaseCriteria[$iCriteriaID];
        } else {
            $aQuery = array("SELECT name FROM $default->document_fields_table WHERE id = ?", array($iCriteriaID)); /*ok*/
            $sName = DBUtil::getOneResultKey($aQuery, 'name');
        }
		$sToRender .= "<OPTION value=\"" . $iCriteriaID . "\" onDblClick=\"optBrowse.transferRight()\" >\n";	
		$sToRender .= $sName;
		$sToRender .= "</OPTION>\n";
	}

	return $sToRender;
}



// get page for removal
function getPage($iGroupID) {
    global $default;
    $oUser = null;
    $oGroup = null;

    // change headings if Group selected
    if (isset($iGroupID)) {
        $oGroup = Group::get($iGroupID);
    } else {
        $heading = "<tr><td>" . _("Please select a Group") . ":</td></tr>\n";
    }

    $sToRender .= renderHeading(_("Edit User's Groups"));
    $sToRender .= "<table>\n";
    $sToRender .= $heading;
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td>" . _("Group Name") . ": " . getGroupDisplay($oGroup) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";    
    $sToRender .= "</td></table>\n";
   
    return $sToRender;
}

// page that comes up when there is nothing selected
function getPageNotSelected() {
	global $default;
 
	$sToRender .= renderHeading(_("Edit Users's Group"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . _("Sorry, Selection Failed") . "</b></td></tr>\n";
	$sToRender .= "<tr><td>" . _("Please select a Group") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listGroups\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";

	return $sToRender;
}


// show listbox of Users belonging to Users_Groups_link table
function getGroupDisplay($oGroup) {
    global $default;
    if (!isset($oGroup)) {
        $oPatternListBox = & new PatternListBox($default->groups_table, "name", "id", "fGroupID");
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $oGroup->getID() . "\">\n" .
               $oGroup->getName();
    }
}

?>
