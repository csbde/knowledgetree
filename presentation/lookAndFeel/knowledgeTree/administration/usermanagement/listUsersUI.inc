<?php
/**
* Presentation information for adding a unit
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getGroupDisplay($iGroupID) {
	global $default;
	if (Permission::userIsSystemAdministrator()) {
	    // if this is the system administrator, prepend group names with unit name
	    $oPatternListBox = & new PatternListBox($default->owl_groups_table, "name", "id", "fGroupID");
	    $oPatternListBox->setFromClause("LEFT OUTER JOIN groups_units_link GUL on ST.id=GUL.group_id " .
	                                    "LEFT OUTER JOIN units_lookup UL on GUL.unit_id=UL.id");
	    $oPatternListBox->setCompositeDisplayName("DISTINCT COALESCE(CONCAT(CONCAT(UL.name, '-'),ST.name),ST.name)");
	} else if (Permission::userIsUnitAdministrator()) {
	    // else if this is a unit administrator, only display the groups in your unit
	    $oPatternListBox = & new PatternListBox($default->owl_groups_table, "name", "id", "fGroupID");
	    $oPatternListBox->setFromClause("INNER JOIN $default->owl_groups_units_table GUL on ST.id=GUL.group_id");
	    $oPatternListBox->setWhereClause("GUL.unit_id=" . User::getUnitID($_SESSION["userID"]));
	}
	$oPatternListBox->setPostBackOnChange(true);
	if ($iGroupID != 0) {
		$oPatternListBox->setSelectedValue($iGroupID);
	}
	        
	return "<table><tr><td><b>Filter By Group </b></td><td>" . $oPatternListBox->render() . "</td></tr></table>";	        
}

function getUsers($fGroupID) {
	global $default;
	$sQuery = 	"SELECT users.id as userID, users.name as name, username, " . 
				"'Edit' , 'Delete', 'Edit Groups' " .
				"FROM users " . 
				($fGroupID ? "INNER JOIN users_groups_link ON users.id = users_groups_link.user_id WHERE users_groups_link.group_id = $fGroupID " : "") .  
				"ORDER BY users.name";			
	
    $aColumns = array("name", "username",  "Edit", "Delete", "Edit Groups");
    $aColumnNames = array("Name", "Username", "Edit", "Delete", "Edit Groups");
    $aColumnTypes = array(1,1,3,3,3);
    $aDBColumnArray = array("userID");
    $aQueryStringVariableNames = array("fUserID");
    	    
    $aHyperLinkURL = array(	2=> "$default->rootUrl/control.php?action=editUser",                       			
                     		3=> "$default->rootUrl/control.php?action=removeUser",
                     		4=> "$default->rootUrl/control.php?action=editUserGroups");
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);
	$oSearchResults->setDisplayColumnHeadings(true);
    return $oSearchResults->render() ;	
}

function getPage($fGroupID) {
	global $default;
	$sToRender .= renderHeading("User Management");

	// add user link
	$sToRender .= getAddLink("addUser", "Add A User");
	$sToRender .= getGroupDisplay($fGroupID);
	$sToRender .= getUsers($fGroupID);
	return $sToRender;
}
?>