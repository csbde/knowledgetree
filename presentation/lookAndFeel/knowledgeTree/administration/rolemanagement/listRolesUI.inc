<?php
/**
* Presentation information for adding a unit
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
function getRoles() {
	global $default;
	$sQuery = 	"SELECT id as roleID, name as name, active, can_read as reader, can_write as writer, " . 
				"'Edit', 'Delete' " .
				"FROM roles " .
				"ORDER BY name";				
	
    $aColumns = array("name", 	  "active", "reader", "writer", "Edit", "Delete");
    $aColumnNames = array("Name", "Active", "Read", "Write", "Edit", "Delete");
    $aColumnTypes = array(1,2,2,2,3,3);
    $aDBColumnArray = array("roleID");
    $aQueryStringVariableNames = array("fRoleID");
    	    
    $aHyperLinkURL = array(	4=> "$default->rootUrl/control.php?action=editRole",                       			
                     			5=> "$default->rootUrl/control.php?action=removeRole"); //"$default->rootUrl/control.php?action=removeUserFromGroup");
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);	    
	$oSearchResults->setDisplayColumnHeadings(true);	

    return $oSearchResults->render() ;	
}

function getPage() {
	global $default;
	$sToRender .= renderHeading("Role Management");

	// add user link
	$sToRender .= getAddLink("addRole", "Add A Role");
	$sToRender .= getRoles();
	return $sToRender;
}
?>