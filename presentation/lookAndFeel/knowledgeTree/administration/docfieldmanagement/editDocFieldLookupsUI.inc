<?php
/**
 * $Id$
 *
 * Edit document field lookups UI functions
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
 * @package administration.docfieldmanagement
 */
 
function getLookupNotSet() {
    global $default;

	$sToRender .= renderHeading("Edit User Groups");
    $sToRender .= "<table>\n";    
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    $sToRender .= "Document Field is not Lookup enabled.";
    $sToRender .= "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td></td>\n";
    $sToRender .= "<td>\n";   
    $sToRender .= "<a href=\"$default->rootUrl/control.php?action=listDocFields\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a>";
    $sToRender .= "</td>\n";
    $sToRender .= "</table>\n";
    
    return $sToRender ;
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
        $heading = "<tr><td>Please select a User:</td></tr>\n";
    }

    $sToRender .= renderHeading("Edit User Groups");
    $sToRender .= "<table>\n";
    $sToRender .= $heading;
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td>User Name: " . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";    
    $sToRender .= "</td></table>\n";
   
    return $sToRender;
}



// get page for removal
function getGroupPage($fDocFieldID) {
    global $default;
    
    $oDocField = DocumentField::get($fDocFieldID);    
           
    $LookupDisplay .= "<b>Current Lookups</b><br>\n";	
	$sQuery = 	" Select * " .	
				" From " . $default->document_fields_lookup_tables .
				" WHERE document_field_id=" . $fDocFieldID;        

 	$aColumns = array("name");
    $aColumnNames = array("Lookup name");
    $aColumnTypes = array(1);
    $aDBColumnArray = array("id");
    $aQueryStringVariableNames = array("fDocFieldLookupID");
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);
    $oSearchResults->setEmptyTableMessage("<font color=\"red\">You must enter at least one value!</font>");   
	$LookupDisplay .= $oSearchResults->render() ;

	$sFontTag  .= "<font face=\"Arial\" color=\"#0066cc\">";
	$sTableTag .= "<table width=100% border=0 cellspacing=0>";
	$sBgColor = "#F5F6EE";
	
    $sToRender .= renderHeading("Edit Document Field Lookups");
	
    $sToRender .= "<table width=\"600\" border=0>";
    $sToRender .= "<tr><td width=100%><br><b>Field Name: &nbsp;</b>" . $oDocField->getName() . "<br><br></td><td>";
    $sToRender .= "<a href=\"$default->rootUrl/control.php?action=listDocFields\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a>";
    
    $sToRender .= "</td></tr>\n";
    $sToRender .= "</table>";    
    
    $sToRender .= "<table width=\"600\" border=0 ><tr><td>";
    $sToRender .= "<table border=0 >";
    
	$sToRender .= "<tr ><td valign=top>" . $LookupDisplay . "</td></tr>\n";
	$sToRender .= "<tr><td height=130><br></td></tr>\n";
	$sToRender .= "</table>";    
	
	$sToRender .= "</td><td valign=top>\n";
    
    $sToRender .= "<table border=0 cellspacing=1>\n";
    $sToRender .= "<tr><td><b>Options</b></td></tr>";
    $sToRender .= "<tr><td width=200 bgcolor=$sBgColor>";
    $sToRender .= "<br><a href=\"$default->rootUrl/control.php?action=addMetaDataForField&fDocFieldID=$fDocFieldID\">&nbsp;&nbsp;Add Document Field Lookups</a><br><br>";
    $sToRender .= "</td><td>"; 	
    $sToRender .= "</td></tr>";
	
	$iDocFieldLookupCount = DocumentField::getLookupCount($fDocFieldID);
	if ($iDocFieldLookupCount > 0) {
		
		$sToRender .= "<tr><td width=200 bgcolor=$sBgColor>";
	    $sToRender .= "<br><a href=\"$default->rootUrl/control.php?action=editMetadataForField&fDocFieldID=$fDocFieldID\">&nbsp;&nbsp;Edit Document Field Lookups</a><br><br>";
	    $sToRender .= "</td><td>"; 	
	    $sToRender .= "</td></tr>";
	    
	    $sToRender .= "<tr><td width=200 bgcolor=$sBgColor>";
	    $sToRender .= "<br><a href=\"$default->rootUrl/control.php?action=removeMetaDataFromField&fDocFieldID=$fDocFieldID\">&nbsp;&nbsp;Remove Document Field Lookups</a><br><br>";
	    $sToRender .= "</td><td>";    
	    $sToRender .= "</td></tr>";
	}
    $sToRender .= "</table>\n";
    
    $sToRender .= "</td></tr>";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// get page for removal
