<?php
/**
* Presentation information for adding a unit
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getOrganisationDisplay($fOrganisationID) {
	global $default;
	$oPatternListBox = & new PatternListBox($default->owl_organisations_table, "name", "id", "fOrganisationID");
	$oPatternListBox->setPostBackOnChange(true);
	if ($fOrganisationID != 0) {
		$oPatternListBox->setSelectedValue($fOrganisationID);
	}
	return "<table><tr><td><b>Filter By Organisation </b></td><td>" . $oPatternListBox->render() . "</td></tr></table>";	        
}

function getUnits($fOrganisationID) {
	global $default;
	
	$sQuery = 	"SELECT units_lookup.id as unitID, units_lookup.name as name, " . 
				"'Edit', 'Delete', 'Edit Organisations', " .
				"CASE  WHEN organisations_lookup.name Is Null THEN '<font color=darkgrey>* No Organisation</font>' ELSE  organisations_lookup.name END AS OrgName " .
				"FROM (units_lookup " .
				"LEFT JOIN units_organisations_link ON units_lookup.id = units_organisations_link.unit_id) " . 
				"LEFT JOIN organisations_lookup ON units_organisations_link.organisation_id = organisations_lookup.id " .
				($fOrganisationID ? "WHERE units_organisations_link.organisation_id = $fOrganisationID " : "") .					
				"ORDER BY units_lookup.name";
		
    $aColumns = array("name", "OrgName", "Edit", "Delete", "Edit Organisations");
    $aColumnNames = array("Unit Name", "Organisation", "Edit", "Delete", "Edit Organisations");
    $aColumnTypes = array(1,1,3,3,3);
    $aDBColumnArray = array("unitID");
    $aQueryStringVariableNames = array("fUnitID");
    	    
    $aHyperLinkURL = array(	2=> "$default->rootUrl/control.php?action=editUnit",                       			
                     			3=> "$default->rootUrl/control.php?action=removeUnit",
                     			4=> "$default->rootUrl/control.php?action=editUnitOrg"); 
	    	    
	$oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);
	$oSearchResults->setDisplayColumnHeadings(true);
    return $oSearchResults->render() ;	
}

function getPage($fOrganisationID) {
	global $default;
	$sToRender .= renderHeading("Unit Management");

	$sToRender .= getAddLink("addUnit", "Add A Unit");
	$sToRender .= getOrganisationDisplay($fOrganisationID);
	$sToRender .= getUnits($fOrganisationID);
	return $sToRender;
}
?>