<?php
/**
* BL information for listing Documemnt Fields	
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

	require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {    
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
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
		
		$oPatternCustom->addHtml(renderHeading("Link Management"));		// Create the Heading				
		 		
		$main->setFormAction($_SERVER['PHP_SELF']);
				
		
		$sQuery = 	"SELECT id as LinkID, name as LinkName, url LinkURL, rank as LinkRank, " . 
					"'Edit', 'Delete' " .
					"FROM " . $default->owl_links_table . " " .
					"ORDER BY name";
		
	    $aColumns = array("LinkName", "LinkURL", "LinkRank", "Edit", "Delete");
	    $aColumnNames = array("Link Name","URL", "Rank", "Edit", "Delete");
	    $aColumnTypes = array(1,1,1,3,3);
	    $aDBColumnArray = array("LinkID");
	    $aQueryStringVariableNames = array("fLinkID");
	    	    
	    $aHyperLinkURL = array(	3=> "$default->rootUrl/control.php?action=editLink",                       			
                       			4=> "$default->rootUrl/control.php?action=removeLink");
                     			
	    	    
	    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);	    
		$oSearchResults->setDisplayColumnHeadings(true);
	    $htmlTables = $oSearchResults->render() ;
	
	    $oPatternCustom->addHtml($htmlTables);	    
	
	} // end of if checksession
	
	$main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
