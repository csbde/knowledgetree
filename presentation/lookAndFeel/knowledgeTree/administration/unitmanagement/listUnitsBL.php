<?php
/**
* BL information for viewing a Discussion	
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

	require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {    
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");    
    require_once("listUnitsUI.inc"); 
	
	require_once("$default->fileSystemRoot/lib/orgManagement/Organisation.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/lib/users/User.inc");    
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");    
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");    
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");   
       
    require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	
    $oPatternCustom = & new PatternCustom();

if(checkSession()) {	
		global $default;
		
		$oPatternCustom->addHtml(renderHeading("Current System Units"));		// Create the Heading
		 
		$oPatternCustom->addHtml(getFilterOptions($fOrgID));
		
		$main->setFormAction($_SERVER['PHP_SELF']);
				
		if($fOrgID != "") {    // Filter Option
			$sWhereStatement = "WHERE units_organisations_link.organisation_id =$fOrgID "; 
			$oOrg = Organisation::get($fOrgID);
			$oPatternCustom->addHtml("<br><table><tr><td><b>Current Organisation filter:</b></td><td>" . $oOrg->getName() . "</td></tr></table>");
		} else {
			$oPatternCustom->addHtml("<br><table><tr><td><b><b>No Organisation filter</b></td></tr></table>");	
		}
		$sQuery = 	"SELECT units_lookup.id as unitID, units_lookup.name as name, " . 
					"'Edit', 'Delete', 'Edit Organisations', " .
					"CASE  WHEN organisations_lookup.name Is Null THEN '<font color=darkgrey>* No Organisation</font>' ELSE  organisations_lookup.name END AS OrgName " .
					"FROM (units_lookup " .
					"LEFT JOIN units_organisations_link ON units_lookup.id = units_organisations_link.unit_id) " . 
					"LEFT JOIN organisations_lookup ON units_organisations_link.organisation_id = organisations_lookup.id " .
					$sWhereStatement . " " .					
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
	    $htmlTables = $oSearchResults->render() ;
	
	    $oPatternCustom->addHtml($htmlTables);	    
	
	} // end of if checksession
	
	$main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
