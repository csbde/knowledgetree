<?php
/**
* Presentation information for adding a unit
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

// display the listbox initially ..then just display the text
function getUnitDisplay($iUnitID) {
    global $default;
    
    if (Permission::userIsUnitAdministrator()) {
        $iUnitID = User::getUnitID($_SESSION["userID"]);
    }
    $oPatternListBox = & new PatternListBox($default->owl_units_table, "name", "id", "fUnitID");
	$oPatternListBox->setPostBackOnChange(true);    
	if ($iUnitID != 0) {
		$oPatternListBox->setSelectedValue($iUnitID);
	}    
    return "<table><tr><td><b>Filter By Unit </b></td><td>" . $oPatternListBox->render() . "</td></tr></table>";
}

function getGroups($fUnitID) {
	global $default;
	$sQuery = 	"SELECT groups_lookup.id as groupID, units_lookup.name as UnitNameB4, groups_lookup.name as name, 'Edit' , 'Delete', 'Edit Units', " .
				"CASE  WHEN units_lookup.name Is Null THEN '<font color=darkgrey>No Unit Assigned</font>' ELSE  units_lookup.name END AS UnitName " . 
				"FROM (groups_lookup LEFT join groups_units_link on groups_lookup.id = groups_units_link.group_id) " . 
				"LEft join units_lookup on units_lookup.id = groups_units_link.unit_id " .
				($fUnitID ? "WHERE groups_units_link.unit_id =$fUnitID " : "") .
				"ORDER BY groups_lookup.name ";
			
    $aColumns = array("name", "UnitName", "Edit", "Delete", "Edit Units");
    $aColumnNames = array( "Name", "Unit Name", "Edit", "Delete", "Edit Units");
    $aColumnTypes = array(1,1,3,3,3);
    $aDBColumnArray = array("groupID");
    $aQueryStringVariableNames = array("fGroupID");
    	    
    $aHyperLinkURL = array(	2=> "$default->rootUrl/control.php?action=editGroup",                       			
                     			3=> "$default->rootUrl/control.php?action=removeGroup",
                     			4=> "$default->rootUrl/control.php?action=editGroupUnit"); 
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);
	$oSearchResults->setDisplayColumnHeadings(true);	
    return $oSearchResults->render() ;	
}

function getPage($fUnitID) {
	global $default;
	$sToRender .= renderHeading("Group Management");

	$sToRender .= getAddLink("addGroup", "Add A Group");
	$sToRender .= getUnitDisplay($fUnitID);
	$sToRender .= getGroups($fUnitID);
	return $sToRender;
}
?>