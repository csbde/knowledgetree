<?php
/**
* Presentation information for adding a unit
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getOrganisations() {
	global $default;
	$sQuery = 	"SELECT org.id as orgID, org.name as name, " . 
				"'Edit', 'Delete' " .
				"FROM organisations_lookup as org " .
				"ORDER BY org.name";
    $aColumns = array("name", "Edit", "Delete");
    $aColumnNames = array("Name", "Edit", "Delete");
    $aColumnTypes = array(1,3,3);
    $aDBColumnArray = array("orgID");
    $aQueryStringVariableNames = array("fOrgID");
    $aHyperLinkURL = array(	1=> "$default->rootUrl/control.php?action=editOrg",                       			
                     		2=> "$default->rootUrl/control.php?action=removeOrg");
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);
	$oSearchResults->setDisplayColumnHeadings(true);
    return $oSearchResults->render() ;	
}

function getPage() {
	global $default;
	$sToRender .= renderHeading("Organisation Management");

	// add user link
	$sToRender .= getAddLink("addOrg", "Add An Organisation");
	$sToRender .= getOrganisations();
	return $sToRender;
}
?>