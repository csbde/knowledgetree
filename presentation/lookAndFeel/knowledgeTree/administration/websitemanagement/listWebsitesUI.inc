<?php
/**
* Presentation information for adding a unit
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
function getWebsites() {
	global $default;
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

    return $oSearchResults->render() ;	
}

function getPage() {
	global $default;
	$sToRender .= renderHeading("Website Management");

	// add user link
	$sToRender .= getAddLink("addWebsite", "Add A Website");
	$sToRender .= getWebsites();
	return $sToRender;
}
?>