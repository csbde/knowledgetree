<?php
/**
* Presentation information for list links
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getQuickLinks() {
	global $default;
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
    return $oSearchResults->render() ;	
}

function getPage() {
	global $default;
	$sToRender .= renderHeading("QuickLinks Management");

	// add user link
	$sToRender .= getAddLink("addLink", "Add A QuickLink");
	$sToRender .= getQuickLinks();
	return $sToRender;
}
?>