function getDeleteConfirmedPage($iUserID,$fGroupID) {
    global $default;
    $oUser = null;
    $oGroup = null;

    // change headings if User selected
    if (isset($iUserID)) {
        $oUser = User::get($iUserID);
        $heading = "<tr><td>Are you sure you wish to remove this user from the Group?</td></tr>";
    }

    // change headings if User selected
    if (isset($fGroupID)) {
        $oGroup = Group::get($fGroupID);
        $Groupdisplay = "<td>Group Name: " . $oGroup->getName() . "</td>\n";
    }

    $sToRender .= "<table width=\"600\">" . renderHeading("Edit User Groups") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= $heading;
    $sToRender .= "</tr>\n";
    $sToRender .= "<td>User Name: " . getUserDisplay($oUser) . "</td>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= $Groupdisplay;
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td></td><td>" . getDeleteButton($oUser);
    $sToRender .= "<td></td><td>" . getCancelButton($oUser);
    $sToRender .= "</td></table>\n";


    return $sToRender;
}

// show paig if deletion fails
function getPageFail() {
    global $default;

    $sToRender .= "<table width=\"600\">" . renderHeading("Edit User Groups") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Sorry, Removal Failed</b></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=removeUserFromGroup\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";

    $sToRender .= "</table>\n";


    return $sToRender;
}

// show success page if removed from Group successfully
function getPageSuccess() {
    global $default;

    $sToRender .= "<table width=\"600\">" . renderHeading("Edit User Groups") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Success!</b></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>The User was successfully removed from the Group</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listUsers\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}
// show listbox of Users belonging to Users_Groups_link table
function getUserDisplay($oUser) {
    global $default;
    if (!isset($oUser)) {
        $oPatternListBox = & new PatternListBox($default->users_table, "username", "id", "fUserID");
        if (Permission::userIsUnitAdministrator()) {
            $oPatternListBox->setFromClause("INNER JOIN $default->users_groups_table UGL on ST.id=UGL.user_id " .
                                            "INNER JOIN $default->groups_units_table GUL on UGL.group_id=GUL.group_id");
            $oPatternListBox->setWhereClause("GUL.unit_id=" . User::getUnitID($_SESSION["userID"]));
        }
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fUserID\" value=\"" . $oUser->iId . "\">\n" .
               $oUser->getUserName();
    }
}

// display the listbox initially ..then just display the text
function getOtherGroupDisplay($oGroup) {
	global $default;
	if (!isset($oGroup)) {
        if (Permission::userIsSystemAdministrator()) {
            // if this is the system administrator, prepend group names with unit name
            $oPatternListBox = & new PatternListBox($default->groups_table, "name", "id", "fOtherGroupID");
            $oPatternListBox->setFromClause("LEFT OUTER JOIN groups_units_link GUL on ST.id=GUL.group_id " .
                                            "LEFT OUTER JOIN units_lookup UL on GUL.unit_id=UL.id");
            $oPatternListBox->setCompositeDisplayName("DISTINCT COALESCE(CONCAT(CONCAT(UL.name, '-'),ST.name),ST.name)");
        } else if (Permission::userIsUnitAdministrator()) {
            // else if this is a unit administrator, only display the groups in your unit
            $oPatternListBox = & new PatternListBox($default->groups_table, "name", "id", "fOtherGroupID");
            $oPatternListBox->setFromClause("INNER JOIN $default->groups_units_table GUL on ST.id=GUL.group_id");
            $oPatternListBox->setWhereClause("GUL.unit_id=" . User::getUnitID($_SESSION["userID"]));
        }
        return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fOtherGroupID\" value=\"" . $oGroup->iId . "\">\n" .
		$oGroup->getName();
	}
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
        return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" border=\"0\"/></td>\n";
    } else {
        return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" border=\"0\"/></td>\n";
    }
}


// show delete button or select button
function getSelectButton($oUser) {
    global $default;
    if (!isset($oUser)) {
        return "<input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" border=\"0\"/>\n";
    } else {
        return "<input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" border=\"0\"/>\n";
    }
}
// show cancel button
function getCancelButton($oUser) {
    global $default;
    if (!isset($oUser)) {
        //return "<td align = right><a href=\"$default->rootUrl/control.php?action=UserManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
    }
    else {
        return "<a href=\"$default->rootUrl/control.php?action=listUsers\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a>\n";
    }
}


?>