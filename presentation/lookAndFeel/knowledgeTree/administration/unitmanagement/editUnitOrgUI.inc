<?php
/**
 * $Id$
 *
 * Edit unit-organisation mapping UI functions.
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

function getOrgPage($iUnitID = null){
	global $default;
	
	$aUnitOrgLink = UnitOrganisationLink::getList("WHERE unit_id = $iUnitID");
	
	if (count($aUnitOrgLink) > 0 ){
		$oLink = $aUnitOrgLink[0];
	}
			
	$sBgColor = "#F5F6EE";
	
	$sToRender .= "<table width=\"600\" border=0>";
    $sToRender .= "<tr><td width=100%></td><td>";
    $sToRender .= "<a href=\"$default->rootUrl/control.php?action=listUnits\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a>";
    $sToRender .= "</td></tr>\n";
    $sToRender .= "</table>";    
    
	
	if ($oLink) {
		$oOrg = Organisation::get($oLink->getOrgID());
		$sOrgName = $oOrg->getName();
		$bNoOrg = false;
	} else { 
		$sOrgName = "* " . _("No assigned Organisation");
		$bNoOrg = true;
	}
        
    $sToRender .= "<table width=100% border=0>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td width=300 valign=top>\n";    
    
	    $sToRender .= "<table width=100% border=0>\n";
	    $sToRender .= "<tr>\n";
	    $sToRender .= "<td><b>\n";
	    $sToRender .= _("Current Organisation") . "\n";
	    $sToRender .= "</b></td>\n";
	    $sToRender .= "</tr>\n";    
	    $sToRender .= "<tr>\n";
	    $sToRender .= "<td bgcolor=$sBgColor>\n";
	  
	    $sToRender .= $sOrgName;
	  
	    $sToRender .= "</td>\n";    
	    $sToRender .= "</tr>\n";    
	    $sToRender .= "</table>\n";  	 	

	$sToRender .= "</td>\n";
	$sToRender .= "<td>\n";
	$sToRender .= getUnitOrgEdit($bNoOrg, $iUnitID);
	$sToRender .= "</td>\n";        
    $sToRender .= "</table>\n";
   	

    return $sToRender;

}

function getUnitOrgEdit($bNoOrg, $iUnitID = null){
	global $default;
	
	$sBgColor = "#F5F6EE";
	       
    $sToRender .= "<table border=0 cellspacing=1>\n";
    $sToRender .= "  <tr><td><b>" . _("Options") . "</b></td></tr>";
    if ($bNoOrg) {
	    $sToRender .= "  <tr><td bgcolor=$sBgColor>";
	    $sToRender .= "  <br>&nbsp;&nbsp;<a href=\"$default->rootUrl/control.php?action=addUnitToOrg&fUnitID=" . $iUnitID . "\">" . _("Add Unit to an Organisation") . "</a><br><br>";
	    $sToRender .= "  </td><td>";	    
	    $sToRender .= "  </td></tr>";
    } else {
	    $sToRender .= "  <tr><td bgcolor=$sBgColor>";
	    $sToRender .= "  <br>&nbsp;&nbsp;<a href=\"$default->rootUrl/control.php?action=removeUnitFromOrg&fUnitID=" . $iUnitID  . "\">" . _("Remove Unit from an Organisation") . "</a><br><br>";
	    $sToRender .= "  </td><td>";	    
	    $sToRender .= "  </td></tr>";
    }
    $sToRender .= "</table>\n";
	
	return $sToRender;
	
}

// get page for removal
function getPage($iUserID) {
    global $default;
    $oUser = null;
    $oGroup = null;

    // change headings if User selected
    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
    } else {
        $heading = "<tr><td>" . _("Please select a User") . ":</td></tr>\n";
    }

    $sToRender .= renderHeading(_("Edit User Groups"));
    $sToRender .= "<table>\n";
    $sToRender .= $heading;
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td>" . _("User Name") . ": " . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";    
    $sToRender .= "</td></table>\n";
  
    return $sToRender;
}

// show paig if deletion fails
function getPageFail() {
    global $default;

    $sToRender .= "<table width=\"600\">" . renderHeading(_("Edit User Groups")) . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>" . _("Sorry, Removal Failed") . "</b></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeUserFromGroup\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// show success page if removed from Group successfully
function getPageSuccess() {
    global $default;

    $sToRender .= "<table width=\"600\">" . renderHeading(_("Edit User Groups")) . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>" . _("Success") . "!</b></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>" . _("The User was successfully removed from the Group") . "</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listUsers\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// show Group details on confirm deletion page
function getGroupDisplay($aGroup) {
    global $default;
	
	$sBgColor = "#F5F6EE";
    $oGroup= null;
    $sGroup = "<table cellspacing=1 border=0>";
    for ($i=0;$i < count($aGroup);$i++) {
        $oGroup= Group::get($aGroup[$i]);
        $Options .=  "<tr><td>" . ($i+1) . "</td><td bgcolor=$sBgColor>". $oGroup->getName() ."</td></tr>\n";
    }
    $sGroup .= $Options . "</table>";
    return $sGroup;   
}

// show delete button or select button
function getDeleteButton($oUser) {
    global $default;
    if (!isset($oUser)) {
        return "<td align = right><input type=\"image\" src =\"" . KTHtml::getSelectButton() . "\" value=\"submit\" border=\"0\"/></td>\n";
    } else {
        return "<td align = right><input type=\"image\" src =\"" . KTHtml::getDeleteButton() . "\" value=\"submit\" border=\"0\"/></td>\n";
    }
}


// show delete button or select button
function getSelectButton($oUser) {
    global $default;
    if (!isset($oUser)) {
        return "<input type=\"image\" src =\"" . KTHtml::getSelectButton() . "\" value=\"submit\" border=\"0\"/>\n";
    } else {
        return "<input type=\"image\" src =\"" . KTHtml::getSelectButton() . "\" value=\"submit\" border=\"0\"/>\n";
    }
}
// show cancel button
function getCancelButton($oUser) {
    global $default;
    if (!isset($oUser)) {
        //return "<td align = right><a href=\"$default->rootUrl/control.php?action=UserManagement\"><img src =\"" . KTHtml::getCancelButton() . "\" value=\"cancel\" /></a></td>\n";
    }
    else {
        return "<a href=\"$default->rootUrl/control.php?action=listUsers\"><img src =\"" . KTHtml::getCancelButton() . "\" border = \"0\" /></a>\n";
    }
}


?>
