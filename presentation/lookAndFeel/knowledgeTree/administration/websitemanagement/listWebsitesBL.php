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
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");    
require_once("$default->fileSystemRoot/lib/security/permission.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");    
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");    
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");    
require_once("$default->fileSystemRoot/presentation/Html.inc");

if(checkSession()) {	
  	$oPatternCustom = & new PatternCustom();
	
	$sQuery = 	"SELECT id as WebsiteID, web_site_name as WebsiteName, web_site_url WebsiteURL, web_master_id as WebmasterID, " . 
				"'Edit', 'Delete' " .
				"FROM " . $default->owl_web_sites_table . " " .
				"ORDER BY web_site_name";
	
    $aColumns = array("WebsiteName", "WebsiteURL", "Edit", "Delete");
    $aColumnNames = array("Link Name","URL", "Edit", "Delete");
    $aColumnTypes = array(1,1,3,3);
    $aDBColumnArray = array("WebsiteID");
    $aQueryStringVariableNames = array("fWebSiteID");
    	    
    $aHyperLinkURL = array(	2=> "$default->rootUrl/control.php?action=editWebSite",                       			
                     		3=> "$default->rootUrl/control.php?action=removeWebSite");
                   			
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);	    
	$oSearchResults->setDisplayColumnHeadings(true);
    $htmlTables = $oSearchResults->render() ;

	$oPatternCustom->addHtml(renderHeading("Website Management"));
    $oPatternCustom->addHtml($htmlTables);
   	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	$main->setCentralPayload($oPatternCustom);
	$main->setFormAction($_SERVER['PHP_SELF']);	
    $main->render();
}
?>