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
    require_once("listGroupsUI.inc");        
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
    require_once("$default->fileSystemRoot/lib/unitmanagement/Unit.inc");       
    require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	
    $oPatternCustom = & new PatternCustom();

if(checkSession()) {	

		$oPatternCustom->addHtml(renderHeading("Current System Groups"));		// Create the Heading				
		$oPatternCustom->addHtml("<table align='left' border=0><tr><td><b>Filter by Unit </b></td><td>");	// 
		
		if (($fUnitID != "")) {  // Check if should use the query string OR ...				
			$oUnit = Unit::get($fUnitID);			
			$oPatternCustom->addHtml(getFilterOptions($sFilterOptions) . "</td></tr></table>\n");		// Get Groups Dropdown for filter option			 
		} else { //if ($_SESSION['GroupUnitFilter'][0]->iSessionUnitID != ""){				 // ... should use session variable
					
			$oPatternCustom->addHtml(getFilterOptions($sFilterOptions) . "</td></tr></table>");		// Get Groups Dropdown for filter option			
		}		
		$oPatternCustom->addHtml(getSubmit());				
		$oPatternCustom->addHtml("<br><br>");
		
		if ($oUnit) {
			$oPatternCustom->addHtml("<font face=arial size=2><b>&nbsp;Active Unit filter: </b>" . $oUnit->getName() . "</font>");
		}
		$main->setFormAction($_SERVER['PHP_SELF']);
		  
		
		if($fUnitID != "") {    // Filter Option
			$sWhereStatement = "WHERE groups_units_link.unit_id =$fUnitID "; 
		}
		$sQuery = 	"SELECT groups_lookup.id as groupID, units_lookup.name as UnitNameB4, groups_lookup.name as name, 'Edit' , 'Delete', 'Edit Units', " .
					"CASE  WHEN units_lookup.name Is Null THEN '<font color=darkgrey>No Unit Assigned</font>' ELSE  units_lookup.name END AS UnitName " . 
					"FROM (groups_lookup LEFT join groups_units_link on groups_lookup.id = groups_units_link.group_id) " . 
					"LEft join units_lookup on units_lookup.id = groups_units_link.unit_id " .
					$sWhereStatement . " " .
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
	    $htmlTables = $oSearchResults->render() ;
	
	    $oPatternCustom->addHtml($htmlTables);	    
			
	} // end of if checksession
	
	$main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
